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
	case ".mp4", ".mov", ".mkv", ".avi", ".webm", ".m4v":
		log.Printf(">>> RUTINA: VIDEO DETECTADO <<<")
		processVideo(task)
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
binary := "magick"

if _, err := exec.LookPath(binary); err != nil {
    binary = "convert"
}
	// Argumento final: la ruta de destino (forzando formato webp)
	args = append(args, "webp:"+mainPath)

	// Ejecutamos el comando con los argumentos dinámicos
	cmd := exec.Command(binary, args...)

	// Captura de errores
	if out, err := cmd.CombinedOutput(); err != nil {
		log.Printf("ERROR REAL DE MAGICK en ID %d: %s", task.ArchivoID, string(out))
	}

	// Generar Miniatura (Thumbnail)
	// Nota: Aquí usamos 'source' para que la miniatura no tenga marca de agua y sea más clara
	exec.Command(binary, source,
		"-thumbnail", "200x200^",
		"-gravity", "center",
		"-extent", "200x200",
		"-quality", "70",
		thumbPath).Run()

	// Actualizamos la DB
	updateDatabase(task.ArchivoID, mainPath, thumbPath)
}

func processVideo(task ProcessingTask) {
	log.Printf("--- Iniciando VIDEO: %s ---", task.Path)

	// Reintento de existencia (igual que en processImage)
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
		updateArchivoStatus(task.ArchivoID, "error")
		return
	}

	// La key, el .keyinfo y la URL firmada los genera Laravel ANTES de encolar
	// la tarea (necesita APP_KEY para firmar la ruta). Go solo necesita la
	// ruta al .keyinfo que PHP ya dejó escrito en disco.
	if task.KeyInfoPath == "" {
		log.Printf("ERROR CRÍTICO: KeyInfoPath vacío para ID %d", task.ArchivoID)
		updateArchivoStatus(task.ArchivoID, "error")
		return
	}
	if _, err := os.Stat(task.KeyInfoPath); err != nil {
		log.Printf("ERROR CRÍTICO: .keyinfo no existe en %s", task.KeyInfoPath)
		updateArchivoStatus(task.ArchivoID, "error")
		return
	}

	outputDir := filepath.Join(basePath, "encrypted", task.OutputName)
	if err := os.MkdirAll(outputDir, 0755); err != nil {
		log.Printf("ERROR CRÍTICO: no se pudo crear %s: %v", outputDir, err)
		updateArchivoStatus(task.ArchivoID, "error")
		return
	}

	// 1. Detectar códec de vídeo con ffprobe
	probeCmd := exec.Command("ffprobe",
		"-v", "error",
		"-select_streams", "v:0",
		"-show_entries", "stream=codec_name",
		"-of", "default=noprint_wrappers=1:nokey=1",
		task.Path,
	)
	probeOut, err := probeCmd.Output()
	codec := "unknown"
	if err != nil {
		log.Printf("WARN ffprobe ID %d: %v (se transcodificará por seguridad)", task.ArchivoID, err)
	} else {
		codec = strings.TrimSpace(string(probeOut))
	}

	useCopy := codec == "h264"

	args := []string{
		"-y",
		"-loglevel", "error",
		"-i", task.Path,
	}

	if useCopy {
		// Ya es H.264: solo remux (rápido)
		args = append(args, "-c", "copy")
	} else {
		// Otro formato: transcodificar a H.264/AAC
		args = append(args,
			"-c:v", "libx264",
			"-crf", "23",
			"-preset", "veryfast",
			"-c:a", "aac",
		)
	}

	args = append(args,
		"-hls_time", "10",
		"-hls_playlist_type", "vod",
		"-hls_key_info_file", task.KeyInfoPath,
		"-hls_segment_filename", filepath.Join(outputDir, "segment_%03d.ts"),
		filepath.Join(outputDir, task.OutputName+".m3u8"),
	)

	cmd := exec.Command("ffmpeg", args...)
	if out, err := cmd.CombinedOutput(); err != nil {
		log.Printf("ERROR ffmpeg ID %d: %v | output: %s", task.ArchivoID, err, string(out))
		updateArchivoStatus(task.ArchivoID, "error")
		return
	}

	// Generar thumbnail ANTES de borrar el original: el HLS ya quedó
	// cifrado y no es trivial re-leerlo con ffmpeg sin pasar la key.
	thumbPath := filepath.Join(outputDir, "thumb.webp")
	thumbOk := true
	thumbCmd := exec.Command("ffmpeg",
		"-y",
		"-loglevel", "error",
		"-ss", "1",
		"-i", task.Path,
		"-frames:v", "1",
		"-vf", "scale=200:200:force_original_aspect_ratio=increase,crop=200:200",
		"-q:v", "80",
		thumbPath,
	)
	if out, err := thumbCmd.CombinedOutput(); err != nil {
		log.Printf("WARN: no se pudo generar thumbnail ID %d: %v | output: %s", task.ArchivoID, err, string(out))
		thumbOk = false
	}

	m3u8Path := filepath.Join(outputDir, task.OutputName+".m3u8")
	if thumbOk {
		updateVideoAssets(task.ArchivoID, m3u8Path, thumbPath)
	} else {
		updateVideoAssets(task.ArchivoID, m3u8Path, "")
	}
	log.Printf("--- Finalizado VIDEO: %s ---", task.OutputName)
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
binary := "magick"

if _, err := exec.LookPath(binary); err != nil {
    binary = "convert"
}
		// 3. Magick: Marca de agua + Conversión a WebP
		// Usamos el PNG como fuente y guardamos directamente en .webp
		watermarkCmd := exec.Command(binary,
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
		exec.Command(binary, finalWebp,
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