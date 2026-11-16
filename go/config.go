package main

import (
	"fmt"
	"os"
	"path/filepath"
	"runtime"

	"github.com/caarlos0/env/v11"
	"github.com/joho/godotenv"
)

type Secret string

func (s Secret) String() string {
	if s == "" {
		return ""
	}
	return "[REDACTED]"
}
func (s Secret) GoString() string { return s.String() }
func (s Secret) Value() string    { return string(s) }

type AppConfig struct {
	// Variables nativas de Base de Datos en Laravel (.env)
	DBHost string `env:"DB_HOST" envDefault:"127.0.0.1"`
	DBPort int    `env:"DB_PORT" envDefault:"3306"`
	DBName string `env:"DB_DATABASE,required"`
	DBUser string `env:"DB_USERNAME,required"`
	DBPass Secret `env:"DB_PASSWORD"`

	// Variables nativas de Redis en Laravel (.env)
	RedisHost     string `env:"REDIS_HOST" envDefault:"127.0.0.1"`
	RedisPort     string `env:"REDIS_PORT" envDefault:"6379"`
	RedisPassword Secret `env:"REDIS_PASSWORD"`
	RedisDB       int    `env:"REDIS_DB" envDefault:"0"`

	// Variables específicas del Worker (opcionales en .env o systemd)
	StoragePrivatePath string   `env:"BPEJ_STORAGE_PATH"`
	WorkerConcurrency  int      `env:"WORKER_CONCURRENCY" envDefault:"2"`
	MagickThreadLimit  string   `env:"MAGICK_THREAD_LIMIT" envDefault:"1"`
	OcrDefaultLangs    []string `env:"OCR_DEFAULT_LANGS" envSeparator:"," envDefault:"spa,eng"`

	// Nombres de colas
	ColaProcesamiento string `env:"COLA_PROCESAMIENTO" envDefault:"bpej_cola_procesamiento"`
	ColaOcr           string `env:"COLA_OCR" envDefault:"bpej_cola_ocr"`
	ColaReindex       string `env:"COLA_REINDEX" envDefault:"bpej_ocr_reindex_queue"`
}

// MySQLDSN construye la cadena de conexión para go-sql-driver/mysql
func (c AppConfig) MySQLDSN() string {
	return fmt.Sprintf(
		"%s:%s@tcp(%s:%d)/%s?parseTime=true&charset=utf8mb4",
		c.DBUser, c.DBPass.Value(), c.DBHost, c.DBPort, c.DBName,
	)
}

// RedisAddr combina REDIS_HOST y REDIS_PORT
func (c AppConfig) RedisAddr() string {
	return fmt.Sprintf("%s:%s", c.RedisHost, c.RedisPort)
}

var cfg AppConfig

func loadConfig() error {
	// En desarrollo local busca ../.env (si corres desde una subcarpeta) o ./.env.
	// En producción con systemd, las variables ya vienen cargadas en el entorno.
	_ = godotenv.Load()

	if err := env.Parse(&cfg); err != nil {
		return fmt.Errorf("error leyendo variables de entorno: %w", err)
	}

	if cfg.StoragePrivatePath == "" {
		if runtime.GOOS == "windows" {
			cfg.StoragePrivatePath = filepath.Join("../storage", "app", "private")
		} else {
			cfg.StoragePrivatePath = "/var/www/html/bpej/storage/app/private"
		}
	}

	os.Setenv("MAGICK_THREAD_LIMIT", cfg.MagickThreadLimit)
	return nil
}