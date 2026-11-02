<?php

namespace Tests\Feature;

use App\Models\Recursos;
use App\Support\FiltroAvanzado;
use Meilisearch\Client;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('meilisearch')]
class BusquedaAvanzadaMeilisearchTest extends TestCase
{
    private Client $meili;
    private string $indice = 'testing_recursos_busqueda';

    protected function setUp(): void
    {
        parent::setUp();

        $this->meili = app(Client::class);
        $this->esperar($this->meili->createIndex($this->indice, ['primaryKey' => 'id']));

        $filtrables = config('scout.meilisearch.index-settings.' . Recursos::class . '.filterableAttributes');
        $this->esperar($this->meili->index($this->indice)->updateFilterableAttributes($filtrables));

        $this->esperar($this->meili->index($this->indice)->addDocuments([
            ['id' => 1, 'fondo_nombre' => 'Fondo A', 'metadata' => ['titulo' => 'Kuwait report n8']],
            ['id' => 2, 'fondo_nombre' => 'Fondo B', 'metadata' => ['titulo' => 'Otro documento']],
        ]));
    }

    protected function tearDown(): void
    {
        $this->meili->deleteIndex($this->indice);
        parent::tearDown();
    }

    private function esperar(array $tarea): void
    {
        $this->meili->waitForTask($tarea['taskUid']);
    }

    private function buscar(array $filas, array $fondos = []): int
    {
        $r = FiltroAvanzado::construir($filas, $fondos);

        return $this->meili->index($this->indice)
            ->search('', ['filter' => implode(' AND ', $r['filtros'])])
            ->getEstimatedTotalHits();
    }

    public function test_contains_sobre_metadata_funciona(): void
    {
        $this->assertSame(1, $this->buscar([
            ['campo' => 'titulo', 'termino' => 'kuwait', 'operador' => 'AND'],
        ]));
    }

    public function test_not_excluye_documentos(): void
    {
        $this->assertSame(1, $this->buscar([
            ['campo' => 'titulo', 'termino' => 'kuwait', 'operador' => 'NOT'],
        ]));
    }

    public function test_filtra_por_fondo(): void
    {
        $this->assertSame(1, $this->buscar([], ['Fondo B']));
    }
}