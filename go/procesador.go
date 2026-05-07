package main

import (
	"fmt"
	"log"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"strconv"
	"strings"
	"time"
)

var basePath string

func init() {
	if runtime.GOOS == "windows" {
		basePath = filepath.Join("../storage", "app", "private")
	} else {
		basePath = "/var/www/html/bpej/storage/app/private"
	}
}

func processTask(task ProcessingTask) {
	path := strings.TrimSpace(task.Path)
	ext := strings.ToLower(filepath.Ext(path))

	log.Printf("------------------------------------------------")
	log.Printf("PROCESANDO: ID=%d | EXTENSIÓN: %s", task.ArchivoID, ext)

	// Solo entra a rutina de PDF si la extensión es .pdf
	switch ext {
	case ".pdf", "pdf":
		log.Printf(">>> RUTINA: PDF DETECTADO <<<")
		processPdf(task)
	case ".jpg", ".jpeg", ".png", ".webp":
		log.Printf(">>> RUTINA: IMAGEN DETECTADA <<<")
		processImage(task)
	default:
		log.Printf("ERROR: Extensión '%s' no soportada para ID %d", ext, task.ArchivoID)
	}
	log.Printf("------------------------------------------------")
}

func processImage(task ProcessingTask) {

	exists := false
	for i := 0; i < 5; i++ {
		if _, err := os.Stat(task.Path); err == nil {
			exists = true
			break
		}
		log.Printf("Archivo no listo para ID %d, reintentando en 500ms...", task.ArchivoID)
		time.Sleep(500 * time.Millisecond)
	}

	if !exists {
		log.Printf("ERROR CRÍTICO: El archivo nunca apareció en %s", task.Path)
		return
	}

	source := strings.ReplaceAll(task.Path, "\\", "/")

	// Estructura: private/slug-coleccion/id-recurso/id-archivo/
	outputDir := filepath.Join(basePath, task.ColeccionSlug,
		strconv.Itoa(task.RecursoID),
		strconv.Itoa(task.ArchivoID))

	os.MkdirAll(outputDir, 0755)

	thumbPath := filepath.Join(outputDir, "thumb.webp")
	mainPath := filepath.Join(outputDir, "main.webp")

	// Ruta al logo SVG
	//watermark := "/var/www/html/bpej/public/img/logo.svg"

	// COMANDO CORREGIDO:
	// 1. Cargamos la fuente
	// 2. Cargamos el watermark con su configuración de fondo
	// 3. Aplicamos la gravedad y geometría antes del composite
	// En tu función processImage, cambia el comando de la marca de agua por esto:
	args := []string{
		task.Path,
		"-resize", "2500x>", // Recomendado para evitar archivos gigantes
		"-quality", "80", // El ajuste de calidad para bajar de MBs a KBs
	}

	/*
	   // COMENTADO DE MOMENTO: Lógica de Marca de Agua
	   args = append(args,
	       "-background", "none",
	       "-size", "150x",
	       watermark,
	       "-gravity", "south-east",
	       "-geometry", "+50+50",
	       "-composite",
	   )
	*/

	// Argumento final: la ruta de destino (forzando formato webp)
	args = append(args, "webp:"+mainPath)

	// Ejecutamos el comando con los argumentos dinámicos
	cmd := exec.Command("magick", args...)

	// Captura de errores
	if out, err := cmd.CombinedOutput(); err != nil {
		log.Printf("ERROR REAL DE MAGICK en ID %d: %s", task.ArchivoID, string(out))
	}

	// Generar Miniatura (Thumbnail)
	// Nota: Aquí usamos 'source' para que la miniatura no tenga marca de agua y sea más clara
	exec.Command("magick", source,
		"-thumbnail", "200x200^",
		"-gravity", "center",
		"-extent", "200x200",
		"-quality", "70",
		thumbPath).Run()

	// Actualizamos la DB
	updateDatabase(task.ArchivoID, mainPath, thumbPath)
}

func extractPages(info string) int {
	lines := strings.Split(info, "\n")
	for _, line := range lines {
		if strings.Contains(line, "Pages:") {
			// Limpia la línea para quedarse solo con el número
			fields := strings.Fields(line)
			if len(fields) >= 2 {
				p, err := strconv.Atoi(fields[1])
				if err == nil {
					return p
				}
			}
		}
	}
	return 0
}

func processPdf(task ProcessingTask) {
	log.Printf("--- Iniciando PDF: %s ---", task.Path)
	watermark := "/var/www/html/bpej/public/img/logo.svg"

	// 1. Obtener total de páginas
	cmdInfo := exec.Command("pdfinfo", task.Path)
	out, err := cmdInfo.CombinedOutput()
	if err != nil {
		log.Printf("ERROR pdfinfo: %v", err)
		return
	}
	totalPages := extractPages(string(out))
	log.Printf("Páginas a procesar: %d", totalPages)

	for i := 1; i <= totalPages; i++ {
		pageDir := filepath.Join(basePath, task.ColeccionSlug,
			strconv.Itoa(task.RecursoID),
			fmt.Sprintf("p%d", i))

		os.MkdirAll(pageDir, 0755)

		// Definición de rutas
		tempPngBase := filepath.Join(pageDir, "temp_render")
		actualPng := tempPngBase + ".png" // Cairo añade el .png
		finalWebp := filepath.Join(pageDir, "main.webp")
		thumbPath := filepath.Join(pageDir, "thumb.webp")

		// 2. Extraer a PNG (Formato compatible con Poppler 25.03+)
		extractCmd := exec.Command("pdftocairo",
			"-png",
			"-singlefile",
			"-f", strconv.Itoa(i),
			"-l", strconv.Itoa(i),
			task.Path,
			tempPngBase)

		if err := extractCmd.Run(); err != nil {
			log.Printf("Error Cairo pág %d: %v", i, err)
			continue
		}

		// 3. Magick: Marca de agua + Conversión a WebP
		// Usamos el PNG como fuente y guardamos directamente en .webp
		watermarkCmd := exec.Command("magick",
			actualPng,
			"-background", "none", "-size", "150x", watermark,
			"-gravity", "south-east", "-geometry", "+50+50",
			"-composite",
			"-quality", "80",
			finalWebp)

		if err := watermarkCmd.Run(); err != nil {
			log.Printf("Error Magick pág %d: %v", i, err)
		}

		// 4. Generar Thumbnail (desde el WebP ya procesado)
		exec.Command("magick", finalWebp,
			"-thumbnail", "200x200^",
			"-gravity", "center",
			"-extent", "200x200",
			"-quality", "70",
			thumbPath).Run()

		// 5. Limpieza: Eliminar el PNG temporal para ahorrar espacio
		os.Remove(actualPng)

		// 6. Registro en Base de Datos
		if i == 1 {
			updateDatabase(task.ArchivoID, finalWebp, thumbPath)
		} else {
			createNewPageRecord(task, i, finalWebp, thumbPath)
		}
	}
	log.Printf("--- Finalizado PDF: %d ---", task.RecursoID)
}
