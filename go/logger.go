package main

import (
	"fmt"
	"log"
	"os"
	"path/filepath"
)

var (
	logListos  *log.Logger
	logErrores *log.Logger

	fileListos  *os.File
	fileErrores *os.File
)

func initLoggers(logDir string) error {
	if err := os.MkdirAll(logDir, 0755); err != nil {
		return fmt.Errorf("no se pudo crear directorio de logs %s: %w", logDir, err)
	}

	var err error
	fileListos, err = os.OpenFile(filepath.Join(logDir, "listos.log"), os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		return fmt.Errorf("error abriendo listos.log: %w", err)
	}

	fileErrores, err = os.OpenFile(filepath.Join(logDir, "errores.log"), os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		fileListos.Close()
		return fmt.Errorf("error abriendo errores.log: %w", err)
	}

	// log.LstdFlags agrega automáticamente fecha y hora (2026/11/16 11:55:00)
	logListos = log.New(fileListos, "", log.LstdFlags)
	logErrores = log.New(fileErrores, "", log.LstdFlags)

	return nil
}

func closeLoggers() {
	if fileListos != nil {
		fileListos.Close()
	}
	if fileErrores != nil {
		fileErrores.Close()
	}
}

// Helpers limpios para registrar cada resultado
func registrarListo(tipo string, archivoID int, recursoID int, detalle string) {
	if logListos != nil {
		logListos.Printf("[%s] ArchivoID=%d | RecursoID=%d | %s", tipo, archivoID, recursoID, detalle)
	}
}

func registrarError(tipo string, archivoID int, recursoID int, path string, motivo string) {
	if logErrores != nil {
		logErrores.Printf("[%s] ArchivoID=%d | RecursoID=%d | Path=%s | Motivo: %s", tipo, archivoID, recursoID, path, motivo)
	}
}