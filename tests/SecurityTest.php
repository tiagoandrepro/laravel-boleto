<?php

namespace Eduardokum\LaravelBoleto\Tests;

use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Boleto\Render\Html;
use Eduardokum\LaravelBoleto\Tests\Snapshot\Inputs;
use Eduardokum\LaravelBoleto\Exception\ValidationException;

class SecurityTest extends TestCase
{
    protected function tearDown(): void
    {
        // Garante que a validação opt-in não vaza para outros testes
        Pessoa::validarDocumentos(false);
        parent::tearDown();
    }

    public function testDocumentoFakeAceitoPorPadrao()
    {
        $pessoa = new Pessoa(['nome' => 'ACME', 'documento' => '99.999.999/9999-99']);
        $this->assertSame('99.999.999/9999-99', $pessoa->getDocumento());
    }

    public function testValidacaoDocumentoOptInRejeitaCpfInvalido()
    {
        Pessoa::validarDocumentos();

        $this->expectException(ValidationException::class);
        new Pessoa(['nome' => 'ACME', 'documento' => '999.999.999-99']);
    }

    public function testValidacaoDocumentoOptInRejeitaCnpjInvalido()
    {
        Pessoa::validarDocumentos();

        $this->expectException(ValidationException::class);
        new Pessoa(['nome' => 'ACME', 'documento' => '99.999.999/9999-99']);
    }

    public function testValidacaoDocumentoOptInAceitaDocumentosValidos()
    {
        Pessoa::validarDocumentos();

        // CPF e CNPJ com DVs corretos (geradores públicos de teste)
        $cpf = new Pessoa(['nome' => 'ACME', 'documento' => '529.982.247-25']);
        $cnpj = new Pessoa(['nome' => 'ACME', 'documento' => '11.222.333/0001-81']);

        $this->assertSame('529.982.247-25', $cpf->getDocumento());
        $this->assertSame('11.222.333/0001-81', $cnpj->getDocumento());
    }

    public function testHtmlEscapaNomeDoPagadorContraXss()
    {
        $inputs = Inputs::boletos();
        $config = $inputs['banrisul'];

        $config['params']['pagador'] = new Pessoa([
            'nome'      => '<script>alert("xss")</script>',
            'endereco'  => 'Rua um, 123',
            'bairro'    => 'Bairro',
            'cep'       => '99999-999',
            'uf'        => 'UF',
            'cidade'    => 'CIDADE',
            'documento' => '999.999.999-99',
        ]);

        $boleto = new $config['class']($config['params']);

        $html = new Html();
        $html->addBoleto($boleto);
        $output = $html->gerarBoleto();

        $this->assertStringNotContainsString('<script>alert("xss")</script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }
}
