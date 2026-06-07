<?php

namespace Eduardokum\LaravelBoleto\Tests;

use Eduardokum\LaravelBoleto\Webhook\Boleto;
use Eduardokum\LaravelBoleto\Webhook\Banco\Inter;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

class WebhookTest extends TestCase
{
    public function testConstrutorExigeArray()
    {
        $this->expectException(ValidationException::class);
        new Inter('não é array');
    }

    public function testPostEHeadersNaoSeSobrescrevem()
    {
        $post = [['nossoNumero' => '123']];
        $headers = ['x-conta-corrente' => '456789'];

        $webhook = new Inter($post, $headers);

        $this->assertSame($post, $webhook->getPost());
        $this->assertSame($headers, $webhook->getHeaders());
    }

    public function testProcessarWebhookInterLiquidado()
    {
        $post = [
            [
                'nossoNumero'           => '00000000001',
                'seuNumero'             => '12345',
                'situacao'              => 'PAGO',
                'dataHoraSituacao'      => '2030-06-01 10:00:00',
                'valorNominal'          => 1234.56,
                'valorTotalRecebimento' => 1234.56,
                'codigoBarras'          => '07791234500001234561112300000000000122222250',
                'linhaDigitavel'        => '07791112330000000000301222222509123450000123456',
                'origemRecebimento'     => 'PIX',
                'txid'                  => 'TX123',
            ],
        ];

        $webhook = new Inter($post, ['x-conta-corrente' => '456789']);
        $boletos = $webhook->processar();

        $this->assertCount(1, $boletos);
        $this->assertSame('456789', $webhook->getConta());

        $boleto = $boletos[0];
        $this->assertSame('00000000001', $boleto->getNossoNumero());
        $this->assertSame('12345', $boleto->getNumeroDocumento());
        $this->assertSame(Boleto::OCORRENCIA_LIQUIDADA, $boleto->getOcorrenciaTipo());
        $this->assertSame(Boleto::OCORRENCIA_ORIGEM_PIX, $boleto->getOcorrenciaOrigem());
        $this->assertSame('TX123', $boleto->getTxid());
        $this->assertSame('2030-06-01', $boleto->getDataOcorrencia()->format('Y-m-d'));
    }

    public function testProcessarWebhookInterCancelado()
    {
        $post = [
            [
                'nossoNumero' => '00000000002',
                'seuNumero'   => '54321',
                'situacao'    => 'CANCELADO',
                'horario'     => '2030-06-02 11:00:00',
            ],
        ];

        $webhook = new Inter($post);
        $boletos = $webhook->processar();

        $this->assertCount(1, $boletos);
        $this->assertSame(Boleto::OCORRENCIA_BAIXADA, $boletos[0]->getOcorrenciaTipo());
        $this->assertSame(Boleto::OCORRENCIA_ORIGEM_BOLETO, $boletos[0]->getOcorrenciaOrigem());
    }
}
