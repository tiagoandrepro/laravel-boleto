<?php

namespace Eduardokum\LaravelBoleto\Tests\Boleto;

use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Eduardokum\LaravelBoleto\Tests\Snapshot\Inputs;

/**
 * Benchmark de geração de PDF em lote (não-bloqueante: limites generosos
 * apenas para detectar regressões de ordem de grandeza).
 *
 * Referência medida em 2026-06 (PHP 8.4, FPDF 1.9):
 * 100 boletos => ~0.35s, ~4 MB de pico adicional, PDF ~0.4 MB.
 */
class PdfBatchBenchmarkTest extends TestCase
{
    const QUANTIDADE = 100;

    public function testGeracaoDeLoteDePdf()
    {
        $inputs = Inputs::boletos();
        $config = $inputs['banrisul'];

        $boletos = [];
        for ($i = 1; $i <= self::QUANTIDADE; $i++) {
            $params = $config['params'];
            $params['numero'] = $i;
            $params['numeroDocumento'] = $i;
            $boletos[] = new $config['class']($params);
        }

        $inicio = microtime(true);
        $memoriaInicial = memory_get_usage(true);

        $pdf = new Pdf();
        $pdf->addBoletos($boletos);
        $output = $pdf->gerarBoleto($pdf::OUTPUT_STRING);

        $duracao = microtime(true) - $inicio;
        $picoMemoria = memory_get_peak_usage(true) - $memoriaInicial;

        $metricas = sprintf(
            '[benchmark] %d boletos => %.2fs, pico de memoria adicional %.1f MB, PDF %.1f MB',
            self::QUANTIDADE,
            $duracao,
            $picoMemoria / 1048576,
            strlen($output) / 1048576
        );

        $this->assertNotEmpty($output);
        // Limites de ordem de grandeza (regressão grosseira, não SLA)
        $this->assertLessThan(60, $duracao, 'Geração de lote de PDF ficou uma ordem de grandeza mais lenta. ' . $metricas);
        $this->assertLessThan(512 * 1048576, $picoMemoria, 'Pico de memória de lote de PDF explodiu. ' . $metricas);
    }
}
