package main

type ProcessingTask struct {
	ArchivoID     int    `json:"archivo_id"`  // ID de la tabla recursos_archvios
	RecursoID     int    `json:"recurso_id"`  // ID del padre (para la carpeta)
	Path          string `json:"path"`        // Ruta absoluta enviada por Laravel
	ColeccionSlug string `json:"coleccion_slug"`
	Tipo          string `json:"tipo"`

	// Acción que disparó el encolado: "create" o "update".
	// No se usa todavía en processTask, pero Laravel ya lo manda.
	Action string `json:"action,omitempty"`

	// --- Campos exclusivos de video ---
	// OutputName: nombre base para el .m3u8 y los segmentos .ts (usamos
	// el ID del archivo como string, igual que outputName en el Job original).
	OutputName string `json:"output_name,omitempty"`

	// KeyInfoPath: ruta absoluta al .keyinfo que Laravel ya generó antes
	// de encolar (incluye la key AES-128 + la URL firmada). Go NUNCA
	// genera ni firma esto — solo lo consume para pasárselo a ffmpeg.
	KeyInfoPath string `json:"key_info_path,omitempty"`
}