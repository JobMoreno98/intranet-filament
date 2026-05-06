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

	// LOG DE EMERGENCIA: Vamos a ver exactamente qué llega de Redis
	log.Printf("------------------------------------------------")
	log.Printf("RECIBIDO: ID=%d | PATH=%s", task.ArchivoID, path)
	log.Printf("EXTENSIÓN DETECTADA: '%s'", ext)
	log.Printf("TIPO EN JSON: '%s'", task.Tipo)

	// Forzamos la detección tanto por extensión como por el campo "tipo"
	if ext == ".pdf" || strings.ToLower(task.Tipo) == "pdf" {
		log.Printf(">>> EJECUTANDO RUTINA DE PDF <<<")
		processPdf(task)
	} else {
		log.Printf(">>> EJECUTANDO RUTINA DE IMAGEN <<<")
		processImage(task)
	}
	log.Printf("------------------------------------------------")
}

func processImage(task ProcessingTask) {
	source := strings.ReplaceAll(task.Path, "\\", "/")

	// Estructura: private/slug-coleccion/id-recurso/id-archivo/
	outputDir := filepath.Join(basePath, task.ColeccionSlug,
		strconv.Itoa(task.RecursoID),
		strconv.Itoa(task.ArchivoID))

	os.MkdirAll(outputDir, 0755)

	thumbPath := filepath.Join(outputDir, "thumb.webp")
	mainPath := filepath.Join(outputDir, "main.webp")

	// Ruta al logo SVG
	watermark := "/var/www/html/bpej/public/img/logo.svg"

	// COMANDO CORREGIDO:
	// 1. Cargamos la fuente
	// 2. Cargamos el watermark con su configuración de fondo
	// 3. Aplicamos la gravedad y geometría antes del composite
	cmd := exec.Command("magick",
		source,
		"-background", "none",
		"-size", "150x",
		watermark,
		"-gravity", "south-east",
		"-geometry", "+50+50",
		"-composite",
		"-quality", "80",
		mainPath)

	if err := cmd.Run(); err != nil {
		log.Printf("Error al procesar marca de agua en ID %d: %v", task.ArchivoID, err)
		// Fallback: Si falla el logo (ej. librsvg no instalada), generamos imagen limpia
		exec.Command("magick", source, "-quality", "80", mainPath).Run()
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
			"-scale-to-x", "2000",
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
			"-background", "none", "-resize", "350x", watermark,
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
