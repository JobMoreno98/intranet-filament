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
		// 1. Definimos la carpeta de la página
		pageDir := filepath.Join(basePath, task.ColeccionSlug,
			strconv.Itoa(task.RecursoID),
			fmt.Sprintf("p%d", i))

		os.MkdirAll(pageDir, 0755)

		// 2. NOMBRE ÚNICO: En lugar de "main", usamos "page_1", "page_2", etc.
		// Esto evita que pdftocairo se confunda con archivos previos
		fileName := fmt.Sprintf("page_%d", i)
		outputBase := filepath.Join(pageDir, fileName)

		// Rutas finales que usaremos en la DB y Magick
		actualWebpPath := filepath.Join(pageDir, fileName+".webp")
		finalMainPath := filepath.Join(pageDir, "main.webp") // El nombre que Laravel espera
		thumbPath := filepath.Join(pageDir, "thumb.webp")

		// 3. Extraer página con pdftocairo
		extractCmd := exec.Command("pdftocairo", "-webp", "-singlefile",
			"-f", strconv.Itoa(i),
			"-l", strconv.Itoa(i),
			"-scale-to-x", "2000",
			task.Path, outputBase)

		if err := extractCmd.Run(); err != nil {
			log.Printf("Error extrayendo página %d: %v", i, err)
			continue
		}

		// 4. RENOMBRAR a main.webp inmediatamente
		// Esto asegura que el archivo esté disponible y "cerrado" por el sistema
		err := os.Rename(actualWebpPath, finalMainPath)
		if err != nil {
			log.Printf("Error renombrando página %d: %v", i, err)
			continue
		}

		// 5. Aplicar Marca de Agua sobre el archivo final
		watermarkCmd := exec.Command("magick",
			finalMainPath,
			"-background", "none", "-resize", "300x", watermark,
			"-gravity", "south-east",
			"-geometry", "+50+50",
			"-composite",
			"-quality", "80",
			finalMainPath)
		watermarkCmd.Run()

		// 6. Generar Thumbnail
		exec.Command("magick", finalMainPath,
			"-thumbnail", "200x200^",
			"-gravity", "center",
			"-extent", "200x200",
			"-quality", "70",
			thumbPath).Run()

		// 7. Guardado en DB
		if i == 1 {
			updateDatabase(task.ArchivoID, finalMainPath, thumbPath)
		} else {
			createNewPageRecord(task, i, finalMainPath, thumbPath)
		}
	}
	log.Printf("--- Finalizado PDF ID: %d ---", task.RecursoID)
}
