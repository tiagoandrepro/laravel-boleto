<?php

namespace Eduardokum\LaravelBoleto\Tests\Snapshot;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Golden files do conteúdo integral da remessa CNAB400 por banco.
 */
class RemessaCnab400SnapshotTest extends SnapshotTestCase
{
    /**
     * Máscaras extras por banco para campos data/hora gravados com date()/now() cru.
     * Sicredi 400: trailer posições 63-70 = Carbon::now()->format('Ymd').
     */
    private static $extraMasks = [
        'sicredi' => [['line' => -1, 'start' => 63, 'len' => 8]],
    ];

    public static function bancoProvider()
    {
        $boletos = Inputs::boletos();
        $cases = [];
        foreach (Inputs::remessasCnab400() as $slug => $config) {
            $cases[$slug] = [$slug, $config, $boletos[$config['boleto']]];
        }

        return $cases;
    }

    #[DataProvider('bancoProvider')]
    public function testConteudoRemessa($slug, array $remessaConfig, array $boletoConfig)
    {
        $boleto = new $boletoConfig['class']($boletoConfig['params']);
        $remessa = new $remessaConfig['class']($remessaConfig['params']);
        $remessa->addBoleto($boleto);

        $conteudo = self::normalizeCnab400(
            $remessa->gerar(),
            isset(self::$extraMasks[$slug]) ? self::$extraMasks[$slug] : []
        );

        $this->assertMatchesSnapshot('remessa400/' . $slug . '.txt', $conteudo);
    }
}
