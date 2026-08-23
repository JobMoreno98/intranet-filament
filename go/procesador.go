package main

import (
	"encoding/json"
	"fmt"
	"log"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"strconv"
	"strings"
	"time"
	"unicode"

	"github.com/otiai10/gosseract/v2"
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
	case ".mp3", ".wav", ".m4a", ".ogg", ".flac", ".aac":
		log.Printf(">>> RUTINA: AUDIO DETECTADO <<<")
		processAudio(task)
	default:
		log.Printf("ERROR: Extensión '%s' no soportada para ID %d", ext, task.ArchivoID)
	}
	log.Printf("------------------------------------------------")
}

func processImage(task ProcessingTask) {

	// 1. Limpiamos la ruta de posibles espacios o saltos de línea invisibles
	cleanPath := strings.TrimSpace(task.Path)

	exists := false
	// 2. Aumentamos a 20 intentos (10 segundos total) para dar tiempo al backend a ensamblar el archivo
	for i := 0; i < 20; i++ {
		if _, err := os.Stat(cleanPath); err == nil {
			exists = true
			break
		}
		log.Printf("Archivo no listo para ID %d, reintentando en 500ms...", task.ArchivoID)
		time.Sleep(500 * time.Millisecond)
	}

	if !exists {
		log.Printf("ERROR CRÍTICO: El archivo nunca apareció en %s", cleanPath)
		return
	}

	// Actualizamos variables para usar cleanPath de aquí en adelante
	source := strings.ReplaceAll(cleanPath, "\\", "/")

	// Estructura: private/slug-coleccion/id-recurso/id-archivo/
	outputDir := filepath.Join(basePath, task.ColeccionSlug,
		strconv.Itoa(task.RecursoID),
		strconv.Itoa(task.ArchivoID))

	os.MkdirAll(outputDir, 0755)

	// --- INICIO RUTINA OCR ESPACIAL ---
	log.Printf(">>> Iniciando OCR para ID %d <<<", task.ArchivoID)

	textoOcr, err := runOcr(fmt.Sprintf("imagen ID %d", task.ArchivoID), cleanPath, outputDir)
	if err != nil {
		log.Printf("ADVERTENCIA OCR ID %d: %v", task.ArchivoID, err)
		textoOcr = ""
	}
	// --- FIN RUTINA OCR ---

	thumbPath := filepath.Join(outputDir, "thumb.webp")
	mainPath := filepath.Join(outputDir, "main.webp")
	watermark := "/var/www/html/bpej/public/img/logo.svg" // Definición de tu marca de agua

	// Construimos los argumentos de Magick integrando la marca de agua
	args := []string{
		cleanPath,
		"-resize", "2500x>", // Redimensiona primero
		"-background", "none", 
		"-size", "150x", watermark, // Carga la marca de agua
		"-gravity", "south-east", "-geometry", "+50+50", // La posiciona
		"-composite", // Las fusiona
		"-quality", "80",
	}

	binary := "magick"
	if _, err := exec.LookPath(binary); err != nil {
		binary = "convert"
	}

	args = append(args, "webp:"+mainPath)

	cmd := exec.Command(binary, args...)

	if out, err := cmd.CombinedOutput(); err != nil {
		log.Printf("ERROR REAL DE MAGICK en ID %d: %s", task.ArchivoID, string(out))
	}

	// Generar Miniatura (Se genera desde la fuente original para que el thumbnail quede limpio)
	exec.Command(binary, source,
		"-thumbnail", "200x200^",
		"-gravity", "center",
		"-extent", "200x200",
		"-quality", "70",
		thumbPath).Run()

	updateDatabase(task.ArchivoID, mainPath, thumbPath, textoOcr)

	if textoOcr != "" {
		pushOcrReindexQueue(task.RecursoID)
	}
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

	hilos := runtime.NumCPU() - 2

	if hilos < 1 {
		hilos = 1 // Asegura que siempre use al menos 1 núcleo si el servidor es muy pequeño
	}

	args := []string{
		"-y",
		"-loglevel", "error",
		"-threads", strconv.Itoa(hilos), // 2. Se lo pasamos dinámicamente a FFmpeg
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

func processAudio(task ProcessingTask) {
	log.Printf("--- Iniciando AUDIO: %s ---", task.Path)

	// Reintento de existencia (igual que en processVideo/processImage)
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

	// Mismo esquema de cifrado que video: Laravel ya generó la key,
	// el .keyinfo y la URL firmada antes de encolar la tarea.
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

	// 1. Detectar códec de audio con ffprobe
	probeCmd := exec.Command("ffprobe",
		"-v", "error",
		"-select_streams", "a:0",
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

	useCopy := codec == "aac"

	hilos := runtime.NumCPU() - 2
	if hilos < 1 {
		hilos = 1 // Asegura que siempre use al menos 1 núcleo si el servidor es muy pequeño
	}

	args := []string{
		"-y",
		"-loglevel", "error",
		"-threads", strconv.Itoa(hilos), // 2. Se lo pasamos dinámicamente a FFmpeg
		"-i", task.Path,
		"-vn",
	}

	if useCopy {
		// Ya es AAC: solo remux (rápido)
		args = append(args, "-c:a", "copy")
	} else {
		// Otro formato (mp3, wav, ogg, flac...): transcodificar a AAC
		args = append(args, "-c:a", "aac", "-b:a", "128k")
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

	// Generar una miniatura tipo "waveform". Si falla, no es crítico:
	// el audio igual queda listo, solo sin thumb.
	thumbPath := filepath.Join(outputDir, "thumb.webp")
	thumbOk := true
	
	thumbCmd := exec.Command("ffmpeg",
		"-y",
		"-loglevel", "error",
		"-i", task.Path,
		"-an", // Ignoramos el audio, solo queremos la imagen
		"-frames:v", "1", // Extraemos solo el primer fotograma (la portada)
		// CORRECCIÓN: Comillas ajustadas en el filtro scale
		"-vf", "scale='min(800,iw)':'min(800,ih)':force_original_aspect_ratio=decrease,pad=ceil(iw/2)*2:ceil(ih/2)*2",
		"-q:v", "80",
		thumbPath,
	)

	if _, err := thumbCmd.CombinedOutput(); err != nil {
		log.Printf("WARN: El MP3 no tiene portada o falló la extracción ID %d: %v", task.ArchivoID, err)
		
		// FALLBACK: Si falla (porque el MP3 no tiene imagen), generamos el waveform como respaldo
		thumbCmd = exec.Command("ffmpeg",
			"-y",
			"-loglevel", "error",
			"-i", task.Path,
			"-filter_complex", "showwavespic=s=800x450:colors=0x4A4A4A",
			"-frames:v", "1",
			"-q:v", "80",
			thumbPath,
		)
		if _, err := thumbCmd.CombinedOutput(); err != nil {
			thumbOk = false
		}
	}

	m3u8Path := filepath.Join(outputDir, task.OutputName+".m3u8")
	if thumbOk {
		updateAudioAssets(task.ArchivoID, m3u8Path, thumbPath)
	} else {
		updateAudioAssets(task.ArchivoID, m3u8Path, "")
	}
	log.Printf("--- Finalizado AUDIO: %s ---", task.OutputName)
}

// runOcr corre Tesseract sobre una imagen (foto suelta o página renderizada de
// un PDF), guarda las coordenadas por palabra en ocr.json y el texto plano en
// ocr.txt dentro de outputDir, y devuelve ese texto plano para que el llamador
// lo suba a la base de datos y dispare el reindexado en Meilisearch.
// 'label' es solo para identificar la fuente en los logs (ID de imagen, o
// "PDF X pág Y").
func runOcr(label string, sourceImagePath, outputDir string) (string, error) {
	ocrSourcePath, ocrScale, tempOcrFile, err := prepareImageForOcr(sourceImagePath, outputDir)
	if err != nil {
		log.Printf("ADVERTENCIA OCR %s: no se pudo preprocesar (%v), se usa la imagen original", label, err)
		ocrSourcePath, ocrScale, tempOcrFile = sourceImagePath, 1.0, ""
	}
	if tempOcrFile != "" {
		defer os.Remove(tempOcrFile)
	}

	client := gosseract.NewClient()
	defer client.Close()

	client.SetImage(ocrSourcePath)
	client.SetLanguage("spa", "eng")
	client.SetPageSegMode(gosseract.PSM_AUTO)
	client.SetVariable("preserve_interword_spaces", "1")

	// Extraer coordenadas por palabra
	boxes, err := client.GetBoundingBoxes(gosseract.RIL_WORD)
	if err != nil {
		return "", fmt.Errorf("no se pudo extraer texto: %v", err)
	}
	if len(boxes) == 0 {
		return "", nil
	}

	const confianzaMinima = 55.0

	limpias := make([]gosseract.BoundingBox, 0, len(boxes))
	var textoPlano strings.Builder

	for _, b := range boxes {
		palabra := strings.TrimSpace(b.Word)

		// Descarta ruido típico: vacíos, baja confianza, o "palabras"
		// sin ninguna letra/dígito (puros símbolos sueltos = basura de OCR)
		if palabra == "" || b.Confidence < confianzaMinima || !contieneLetraODigito(palabra) {
			continue
		}

		// Si se preprocesó a mayor resolución, regresamos las coordenadas
		// a la escala de la imagen ORIGINAL (la que se muestra en el visor)
		if ocrScale != 1.0 {
			b.Box.Min.X = int(float64(b.Box.Min.X) / ocrScale)
			b.Box.Min.Y = int(float64(b.Box.Min.Y) / ocrScale)
			b.Box.Max.X = int(float64(b.Box.Max.X) / ocrScale)
			b.Box.Max.Y = int(float64(b.Box.Max.Y) / ocrScale)
		}

		b.Word = palabra
		limpias = append(limpias, b)
		textoPlano.WriteString(palabra)
		textoPlano.WriteString(" ")
	}

	if len(limpias) == 0 {
		return "", nil
	}

	// Guardar coordenadas en JSON (usadas por el overlay del visor)
	if jsonData, jsonErr := json.Marshal(limpias); jsonErr == nil {
		jsonPath := filepath.Join(outputDir, "ocr.json")
		os.WriteFile(jsonPath, jsonData, 0644)
		log.Printf("OCR JSON guardado en %s (%d palabras, %d descartadas)",
			jsonPath, len(limpias), len(boxes)-len(limpias))
	}

	texto := strings.TrimSpace(textoPlano.String())

	// Guardar texto plano también en disco (compatibilidad con lo que ya tenías)
	txtPath := filepath.Join(outputDir, "ocr.txt")
	os.WriteFile(txtPath, []byte(texto), 0644)

	return texto, nil
}

// prepareImageForOcr genera una copia optimizada para Tesseract (escala de
// grises + contraste + nitidez) sin tocar la geometría, para que las
// coordenadas del OCR sigan alineadas con la imagen original. Si la imagen
// es pequeña la escala 2x (mejora mucho la precisión) y devuelve el factor
// para revertir las coordenadas después.
func prepareImageForOcr(sourcePath, outputDir string) (path string, scale float64, tempFile string, err error) {
	binary := "magick"
	if _, lookErr := exec.LookPath(binary); lookErr != nil {
		binary = "convert"
	}

	scale = 1.0
	if w, wErr := imageWidthPx(sourcePath); wErr == nil && w > 0 && w < 1600 {
		scale = 2.0
	}

	args := []string{sourcePath}
	if scale != 1.0 {
		args = append(args, "-resize", fmt.Sprintf("%d%%", int(scale*100)))
	}

	args = append(args,
		"-colorspace", "Gray",
		"-normalize",
		"-sharpen", "0x1.2",
	)

	tempFile = filepath.Join(outputDir, "ocr_source.png")
	args = append(args, tempFile)

	cmd := exec.Command(binary, args...)
	if out, cmdErr := cmd.CombinedOutput(); cmdErr != nil {
		return "", 1.0, "", fmt.Errorf("preprocesamiento OCR: %v (%s)", cmdErr, string(out))
	}

	return tempFile, scale, tempFile, nil
}

// imageWidthPx obtiene el ancho en píxeles de una imagen usando ImageMagick
// (identify o "magick identify" según la instalación disponible).
func imageWidthPx(path string) (int, error) {
	cmd := exec.Command("identify", "-format", "%w", path)
	out, err := cmd.Output()
	if err != nil {
		cmd = exec.Command("magick", "identify", "-format", "%w", path)
		out, err = cmd.Output()
		if err != nil {
			return 0, err
		}
	}
	return strconv.Atoi(strings.TrimSpace(string(out)))
}

// contieneLetraODigito descarta "palabras" que Tesseract detecta pero que
// son puro ruido: símbolos sueltos, manchas interpretadas como puntuación, etc.
func contieneLetraODigito(s string) bool {
	for _, r := range s {
		if unicode.IsLetter(r) || unicode.IsDigit(r) {
			return true
		}
	}
	return false
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

		// 4. OCR de la página (usa el PNG sin marca de agua, más limpio para Tesseract,
		// justo antes de borrarlo)
		textoOcr, ocrErr := runOcr(fmt.Sprintf("PDF recurso %d pág %d", task.RecursoID, i), actualPng, pageDir)
		if ocrErr != nil {
			log.Printf("ADVERTENCIA OCR recurso %d pág %d: %v", task.RecursoID, i, ocrErr)
			textoOcr = ""
		}

		// 5. Generar Thumbnail (desde el WebP ya procesado)
		exec.Command(binary, finalWebp,
			"-thumbnail", "200x200^",
			"-gravity", "center",
			"-extent", "200x200",
			"-quality", "70",
			thumbPath).Run()

		// 6. Limpieza: Eliminar el PNG temporal para ahorrar espacio
		os.Remove(actualPng)

		// 7. Registro en Base de Datos
		if i == 1 {
			updateDatabase(task.ArchivoID, finalWebp, thumbPath, textoOcr)
		} else {
			if _, err := createNewPageRecord(task, i, finalWebp, thumbPath, textoOcr); err != nil {
				log.Printf("No se pudo insertar la página %d del recurso %d: %v", i, task.RecursoID, err)
			}
		}

		// El texto se junta por libro completo en Meilisearch (no por página),
		// así que basta con avisarle a Laravel el recursos_id; el worker de
		// Laravel hace debounce para no reindexar una vez por cada página.
		if textoOcr != "" {
			pushOcrReindexQueue(task.RecursoID)
		}
	}
	log.Printf("--- Finalizado PDF: %d ---", task.RecursoID)
}