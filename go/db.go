package main

import (
	"database/sql"
	"log"

	"encoding/json"
	_ "github.com/go-sql-driver/mysql"
	"path/filepath"
	"strings"
	"fmt"
)

func updateDatabase(itemID int, mainRaw string, thumbRaw string) {
	// 1. Convertir barras de Windows (\) a barras normales (/)
	// filepath.ToSlash es más robusto para rutas de sistema

	main := cleanPathForLaravel(mainRaw)
	thumb := cleanPathForLaravel(thumbRaw)

	// 2. LIMPIEZA DE RUTA PARA LARAVEL
	// Go ve: ../storage/app/public/items/coleccion/1/1/main.webp
	// Laravel necesita: items/coleccion/1/1/main.webp
	// Buscamos "public/" y nos quedamos con lo que sigue
	if strings.Contains(main, "public/") {
		parts := strings.Split(main, "public/")
		main = parts[len(parts)-1]
	}
	if strings.Contains(thumb, "public/") {
		parts := strings.Split(thumb, "public/")
		thumb = parts[len(parts)-1]
	}

	// 3. Serializar a JSON
	assetsMap := map[string]string{
		"main":  main,
		"thumb": thumb,
	}

	assetsJSON, err := json.Marshal(assetsMap)
	if err != nil {
		log.Printf("Error serializando JSON: %v", err)
		return
	}

	// 4. Conexión y Update
	// Asegúrate de que el nombre de la DB sea 'intranet-bpej' o 'intranet_bpej' (revisa el guion)
	dsn := "sige:50p0rt3@tcp(127.0.0.1:3306)/intranet-bpej?parseTime=true"
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Printf("Error abriendo DB: %v", err)
		return
	}
	defer db.Close()

	// Usamos el nombre de tabla que mencionaste antes: recursos_archvios (con el typo de la 'v')
	// Si ya lo corregiste en la migración, cámbialo a recursos_archivos
	query := "UPDATE recursos_archivos SET assets_procesados = ?, status = 'listo' WHERE id = ?"
	_, err = db.Exec(query, string(assetsJSON), itemID)

	if err != nil {
		log.Printf("Error actualizando ID %d: %v", itemID, err)
	} else {
		log.Printf("Registro %d actualizado (Ruta: %s)", itemID, thumb)
	}
}
func cleanPathForLaravel(rawPath string) string {
	path := filepath.ToSlash(rawPath)
	searchStr := "private/"
	if idx := strings.Index(path, searchStr); idx != -1 {
		return path[idx+len(searchStr):]
	}
	return path
}

func insertPageInDatabase(recursoID int, mainRaw string, thumbRaw string, orden int) {
	// 1. Limpieza de rutas usando tu función existente
	main := cleanPathForLaravel(mainRaw)
	thumb := cleanPathForLaravel(thumbRaw)

	// 2. Preparar el JSON de assets
	assetsMap := map[string]string{
		"main":  main,
		"thumb": thumb,
	}

	assetsJSON, err := json.Marshal(assetsMap)
	if err != nil {
		log.Printf("Error serializando JSON para página %d: %v", orden, err)
		return
	}

	// 3. Conexión (usando tu DSN actual)
	dsn := "sige:50p0rt3@tcp(127.0.0.1:3306)/intranet-bpej?parseTime=true"
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Printf("Error conectando a DB: %v", err)
		return
	}
	defer db.Close()

	// 4. Inserción de la nueva página
	// Nota: 'nombre_archivo_original' lo seteamos como la página para que sea descriptivo
	query := `
        INSERT INTO recursos_archivos 
        (recurso_id, nombre_archivo_original, assets_procesados, orden, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, 'listo', NOW(), NOW())
    `
	nombrePagina := fmt.Sprintf("Página %d", orden)

	_, err = db.Exec(query, recursoID, nombrePagina, string(assetsJSON), orden)

	if err != nil {
		log.Printf("Error insertando página %d del recurso %d: %v", orden, recursoID, err)
	} else {
		log.Printf("Página %d insertada correctamente para recurso %d", orden, recursoID)
	}
}
func createNewPageRecord(task ProcessingTask, pageNum int, mainRaw string, thumbRaw string) {
    // 1. Limpieza de rutas (usando tu función existente)
    main := cleanPathForLaravel(mainRaw)
    thumb := cleanPathForLaravel(thumbRaw)

    // 2. Preparar el JSON de assets
    assetsMap := map[string]string{
        "main":  main,
        "thumb": thumb,
    }
    assetsJSON, err := json.Marshal(assetsMap)
    if err != nil {
        log.Printf("Error serializando JSON para página %d: %v", pageNum, err)
        return
    }

    // 3. Conexión local (Exactamente igual a tu updateDatabase)
    dsn := "sige:50p0rt3@tcp(127.0.0.1:3306)/intranet-bpej?parseTime=true"
    db, err := sql.Open("mysql", dsn)
    if err != nil {
        log.Printf("Error abriendo DB: %v", err)
        return
    }
    defer db.Close()

    // 4. Inserción
    // Usamos 'recursos_archivos' (ajusta si el typo 'archvios' sigue ahí)
    query := `INSERT INTO recursos_archivos 
              (recurso_id, nombre_archivo_original, assets_procesados, orden, status, created_at, updated_at) 
              VALUES (?, ?, ?, ?, 'listo', NOW(), NOW())`
    
    nombre := fmt.Sprintf("Página %d", pageNum)
    _, err = db.Exec(query, task.RecursoID, nombre, string(assetsJSON), pageNum)

    if err != nil {
        log.Printf("Error insertando página %d: %v", pageNum, err)
    } else {
        log.Printf("Página %d del recurso %d insertada correctamente", pageNum, task.RecursoID)
    }
}