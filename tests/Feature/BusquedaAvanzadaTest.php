<?php

namespace Tests\Feature;

use App\Models\TipoAcervo;
use Database\Seeders\TipoAcervoSeeder; // ADAPTAR: el nombre real de tu seeder
use Illuminate\Foundation\Testing\RefreshDatabase;
use Meilisearch\Client;
use Mockery\MockInterface;
use Tests\TestCase;

class BusquedaAvanzadaTest extends TestCase
{
    use RefreshDatabase;

    private TipoAcervo $acervo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TipoAcervoSeeder::class);
        $this->acervo = TipoAcervo::firstOrFail();

        // Por defecto ninguna prueba consulta el Meilisearch real
        $this->mock(Client::class)
            ->shouldReceive('multiSearch')
            ->andReturn(['results' => [['indexUid' => 'recursos', 'hits' => []]]])
            ->byDefault();
    }

    private function url(array $query): string
    {
        return route('busqueda.resultado', $query); // ADAPTAR: la ruta que ejecuta busquedaAvanzada
    }

public function test_exige_el_acervo(): void
{
    $this->getJson($this->url(['q' => []]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('acervo');
}

    public function test_envia_a_meilisearch_el_filtro_y_el_texto_esperados(): void
    {
        $acervoId = $this->acervo->id;

        $this->mock(Client::class, function (MockInterface $mock) use ($acervoId) {
            $mock->shouldReceive('multiSearch')->once()->withArgs(function (array $queries) use ($acervoId) {
                $q = $queries[0]->toArray();

                return count($queries) === 1
                    && $q['indexUid'] === 'recursos'
                    && $q['q'] === 'kuwait'
                    && $q['filter'] === [
                        '(metadata.titulo CONTAINS "kuwait") AND acervo_id = ' . $acervoId,
                    ];
            })->andReturn(['results' => [['indexUid' => 'recursos', 'hits' => []]]]);
        });

        $this->get($this->url([
            'acervo' => $acervoId,
            'q' => [['campo' => 'titulo', 'termino' => 'kuwait', 'operador' => 'AND']],
        ]))->assertOk();
    }

    public function test_si_meilisearch_falla_responde_sin_resultados_y_sin_error_500(): void
    {
        $this->mock(Client::class, function (MockInterface $mock) {
            $mock->shouldReceive('multiSearch')->andThrow(new \Exception('Meilisearch caído'));
        });

        $this->get($this->url([
            'acervo' => $this->acervo->id,
            'q' => [['campo' => 'all', 'termino' => 'kuwait', 'operador' => 'AND']],
        ]))
            ->assertOk()
            ->assertViewHas('resultados', fn ($p) => $p->total() === 0);
    }
}