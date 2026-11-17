package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"os"
	"os/signal"
	"sync"
	"syscall"
	"time"

	"github.com/redis/go-redis/v9"
)

func main() {
	// 1. Carga y valida la configuración desde las variables de entorno / .env
	if err := loadConfig(); err != nil {
		log.Fatalf("Error fatal iniciando worker: %v", err)
	}

	if err := loadConfig(); err != nil {
		log.Fatalf("Error fatal iniciando worker: %v", err)
	}

	if err := initLoggers(cfg.LogDir); err != nil {
		log.Fatalf("Error fatal inicializando archivos de log: %v", err)
	}
	defer closeLoggers()

	ctx, stop := signal.NotifyContext(context.Background(), os.Interrupt, syscall.SIGTERM)
	defer stop()

	// 2. Usa el cliente centralizado de Redis (definido en db.go)
	rdb := getRedis()
	defer rdb.Close()

	// 3. Verifica que Tesseract tenga los idiomas configurados
	if err := checkOcrLanguages(); err != nil {
		log.Printf("!!! OCR NO DISPONIBLE: %v", err)
	}

	sem := make(chan struct{}, cfg.WorkerConcurrency)
	var wg sync.WaitGroup

	log.Printf("Worker iniciado (%d tareas simultáneas). Storage: %s | Colas: %s, %s",
		cfg.WorkerConcurrency, cfg.StoragePrivatePath, cfg.ColaProcesamiento, cfg.ColaOcr)

loop:
	for {
		// 1) Primero reservamos un lugar y SOLO DESPUÉS sacamos la tarea de
		// Redis. Si el worker se reinicia, las tareas pendientes siguen seguras en Redis.
		select {
		case sem <- struct{}{}:
		case <-ctx.Done():
			break loop
		}

		result, err := rdb.BLPop(ctx, 5*time.Second, cfg.ColaProcesamiento, cfg.ColaOcr).Result()
		if err != nil {
			<-sem
			if ctx.Err() != nil {
				break loop
			}
			if err != redis.Nil { // redis.Nil = timeout normal sin tareas
				log.Printf("Error leyendo de Redis: %v", err)
				time.Sleep(2 * time.Second) // evita un ciclo que consume CPU si Redis se cae
			}
			continue
		}

		cola, payload := result[0], result[1]

		wg.Add(1)
		go func(cola, payload string) {
			defer wg.Done()
			defer func() { <-sem }()
			defer func() {
				// Un panic en una tarea ya no tumba todo el worker.
				if r := recover(); r != nil {
					log.Printf("PANIC procesando tarea de %s: %v | payload: %s", cola, r, payload)
					registrarError("PANIC", 0, 0, cola, fmt.Sprintf("%v | payload: %s", r, payload))
				}
			}()

			if cola == cfg.ColaOcr {
				var t OcrTask
				if err := json.Unmarshal([]byte(payload), &t); err != nil {
					log.Printf("Tarea OCR inválida: %v | payload: %s", err, payload)
					registrarError("JSON_INVALIDO", 0, 0, cola, err.Error())
					return
				}
				reprocessOcr(t)
				return
			}

			var t ProcessingTask
			if err := json.Unmarshal([]byte(payload), &t); err != nil {
				log.Printf("Tarea inválida: %v | payload: %s", err, payload)
				return
			}
			processTask(t)
		}(cola, payload)
	}

	log.Println("Apagando worker: esperando a que terminen las tareas en curso...")
	wg.Wait()
	log.Println("Worker detenido.")
}
