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
	// Detectamos si es PDF por extensión o por el campo Tipo
	ext := strings.ToLower(filepath.Ext(task.Path))

	if ext == ".pdf" {
		processPdf(task)
	} else {
		processImage(task)
	}
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
	//log.Printf("--- Iniciando PDF: %s ---", task.Path)
	watermark := "/var/www/html/bpej/public/img/logo.svg"

	// 1. Validar si el archivo existe
	if _, err := os.Stat(task.Path); os.IsNotExist(err) {
		log.Printf("ERROR: El archivo PDF no existe en la ruta: %s", task.Path)
		return
	}

	// 2. Obtener páginas
	cmdInfo := exec.Command("pdfinfo", task.Path)
	out, err := cmdInfo.CombinedOutput()
	if err != nil {
		log.Printf("ERROR en pdfinfo: %v | Salida: %s", err, string(out))
		return
	}

	totalPages := extractPages(string(out))
	log.Printf("Páginas detectadas: %d", totalPages)

	if totalPages == 0 {
		log.Printf("ERROR: No se detectaron páginas.")
		return
	}

	for i := 1; i <= totalPages; i++ {
		pageDir := filepath.Join(basePath, task.ColeccionSlug,
			strconv.Itoa(task.RecursoID),
			fmt.Sprintf("p%d", i))

		os.MkdirAll(pageDir, 0755)

		outputBase := filepath.Join(pageDir, "main")

		// Esta es la ruta real que creará pdftocairo: "pageDir/main.webp"
		actualWebpPath := filepath.Join(pageDir, "main.webp")
		thumbPath := filepath.Join(pageDir, "thumb.webp")

		// 1. Extraer página
		// -singlefile asegura que no añada numeración como "main-1"
		extractCmd := exec.Command("pdftocairo", "-webp", "-singlefile",
			"-f", strconv.Itoa(i),
			"-l", strconv.Itoa(i),
			"-scale-to-x", "2000",
			task.Path, outputBase) // <-- Sin extensión aquí

		if err := extractCmd.Run(); err != nil {
			log.Printf("Error extrayendo página %d: %v", i, err)
			continue
		}

		// 2. Aplicar Marca de Agua (ahora usamos actualWebpPath que ya existe)
		watermarkCmd := exec.Command("magick",
			actualWebpPath,
			"-background", "none", "-resize", "150x", watermark, // Ajusta el tamaño aquí
			"-gravity", "south-east",
			"-geometry", "+50+50",
			"-composite",
			"-quality", "80",
			actualWebpPath)

		watermarkCmd.Run()

		// 3. Generar Thumbnail
		exec.Command("magick", actualWebpPath,
			"-thumbnail", "200x200^",
			"-gravity", "center",
			"-extent", "200x200",
			"-quality", "70",
			thumbPath).Run()

		// 4. Guardado en DB (pasamos actualWebpPath que tiene el nombre correcto)
		if i == 1 {
			updateDatabase(task.ArchivoID, actualWebpPath, thumbPath)
		} else {
			createNewPageRecord(task, i, actualWebpPath, thumbPath)
		}
	}
	log.Printf("--- Finalizado PDF ID: %d ---", task.RecursoID)
}
