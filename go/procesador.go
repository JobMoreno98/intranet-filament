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
		// Tu ruta local en Zapopan
		basePath = filepath.Join("../storage", "app", "private")

	} else {
		// La ruta en el servidor de la UdeG
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

	// Procesamiento de imagen individual
	exec.Command("magick", source, "-quality", "80", mainPath).Run()
	exec.Command("magick", source, "-thumbnail", "200x200^", "-gravity", "center", "-extent", "200x200", thumbPath).Run()

	// Actualizamos el registro que Laravel ya creó
	updateDatabase(task.ArchivoID, mainPath, thumbPath)
}

func extractPages(info string) int {
	lines := strings.Split(info, "\n")
	for _, line := range lines {
		if strings.HasPrefix(line, "Pages:") {
			fields := strings.Fields(line)
			if len(fields) > 1 {
				p, _ := strconv.Atoi(fields[1])
				return p
			}
		}
	}
	return 1
}

func processPdf(task ProcessingTask) {
	log.Printf("Procesando PDF: %s", task.Path)

	// 1. Obtener páginas
	out, _ := exec.Command("pdfinfo", task.Path).Output()
	totalPages := extractPages(string(out))

	for i := 1; i <= totalPages; i++ {
		// Ahora basePath ya es conocido por la función
		pageDir := filepath.Join(basePath, task.ColeccionSlug,
			strconv.Itoa(task.RecursoID),
			fmt.Sprintf("p%d", i))

		os.MkdirAll(pageDir, 0755)

		mainPath := filepath.Join(pageDir, "main.webp")
		thumbPath := filepath.Join(pageDir, "thumb.webp")

		// Ejecución de comandos (pdftocairo y magick)
		exec.Command("pdftocairo", "-webp", "-singlefile", "-f", strconv.Itoa(i), "-l", strconv.Itoa(i), "-scale-to-x", "2000", task.Path, filepath.Join(pageDir, "main")).Run()
		exec.Command("magick", mainPath, "-thumbnail", "200x200^", "-gravity", "center", "-extent", "200x200", thumbPath).Run()

		// Guardado en DB
		if i == 1 {
			updateDatabase(task.ArchivoID, mainPath, thumbPath)
		} else {
			createNewPageRecord(task, i, mainPath, thumbPath)
		}
	}
}
