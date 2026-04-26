package main

type ProcessingTask struct {
    ArchivoID     int    `json:"archivo_id"`  // ID de la tabla recursos_archvios
    RecursoID     int    `json:"recurso_id"`  // ID del padre (para la carpeta)
    Path          string `json:"path"`        // Ruta absoluta enviada por Laravel
    ColeccionSlug string `json:"coleccion_slug"`
    Tipo          string `json:"tipo"`
}