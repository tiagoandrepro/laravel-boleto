<?php

namespace Eduardokum\LaravelBoleto\Tests\Snapshot;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Tests\TestCase;

/**
 * Base para testes de snapshot (golden files).
 *
 * Fixtures são geradas com UPDATE_SNAPSHOTS=1 (ou na primeira execução, quando
 * o arquivo ainda não existe) e commitadas. Execuções subsequentes comparam
 * byte a byte — qualquer divergência em remessa/código de barras/DV indica
 * regressão de comportamento e exige revisão manual.
 */
abstract class SnapshotTestCase extends TestCase
{
    protected static function fixtureDir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'fixtures';
    }

    protected static function shouldUpdate()
    {
        return (bool) getenv('UPDATE_SNAPSHOTS');
    }

    /**
     * Compara $content com a fixture $relativePath (gerando-a se necessário).
     *
     * @param string $relativePath ex.: "remessa400/bb.txt"
     * @param string $content
     */
    protected function assertMatchesSnapshot($relativePath, $content)
    {
        $path = self::fixtureDir() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (self::shouldUpdate() || ! file_exists($path)) {
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, $content);
            $this->addToAssertionCount(1);

            return;
        }

        $expected = file_get_contents($path);
        $this->assertSame($expected, $content, sprintf(
            'Snapshot "%s" divergiu. Se a mudança de comportamento é INTENCIONAL, rode com UPDATE_SNAPSHOTS=1 e revise o diff da fixture.',
            $relativePath
        ));
    }

    /**
     * Variante para estruturas (arrays) — serializa em JSON estável.
     */
    protected function assertMatchesJsonSnapshot($relativePath, array $data)
    {
        $json = json_encode(
            self::normalizeForJson($data),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
        );
        $this->assertMatchesSnapshot($relativePath, $json . "\n");
    }

    /**
     * Converte recursivamente valores não-serializáveis de forma estável
     * (Carbon/DateTime => 'Y-m-d', objetos com toArray => array).
     */
    protected static function normalizeForJson($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_object($value) && method_exists($value, 'toArray')) {
            return self::normalizeForJson($value->toArray());
        }
        if (is_object($value)) {
            return self::normalizeForJson(get_object_vars($value));
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = self::normalizeForJson($v);
            }

            return $out;
        }

        return $value;
    }

    /**
     * Mascara os campos de data/hora de geração gravados com date()/Carbon::now()
     * cru (fora do controle de dataRemessa), para que o conteúdo seja determinístico.
     *
     * CNAB240 (posições padrão FEBRABAN, 1-indexadas):
     *  - linha 1 (header arquivo): 144-151 data de geração, 152-157 hora de geração
     *  - linha 2 (header lote):    192-199 data de gravação, 200-207 data de crédito
     *
     * @param string $content
     * @param array $extraMasks lista de ['line' => N (1-based, -1 = última), 'start' => pos 1-based, 'len' => N]
     * @return string
     */
    protected static function normalizeCnab240($content, array $extraMasks = [])
    {
        $masks = array_merge([
            ['line' => 1, 'start' => 144, 'len' => 14],
            ['line' => 2, 'start' => 192, 'len' => 16],
        ], $extraMasks);

        return self::applyMasks($content, $masks);
    }

    protected static function normalizeCnab400($content, array $extraMasks = [])
    {
        return self::applyMasks($content, $extraMasks);
    }

    private static function applyMasks($content, array $masks)
    {
        // Preserva o terminador de linha original (CRLF vs LF)
        $eol = strpos($content, "\r\n") !== false ? "\r\n" : "\n";
        $lines = explode($eol, $content);

        foreach ($masks as $mask) {
            $idx = $mask['line'] === -1 ? self::lastNonEmptyLine($lines) : $mask['line'] - 1;
            if (! isset($lines[$idx])) {
                continue;
            }
            $lines[$idx] = substr_replace(
                $lines[$idx],
                str_repeat('*', $mask['len']),
                $mask['start'] - 1,
                $mask['len']
            );
        }

        return implode($eol, $lines);
    }

    private static function lastNonEmptyLine(array $lines)
    {
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (trim($lines[$i]) !== '') {
                return $i;
            }
        }

        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Entradas determinísticas compartilhadas
    |--------------------------------------------------------------------------
    */

    public static function dataVencimento()
    {
        return Carbon::create(2030, 12, 15, 0, 0, 0);
    }

    public static function dataDocumento()
    {
        return Carbon::create(2030, 6, 1, 0, 0, 0);
    }

    public static function dataRemessa()
    {
        return Carbon::create(2030, 6, 1, 0, 0, 0);
    }

    public static function valorFixo()
    {
        return 1234.56;
    }

    public static function multaFixa()
    {
        return 2.0;
    }

    public static function jurosFixo()
    {
        return 1.0;
    }
}
