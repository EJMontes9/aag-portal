<?php

namespace Tests\Unit;

use App\Blocks\BlockRegistry;
use App\Blocks\BlockType;
use PHPUnit\Framework\TestCase;

/**
 * El registro de bloques es el punto del que cuelga todo el constructor de
 * paginas: si un bloque declara una clave repetida o una vista que no existe,
 * el fallo no aparece al guardar sino al abrir la pagina publica, ya en
 * produccion y delante del ciudadano. Estas pruebas cierran ese hueco.
 */
class BlockRegistryTest extends TestCase
{
    public function test_el_registro_declara_los_quince_tipos_de_bloque(): void
    {
        $this->assertCount(15, BlockRegistry::types());
    }

    public function test_todos_los_tipos_extienden_el_contrato_de_bloque(): void
    {
        foreach (BlockRegistry::types() as $clase) {
            $this->assertTrue(
                is_subclass_of($clase, BlockType::class),
                "El tipo {$clase} no extiende BlockType."
            );
        }
    }

    public function test_las_claves_de_los_bloques_son_unicas(): void
    {
        $claves = array_map(fn ($clase) => $clase::key(), BlockRegistry::types());

        $this->assertSame(
            count($claves),
            count(array_unique($claves)),
            'Hay claves de bloque repetidas: ' . implode(', ', array_diff_assoc($claves, array_unique($claves)))
        );
    }

    public function test_ninguna_clave_ni_etiqueta_esta_vacia(): void
    {
        foreach (BlockRegistry::types() as $clase) {
            $this->assertNotSame('', trim($clase::key()), "El tipo {$clase} no declara clave.");
            $this->assertNotSame('', trim($clase::label()), "El tipo {$clase} no declara etiqueta.");
            $this->assertNotSame('', trim($clase::icon()), "El tipo {$clase} no declara icono.");
        }
    }

    /**
     * Cada bloque apunta a una plantilla Blade. Si el archivo no existe, la
     * pagina que use ese bloque revienta al renderizarse.
     */
    public function test_cada_bloque_apunta_a_una_plantilla_que_existe(): void
    {
        $raizVistas = dirname(__DIR__, 2) . '/resources/views/';

        foreach (BlockRegistry::types() as $clase) {
            $ruta = $raizVistas . str_replace('.', '/', $clase::view()) . '.blade.php';

            $this->assertFileExists(
                $ruta,
                "El bloque {$clase::key()} declara la vista {$clase::view()}, que no existe."
            );
        }
    }

    public function test_viewFor_resuelve_la_vista_de_una_clave_conocida(): void
    {
        $this->assertSame('blocks.hero', BlockRegistry::viewFor('hero'));
    }

    public function test_viewFor_devuelve_nulo_ante_una_clave_desconocida(): void
    {
        $this->assertNull(BlockRegistry::viewFor('bloque-que-no-existe'));
    }

    /**
     * defaults() alimenta el formulario al crear un bloque nuevo. Si devuelve
     * algo que no es un arreglo, el panel falla al abrir el constructor.
     */
    public function test_los_valores_por_defecto_son_siempre_un_arreglo(): void
    {
        foreach (BlockRegistry::types() as $clase) {
            $this->assertIsArray($clase::defaults(), "defaults() de {$clase} no devuelve un arreglo.");
        }
    }
}
