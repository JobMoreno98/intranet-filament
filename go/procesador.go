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
	// filepath.Ext devuelve ".pdf", ".jpg", etc.
	ext := strings.ToLower(filepath.Ext(task.Path))
	log.Printf("Detectado tipo  por extensión: %s", ext)
	if ext == ".pdf" { // <--- Agregamos el punto
		log.Printf("Detectado tipo PDF por extensión: %s", ext)
		processPdf(task)
	} else {
		log.Printf("Detectado tipo Imagen por extensión: %s", ext)
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

		os.MkdirAll(pageDir, 0755)

		// Nombre temporal único para evitar colisiones de buffer
		tempName := fmt.Sprintf("temp_page_%d", i)
		outputBase := filepath.Join(pageDir, tempName)
		actualWebp := outputBase + ".webp"

		finalMain := filepath.Join(pageDir, "main.webp")
		thumbPath := filepath.Join(pageDir, "thumb.webp")

		// 2. Extraer página (ORDEN DE ARGUMENTOS CRÍTICO)
		// Ponemos los flags de página ANTES del formato y la escala
		extractCmd := exec.Command("pdftocairo",
			"-f", strconv.Itoa(i),
			"-l", strconv.Itoa(i),
			"-webp",
			"-singlefile",
			"-scale-to-x", "2000",
			task.Path,
			outputBase)

		if err := extractCmd.Run(); err != nil {
			log.Printf("Error pág %d: %v", i, err)
			continue
		}

		// 3. Pequeña pausa para asegurar que el SO soltó el archivo (100ms)
		// Especialmente útil si el storage es vía red (NFS/SMB)
		time.Sleep(100 * time.Millisecond)

		// 4. Renombrar a main.webp (Esto confirma que el archivo es único)
		if err := os.Rename(actualWebp, finalMain); err != nil {
			log.Printf("Error rename pág %d: %v", i, err)
			continue
		}

		// 5. Marca de Agua con ajuste de tamaño (SVG)
		exec.Command("magick", finalMain,
			"-background", "none", "-resize", "350x", watermark,
			"-gravity", "south-east", "-geometry", "+50+50",
			"-composite", finalMain).Run()

		// 6. Thumbnail
		exec.Command("magick", finalMain,
			"-thumbnail", "200x200^", "-gravity", "center",
			"-extent", "200x200", "-quality", "70",
			thumbPath).Run()

		// 7. Base de Datos
		if i == 1 {
			updateDatabase(task.ArchivoID, finalMain, thumbPath)
		} else {
			createNewPageRecord(task, i, finalMain, thumbPath)
		}
	}
	log.Printf("Finalizado: %s (%d páginas)", task.Path, totalPages)
}
