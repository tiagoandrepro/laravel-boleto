<?php

namespace Eduardokum\LaravelBoleto\Tests\Api;

use ReflectionClass;
use Eduardokum\LaravelBoleto\Tests\TestCase;
use Eduardokum\LaravelBoleto\Api\AbstractAPI;
use Eduardokum\LaravelBoleto\Api\Exception\MissingDataException;

class AbstractAPITest extends TestCase
{
    private function makeApi(array $params = [])
    {
        $defaults = [
            'conta'            => '123456',
            'cnpj'             => '11222333000181',
            'certificado'      => 'cert',
            'certificadoChave' => 'chave',
            'certificadoSenha' => 'senha-secreta',
            'identificador'    => 'id',
            'senha'            => 'senha',
        ];

        return new class(array_merge($defaults, $params)) extends AbstractAPI
        {
            protected function headers()
            {
                return [];
            }

            public function createBoleto(\Eduardokum\LaravelBoleto\Contracts\Boleto\BoletoAPI $boleto)
            {
            }

            public function retrieveNossoNumero($nossoNumero)
            {
            }

            public function retrieveID($id)
            {
            }

            public function cancelNossoNumero($nossoNumero, $motivo)
            {
            }

            public function cancelID($id, $motivo)
            {
            }

            public function retrieveList($inputedParams = [])
            {
            }

            public function getPdfNossoNumero($nossoNumero)
            {
            }

            public function getPdfID($id)
            {
            }
        };
    }

    private function invokePrivate($object, $method, array $args)
    {
        $reflection = new ReflectionClass(AbstractAPI::class);
        $m = $reflection->getMethod($method);
        $m->setAccessible(true);

        return $m->invokeArgs($object, $args);
    }

    public function testCamposObrigatoriosFaltando()
    {
        $this->expectException(MissingDataException::class);
        $this->makeApi(['conta' => null, 'certificado' => null]);
    }

    public function testRedacaoDeCredenciaisNoLogDebug()
    {
        $api = $this->makeApi();

        $log = implode("\n", [
            '> POST /token HTTP/1.1',
            '> Authorization: Basic dXNlcjpwYXNz',
            '> Cookie: session=abc123',
            '< Set-Cookie: session=def456',
            'payload Bearer eyJhbGciOiJIUzI1NiJ9.payload.assinatura',
            '> Accept: application/json',
        ]);

        $redacted = $this->invokePrivate($api, 'redactSensitiveData', [$log]);

        $this->assertStringNotContainsString('dXNlcjpwYXNz', $redacted);
        $this->assertStringNotContainsString('abc123', $redacted);
        $this->assertStringNotContainsString('def456', $redacted);
        $this->assertStringNotContainsString('eyJhbGciOiJIUzI1NiJ9', $redacted);
        // Conteúdo inofensivo permanece
        $this->assertStringContainsString('Accept: application/json', $redacted);
        $this->assertStringContainsString('[REDACTED]', $redacted);
    }

    public function testParseResponseSeparaHeadersEBody()
    {
        $api = $this->makeApi();

        $raw = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\n\r\n{\"ok\":true}";
        $parsed = $this->invokePrivate($api, 'parseResponse', [$raw]);

        $this->assertSame('HTTP/1.1 200 OK', $parsed->headers['http_code']);
        $this->assertSame('application/json', $parsed->headers['Content-Type']);
        $this->assertTrue($parsed->body->ok);
    }

    public function testConvertHeaders()
    {
        $api = $this->makeApi();

        $converted = $this->invokePrivate($api, 'convertHeaders', [[
            'Accept'                  => 'application/json',
            'X-Custom: inline-format',
        ]]);

        $this->assertContains('Accept: application/json', $converted);
        $this->assertContains('X-Custom: inline-format', $converted);
    }

    public function testTempFileEscreveConteudoEAgendaLimpeza()
    {
        $api = $this->makeApi();

        $path = $this->invokePrivate($api, 'tempFile', ['conteudo-do-certificado']);

        $this->assertFileExists($path);
        $this->assertSame('conteudo-do-certificado', file_get_contents($path));

        // __destruct deve remover o arquivo temporário
        unset($api);
        gc_collect_cycles();
        $this->assertFileDoesNotExist($path);
    }
}
