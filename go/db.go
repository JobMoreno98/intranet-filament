package main

import (
	"database/sql"
	"log"

	"encoding/json"
	_ "github.com/go-sql-driver/mysql"
	"path/filepath"
	"strings"
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
