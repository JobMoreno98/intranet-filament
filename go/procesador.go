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

	// Procesamiento de imagen individual
	exec.Command("magick", source, "-quality", "80", mainPath).Run()
	exec.Command("magick", source, "-thumbnail", "200x200^", "-gravity", "center", "-extent", "200x200", thumbPath).Run()

	// Actualizamos el registro que Laravel ya creó
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

    // 1. Validar si el archivo existe
    if _, err := os.Stat(task.Path); os.IsNotExist(err) {
        log.Printf("ERROR: El archivo PDF no existe en la ruta: %s", task.Path)
        return
    }

    // 2. Obtener páginas con log de error
    cmdInfo := exec.Command("pdfinfo", task.Path)
    out, err := cmdInfo.CombinedOutput()
    if err != nil {
        log.Printf("ERROR en pdfinfo: %v | Salida: %s", err, string(out))
        return
    }

    totalPages := extractPages(string(out))
    log.Printf("Páginas detectadas: %d", totalPages)

    if totalPages == 0 {
        log.Printf("ERROR: No se detectaron páginas. ¿El PDF está corrupto o protegido?")
        return
    }

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
