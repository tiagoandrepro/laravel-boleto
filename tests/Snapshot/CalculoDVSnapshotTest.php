<?php

namespace Eduardokum\LaravelBoleto\Tests\Snapshot;

use Throwable;
use ReflectionClass;
use ReflectionMethod;
use Eduardokum\LaravelBoleto\CalculoDV;

/**
 * Trava o resultado de TODOS os métodos de CalculoDV para conjuntos fixos de
 * entradas. Qualquer mudança silenciosa de DV corrompe nosso número / código
 * de barras — este snapshot é o gate da normalização de tipos da Fase 3.
 *
 * Os valores são resolvidos pelo nome do parâmetro, então métodos novos são
 * cobertos automaticamente.
 */
class CalculoDVSnapshotTest extends SnapshotTestCase
{
    /**
     * Dois conjuntos de valores por nome de parâmetro (A e B) para exercitar
     * caminhos diferentes do módulo 10/11.
     */
    private static $valores = [
        'agencia'       => ['1111', '3978'],
        'conta'         => ['12345', '987654'],
        'nossoNumero'   => ['12345678901', '94373654102'],
        'carteira'      => ['09', '112'],
        'numero_boleto' => ['12345', '9876543'],
        'nossaCarteira' => ['112', '121'],
        'posto'         => ['02', '17'],
        'codigoCliente' => ['12345', '54321'],
        'ano'           => ['26', '30'],
        'byte'          => ['2', '3'],
        'convenio'      => ['1234567', '7654321'],
        'campo'         => ['12345678', '03978000'],
    ];

    public function testVetoresCalculoDV()
    {
        $reflection = new ReflectionClass(CalculoDV::class);
        $resultados = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ([0, 1] as $set) {
                $args = [];
                foreach ($method->getParameters() as $param) {
                    $name = $param->getName();
                    if (isset(self::$valores[$name])) {
                        $args[] = self::$valores[$name][$set];
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        $this->fail(sprintf(
                            'Parâmetro "%s" de CalculoDV::%s sem valor mapeado em $valores — adicione-o.',
                            $name,
                            $method->getName()
                        ));
                    }
                }

                try {
                    $result = $method->invokeArgs(null, $args);
                    $resultado = ['result' => $result, 'type' => gettype($result)];
                } catch (Throwable $e) {
                    $resultado = ['exception' => get_class($e), 'message' => $e->getMessage()];
                }

                $resultados[$method->getName()][] = [
                    'args' => $args,
                ] + $resultado;
            }
        }

        ksort($resultados);

        $this->assertMatchesJsonSnapshot('calculodv.json', $resultados);
    }

    /**
     * Casos de borda conhecidos com retornos não-inteiros, travados explicitamente.
     */
    public function testCasosDeBorda()
    {
        $this->assertMatchesJsonSnapshot('calculodv_bordas.json', [
            // len(nossoNumero) >= 17 => sem DV
            'bbNossoNumero_17digitos' => ['result' => CalculoDV::bbNossoNumero('12345678901234567'), 'type' => gettype(CalculoDV::bbNossoNumero('12345678901234567'))],
            // resto 10 => 'X' em alguns bancos
            'bnbAgencia_resto'        => ['result' => CalculoDV::bnbAgencia('0048'), 'type' => gettype(CalculoDV::bnbAgencia('0048'))],
            'sisprime_p'              => ['result' => CalculoDV::sisprimeNossoNumero('00000001'), 'type' => gettype(CalculoDV::sisprimeNossoNumero('00000001'))],
        ]);
    }
}
