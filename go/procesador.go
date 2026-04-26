package main

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

func processImage(task ProcessingTask) {
	// Normalizamos rutas para evitar errores de escape en JSON
	source := strings.ReplaceAll(task.Path, "\\", "/")

	// Estructura: public/items/slug-coleccion/id-recurso/id-archivo/
	// Cambia esto en tu procesador de Go:
	basePath := "/var/www/html/bpej/storage/app/private"
	outputDir := filepath.Join(basePath, task.ColeccionSlug,
		fmt.Sprintf("%d", task.RecursoID),
		fmt.Sprintf("%d", task.ArchivoID))

	os.MkdirAll(outputDir, 0755)

	thumbPath := filepath.Join(outputDir, "thumb.webp")
	mainPath := filepath.Join(outputDir, "main.webp")

	// Ejecutar ImageMagick
	exec.Command("magick", source, "-quality", "80", mainPath).Run()
	exec.Command("magick", source, "-thumbnail", "200x200^", "-gravity", "center", "-extent", "200x200", thumbPath).Run()

	// Actualizamos la DB usando el ArchivoID
	updateDatabase(task.ArchivoID, mainPath, thumbPath)
}
