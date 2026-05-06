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

	// 1. Validaciones iniciales
	if _, err := os.Stat(task.Path); os.IsNotExist(err) {
		log.Printf("ERROR: Archivo no encontrado: %s", task.Path)
		return
	}

	cmdInfo := exec.Command("pdfinfo", task.Path)
	out, _ := cmdInfo.Output()
	totalPages := extractPages(string(out))

	if totalPages == 0 {
		return
	}

	for i := 1; i <= totalPages; i++ {
		// Carpeta única por página
		pageDir := filepath.Join(basePath, task.ColeccionSlug,
			strconv.Itoa(task.RecursoID),
			fmt.Sprintf("p%d", i))

		// ¡CRÍTICO!: Asegurarnos de que la carpeta existe físicamente
		err := os.MkdirAll(pageDir, 0755)
		if err != nil {
			log.Printf("Error creando carpeta %s: %v", pageDir, err)
			continue
		}

		// 2. Definir rutas de archivos
		outputBase := filepath.Join(pageDir, "main") // Cairo añadirá .webp automáticamente
		actualWebp := outputBase + ".webp"
		thumbPath := filepath.Join(pageDir, "thumb.webp")

		// 3. Ejecutar Cairo
		// Añadimos logs para ver qué comando exacto se está ejecutando
		extractCmd := exec.Command("pdftocairo",
			"-f", strconv.Itoa(i),
			"-l", strconv.Itoa(i),
			"-webp",
			"-singlefile",
			"-scale-to-x", "2000",
			task.Path,
			outputBase)

		// Capturamos el error detallado si falla
		if out, err := extractCmd.CombinedOutput(); err != nil {
			log.Printf("Fallo Cairo pág %d: %v | Salida: %s", i, err, string(out))
			continue
		}

		// 4. Marca de agua (solo si Cairo tuvo éxito)
		watermarkCmd := exec.Command("magick",
			actualWebp,
			"-background", "none", "-resize", "350x", watermark,
			"-gravity", "south-east", "-geometry", "+50+50",
			"-composite", actualWebp)
		watermarkCmd.Run()

		// 6. Thumbnail
		exec.Command("magick", outputBase,
			"-thumbnail", "200x200^", "-gravity", "center",
			"-extent", "200x200", "-quality", "70",
			thumbPath).Run()

		// 7. Base de Datos
		if i == 1 {
			updateDatabase(task.ArchivoID, outputBase, thumbPath)
		} else {
			createNewPageRecord(task, i, outputBase, thumbPath)
		}
	}
	log.Printf("Finalizado: %s (%d páginas)", task.Path, totalPages)
}
