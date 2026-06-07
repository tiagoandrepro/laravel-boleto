<?php

namespace Eduardokum\LaravelBoleto\Tests\Snapshot;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Golden files do conteúdo integral da remessa CNAB240 por banco.
 * Datas/horas de geração (posições padrão FEBRABAN) são mascaradas.
 */
class RemessaCnab240SnapshotTest extends SnapshotTestCase
{
    public static function bancoProvider()
    {
        $boletos = Inputs::boletos();
        $cases = [];
        foreach (Inputs::remessasCnab240() as $slug => $config) {
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

        $conteudo = self::normalizeCnab240($remessa->gerar());

        $this->assertMatchesSnapshot('remessa240/' . $slug . '.txt', $conteudo);
    }
}
