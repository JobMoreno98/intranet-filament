package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

// OcrTask es la tarea que Laravel manda a la cola "bpej_cola_ocr" para volver
// a ejecutar el OCR de archivos que YA están procesados.
//
// Basta uno de los dos IDs:
//   - archivo_id: una sola fila de recursos_archivos (una imagen o una página).
//   - recurso_id: todas las páginas/archivos de ese recurso, en orden.
//
// langs es opcional (ej. "spa+eng"); si falta se usan los idiomas por defecto.
type OcrTask struct {
	ArchivoID int    `json:"archivo_id,omitempty"`
	RecursoID int    `json:"recurso_id,omitempty"`
	Langs     string `json:"langs,omitempty"`
}

var defaultOcrLangs = []string{"spa", "eng"}

type archivoOcr struct {
	ID        int
	RecursoID int
	Main      string // ruta relativa a storage/app/private, tal como se guardó en assets_procesados
}

// reprocessOcr toma el main.webp ya generado de cada archivo (no hace falta el
// original ni volver a renderizar el PDF), corre Tesseract y actualiza
// únicamente la columna ocr, ocr.json y ocr.txt. No toca status ni assets.
func reprocessOcr(task OcrTask) {
	langs := parseOcrLangs(task.Langs)
	log.Printf("REOCR: archivo=%d recurso=%d idiomas=%v", task.ArchivoID, task.RecursoID, langs)

	archivos, err := getArchivosParaOcr(task)
	if err != nil {
		log.Printf("REOCR: error consultando la base de datos: %v", err)
		return
	}
	if len(archivos) == 0 {
		log.Printf("REOCR: no hay archivos con imagen procesada para esta tarea")
		return
	}

	recursosActualizados := map[int]bool{}
	base := filepath.Clean(basePath)

	for _, a := range archivos {
		mainAbs := filepath.Clean(filepath.Join(basePath, filepath.FromSlash(a.Main)))

		// Defensa básica: la ruta debe quedar dentro de storage/app/private.
		if !strings.HasPrefix(mainAbs, base+string(filepath.Separator)) {
			log.Printf("REOCR archivo %d: ruta fuera de %s, se omite: %s", a.ID, base, mainAbs)
			continue
		}
		if _, err := os.Stat(mainAbs); err != nil {
			log.Printf("REOCR archivo %d: no existe %s", a.ID, mainAbs)
			continue
		}

		outputDir := filepath.Dir(mainAbs) // misma carpeta donde ya viven ocr.json y ocr.txt

		texto, err := runOcrWithLangs(fmt.Sprintf("REOCR archivo %d", a.ID), mainAbs, outputDir, langs)
		if err != nil {
			log.Printf("REOCR archivo %d: %v", a.ID, err)
			continue
		}
		if texto == "" {
			// Si Tesseract no detecta nada, se conserva el OCR anterior.
			log.Printf("REOCR archivo %d: sin texto detectado, se conserva el anterior", a.ID)
			continue
		}

		if err := updateOcrText(a.ID, texto); err != nil {
			log.Printf("REOCR archivo %d: no se pudo guardar el texto: %v", a.ID, err)
			continue
		}
		log.Printf("REOCR archivo %d: OK (%d caracteres)", a.ID, len(texto))
		recursosActualizados[a.RecursoID] = true
	}

	// Un solo aviso por recurso; el worker de Laravel ya hace debounce.
	for recursoID := range recursosActualizados {
		pushOcrReindexQueue(recursoID)
	}
	log.Printf("REOCR terminado: %d recurso(s) actualizados", len(recursosActualizados))
}

func getArchivosParaOcr(task OcrTask) ([]archivoOcr, error) {
	db, err := getDB()
	if err != nil {
		return nil, err
	}

	var rows *sql.Rows
	switch {
	case task.ArchivoID > 0:
		rows, err = db.Query(
			"SELECT id, recursos_id, assets_procesados FROM recursos_archivos WHERE id = ?",
			task.ArchivoID)
	case task.RecursoID > 0:
		rows, err = db.Query(
			"SELECT id, recursos_id, assets_procesados FROM recursos_archivos WHERE recursos_id = ? ORDER BY orden, id",
			task.RecursoID)
	default:
		return nil, fmt.Errorf("la tarea no trae archivo_id ni recurso_id")
	}
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var lista []archivoOcr
	for rows.Next() {
		var id int
		var recursoID sql.NullInt64
		var assets sql.NullString
		if err := rows.Scan(&id, &recursoID, &assets); err != nil {
			return nil, err
		}
		if !assets.Valid || assets.String == "" {
			continue
		}

		var m map[string]string
		if err := json.Unmarshal([]byte(assets.String), &m); err != nil {
			continue
		}
		// Videos y audios no tienen "main" (solo "video"/"audio"), se saltan solos.
		if m["main"] == "" {
			continue
		}

		rid := task.RecursoID
		if recursoID.Valid {
			rid = int(recursoID.Int64)
		}
		lista = append(lista, archivoOcr{ID: id, RecursoID: rid, Main: m["main"]})
	}
	return lista, rows.Err()
}

func updateOcrText(archivoID int, texto string) error {
	db, err := getDB()
	if err != nil {
		return err
	}
	_, err = db.Exec("UPDATE recursos_archivos SET ocr = ?, updated_at = NOW() WHERE id = ?", texto, archivoID)
	return err
}

// parseOcrLangs acepta "spa+eng", "spa,eng" o "spa eng".
func parseOcrLangs(s string) []string {
	s = strings.NewReplacer("+", ",", " ", ",").Replace(s)
	var langs []string
	for _, l := range strings.Split(s, ",") {
		if l = strings.TrimSpace(l); l != "" {
			langs = append(langs, l)
		}
	}
	if len(langs) == 0 {
		return defaultOcrLangs
	}
	return langs
}

// checkOcrLanguages confirma que Tesseract, con el mismo entorno del worker
// (incluido TESSDATA_PREFIX), puede ver los idiomas que usamos.
func checkOcrLanguages() error {
	out, err := exec.Command("tesseract", "--list-langs").CombinedOutput()
	if err != nil {
		return fmt.Errorf("no se pudo ejecutar 'tesseract --list-langs': %v (%s)", err, strings.TrimSpace(string(out)))
	}

	disponibles := map[string]bool{}
	for _, l := range strings.Split(string(out), "\n") {
		disponibles[strings.TrimSpace(l)] = true
	}

	var faltan []string
	for _, l := range defaultOcrLangs {
		if !disponibles[l] {
			faltan = append(faltan, l)
		}
	}
	if len(faltan) > 0 {
		return fmt.Errorf("a Tesseract le faltan los idiomas %v (TESSDATA_PREFIX=%q). Salida de --list-langs: %s",
			faltan, os.Getenv("TESSDATA_PREFIX"), strings.TrimSpace(string(out)))
	}
	return nil
}
