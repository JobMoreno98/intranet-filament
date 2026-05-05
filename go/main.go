package main

import (
	"context"
	"encoding/json"
	"github.com/redis/go-redis/v9"
	"log"

)

func main() {
	rdb := redis.NewClient(&redis.Options{Addr: "127.0.0.1:6379"})
	ctx := context.Background()

	// Limitar a 3 procesos simultáneos para no colgar el disco duro
	sem := make(chan struct{}, 3)

	log.Println("Worker iniciado. Esperando imágenes...")
	log.Println("Se actualizo el binario")

	for {
		// BLPop espera hasta que Laravel mande algo
		result, err := rdb.BLPop(ctx, 0, "bpej_cola_procesamiento").Result()
		if err != nil {
			continue
		}

		var task ProcessingTask
		json.Unmarshal([]byte(result[1]), &task)

		// Ejecutar proceso
		go func(t ProcessingTask) {
			sem <- struct{}{}        // Ocupa un lugar
			defer func() { <-sem }() // Libera el lugar al terminar
			processImage(t)
		}(task)
	}
}
