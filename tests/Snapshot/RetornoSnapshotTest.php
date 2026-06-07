<?php

namespace Eduardokum\LaravelBoleto\Tests\Snapshot;

use Throwable;
use PHPUnit\Framework\Attributes\DataProvider;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Factory;

/**
 * Golden files das estruturas parseadas de retorno (header/detalhes/trailer)
 * para cada fixture .ret existente. Erros de parsing também são travados.
 */
class RetornoSnapshotTest extends SnapshotTestCase
{
    public static function arquivoProvider()
    {
        $cases = [];
        foreach (['cnab240', 'cnab400'] as $layout) {
            $dir = __DIR__ . '/../Retorno/files/' . $layout;
            foreach (glob($dir . '/*.ret') as $file) {
                $name = $layout . '_' . pathinfo($file, PATHINFO_FILENAME);
                $cases[$name] = [$name, $file];
            }
        }

        return $cases;
    }

    #[DataProvider('arquivoProvider')]
    public function testParseRetorno($name, $file)
    {
        try {
            $retorno = Factory::make($file);
            $retorno->processar();

            $detalhes = [];
            foreach ($retorno->getDetalhes() as $detalhe) {
                $detalhes[] = $detalhe->toArray();
            }

            $data = [
                'banco'    => $retorno->getCodigoBanco(),
                'header'   => $retorno->getHeader()->toArray(),
                'detalhes' => $detalhes,
                'trailer'  => $retorno->getTrailer()->toArray(),
            ];
        } catch (Throwable $e) {
            $data = [
                'exception' => get_class($e),
                // Caminho absoluto varia por máquina/SO — normaliza para o
                // basename para que a fixture seja portável (Windows/Linux)
                'message'   => str_replace($file, basename($file), $e->getMessage()),
            ];
        }

        $this->assertMatchesJsonSnapshot('retorno/' . $name . '.json', $data);
    }
}
