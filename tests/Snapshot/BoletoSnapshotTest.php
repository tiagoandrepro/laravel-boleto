<?php

namespace Eduardokum\LaravelBoleto\Tests\Snapshot;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Golden files de código de barras / linha digitável / nosso número por banco.
 * Maior ROI contra regressões de arredondamento de valores e cálculo de DV.
 */
class BoletoSnapshotTest extends SnapshotTestCase
{
    public static function bancoProvider()
    {
        $cases = [];
        foreach (Inputs::boletos() as $slug => $config) {
            $cases[$slug] = [$slug, $config['class'], $config['params']];
        }

        return $cases;
    }

    #[DataProvider('bancoProvider')]
    public function testCamposCalculadosDoBoleto($slug, $class, array $params)
    {
        $boleto = new $class($params);

        $this->assertMatchesJsonSnapshot('boleto/' . $slug . '.json', [
            'codigo_barras'               => $boleto->getCodigoBarras(),
            'linha_digitavel'             => $boleto->getLinhaDigitavel(),
            'nosso_numero'                => $boleto->getNossoNumero(),
            'nosso_numero_boleto'         => $boleto->getNossoNumeroBoleto(),
            'agencia_codigo_beneficiario' => $boleto->getAgenciaCodigoBeneficiario(),
        ]);
    }
}
