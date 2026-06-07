<?php

namespace Eduardokum\LaravelBoleto\Tests\Snapshot;

use Eduardokum\LaravelBoleto\Pessoa;
use Eduardokum\LaravelBoleto\Boleto\Banco as Boleto;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco as Remessa400;
use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab240\Banco as Remessa240;

/**
 * Registro estático de entradas determinísticas por banco, colhidas dos testes e
 * exemplos existentes, para alimentar os testes de snapshot (golden files).
 *
 * Todos os valores aleatórios/relativos (datas, valor, multa, juros) foram
 * substituídos por valores fixos de {@see SnapshotTestCase}, mantendo verbatim
 * os demais campos específicos de cada banco (carteira, agência, conta, etc.).
 */
class Inputs
{
    /**
     * @var Pessoa|null
     */
    private static $beneficiario;

    /**
     * @var Pessoa|null
     */
    private static $pagador;

    public static function beneficiario()
    {
        if (self::$beneficiario === null) {
            self::$beneficiario = new Pessoa([
                'nome'      => 'ACME',
                'endereco'  => 'Rua um, 123',
                'cep'       => '99999-999',
                'uf'        => 'UF',
                'cidade'    => 'CIDADE',
                'documento' => '99.999.999/9999-99',
            ]);
        }

        return self::$beneficiario;
    }

    public static function pagador()
    {
        if (self::$pagador === null) {
            self::$pagador = new Pessoa([
                'nome'      => 'Cliente',
                'endereco'  => 'Rua um, 123',
                'bairro'    => 'Bairro',
                'cep'       => '99999-999',
                'uf'        => 'UF',
                'cidade'    => 'CIDADE',
                'documento' => '999.999.999-99',
            ]);
        }

        return self::$pagador;
    }

    /**
     * Campos determinísticos comuns a todos os boletos.
     *
     * @return array
     */
    private static function comum()
    {
        return [
            'dataVencimento'         => SnapshotTestCase::dataVencimento(),
            'dataDocumento'          => SnapshotTestCase::dataDocumento(),
            'dataProcessamento'      => SnapshotTestCase::dataDocumento(),
            'valor'                  => SnapshotTestCase::valorFixo(),
            'multa'                  => SnapshotTestCase::multaFixa(),
            'juros'                  => SnapshotTestCase::jurosFixo(),
            'aceite'                 => 'S',
            'especieDoc'             => 'DM',
            'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
            'instrucoes'             => ['instrucao 1', 'instrucao 2', 'instrucao 3'],
            'pagador'                => self::pagador(),
            'beneficiario'           => self::beneficiario(),
        ];
    }

    /**
     * @return array<string, array{class: class-string, params: array}>
     */
    public static function boletos()
    {
        $c = self::comum();

        return [
            'abc' => [
                'class'  => Boleto\Abc::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => 6,
                    'operacao'        => 1234567,
                    'agencia'         => '0001',
                    'conta'           => '7654321',
                ],
            ],
            'ailos' => [
                'class'  => Boleto\Ailos::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => '1',
                    'convenio'        => '000000',
                    'agencia'         => 1111,
                    'agenciaDv'       => 1,
                    'conta'           => 11111,
                    'contaDv'         => 1,
                ],
            ],
            'bancoob' => [
                'class'  => Boleto\Bancoob::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => '1',
                    'byte'            => 2,
                    'agencia'         => 1111,
                    'convenio'        => 123123,
                    'conta'           => 11111,
                ],
            ],
            'banrisul' => [
                'class'  => Boleto\Banrisul::class,
                'params' => $c + [
                    'numero'              => 1,
                    'diasBaixaAutomatica' => 20,
                    'numeroDocumento'     => 1,
                    'carteira'            => 1,
                    'agencia'             => 1111,
                    'conta'               => 22222,
                ],
            ],
            'bb' => [
                'class'  => Boleto\Bb::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => 11,
                    'convenio'        => 1234567,
                ],
            ],
            'bnb' => [
                'class'  => Boleto\Bnb::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => '21',
                    'agencia'         => 1111,
                    'conta'           => 11111,
                    'contaDv'         => 1,
                ],
            ],
            'bradesco' => [
                'class'  => Boleto\Bradesco::class,
                'params' => $c + [
                    'numero'              => 1,
                    'diasBaixaAutomatica' => 2,
                    'numeroDocumento'     => 1,
                    'carteira'            => '09',
                    'agencia'             => 1111,
                    'conta'               => 9999999,
                ],
            ],
            'btg' => [
                'class'  => Boleto\Btg::class,
                'params' => $c + [
                    'numero'          => 6380,
                    'numeroDocumento' => 6380,
                    'desconto'        => 10,
                    'carteira'        => '1',
                    'agencia'         => '0050',
                    'conta'           => '000000000',
                ],
            ],
            'bv' => [
                'class'  => Boleto\Bv::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => 500,
                    'convenio'        => 1234567890,
                    'conta'           => 12345678,
                ],
            ],
            'c6' => [
                'class'  => Boleto\C6::class,
                'params' => $c + [
                    'numero'          => 66,
                    'numeroDocumento' => 1,
                    'carteira'        => '10',
                    'agencia'         => 1,
                    'conta'           => 123,
                    'codigoCliente'   => 1893,
                ],
            ],
            'caixa' => [
                'class'  => Boleto\Caixa::class,
                'params' => $c + [
                    'numero'              => 1,
                    'numeroDocumento'     => 1,
                    'diasBaixaAutomatica' => 2,
                    'agencia'             => 1111,
                    'conta'               => 123456,
                    'carteira'            => 'RG',
                    'codigoCliente'       => 999999,
                ],
            ],
            'cresol' => [
                'class'  => Boleto\Cresol::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => '09',
                    'agencia'         => 1111,
                    'conta'           => 22222,
                ],
            ],
            'daycoval' => [
                'class'  => Boleto\Daycoval::class,
                'params' => $c + [
                    'numero'          => '0004309540',
                    'numeroDocumento' => 1,
                    'carteira'        => 6,
                    'operacao'        => 1234567,
                    'agencia'         => '0001',
                    'conta'           => '7654321',
                ],
            ],
            'delbank' => [
                'class'  => Boleto\Delbank::class,
                'params' => $c + [
                    'numero'              => 0,
                    'numero_controle'     => 'SEUNUMERO',
                    'diasBaixaAutomatica' => 5,
                    'numeroDocumento'     => 1,
                    'carteira'            => '112',
                    'agencia'             => 19,
                    'conta'               => 10138,
                ],
            ],
            'fibra' => [
                'class'  => Boleto\Fibra::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'range'           => 0,
                    'carteira'        => 'D',
                    'agencia'         => '0001',
                    'codigoCliente'   => '12345',
                    'conta'           => '1234567',
                ],
            ],
            'grafeno' => [
                'class'  => Boleto\Grafeno::class,
                'params' => $c + [
                    'numero'              => 1,
                    'diasBaixaAutomatica' => 2,
                    'numeroDocumento'     => 1,
                    'carteira'            => '1',
                    'agencia'             => '0001',
                    'conta'               => '12345678',
                    'range'               => '25000000000',
                ],
            ],
            'hsbc' => [
                'class'  => Boleto\Hsbc::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => 'CSB',
                    'range'           => 12345,
                    'agencia'         => 1111,
                    'conta'           => 999999,
                    'contaDv'         => 9,
                ],
            ],
            'inter' => [
                'class'  => Boleto\Inter::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'nossoNumero'     => '00000000001',
                    'carteira'        => 112,
                    'agencia'         => '0001',
                    'conta'           => '123456789',
                    'operacao'        => '1234567',
                ],
            ],
            'itau' => [
                'class'  => Boleto\Itau::class,
                'params' => $c + [
                    'numero'              => 1,
                    'numeroDocumento'     => 1,
                    'diasBaixaAutomatica' => 2,
                    'carteira'            => 112,
                    'agencia'             => 1111,
                    'conta'               => 99999,
                ],
            ],
            'ourinvest' => [
                'class'  => Boleto\Ourinvest::class,
                'params' => $c + [
                    'numero'          => 2,
                    'numeroDocumento' => 2,
                    'carteira'        => '19',
                    'agencia'         => 1,
                    'conta'           => 9999999,
                    'chaveNfe'        => '12345678901234567890123456789012345678901234',
                ],
            ],
            'pine' => [
                'class'  => Boleto\Pine::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'range'           => 0,
                    'carteira'        => '1',
                    'agencia'         => '0001',
                    'codigoCliente'   => '12345',
                    'conta'           => '1234',
                ],
            ],
            'rendimento' => [
                'class'  => Boleto\Rendimento::class,
                'params' => $c + [
                    'numero'          => 2,
                    'numeroDocumento' => 2,
                    'carteira'        => '6',
                    'agencia'         => '0001',
                    'codigoCliente'   => '5447390',
                    'conta'           => '1234',
                ],
            ],
            'santander' => [
                'class'  => Boleto\Santander::class,
                'params' => $c + [
                    'numero'              => 1,
                    'numeroDocumento'     => 1,
                    'diasBaixaAutomatica' => 15,
                    'carteira'            => 101,
                    'agencia'             => 1111,
                    'conta'               => 99999999,
                    'codigoCliente'       => 9999999,
                ],
            ],
            'sicredi' => [
                'class'  => Boleto\Sicredi::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => '1',
                    'byte'            => 2,
                    'agencia'         => 1111,
                    'posto'           => 11,
                    'conta'           => 11111,
                ],
            ],
            'sisprime' => [
                'class'  => Boleto\Sisprime::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => '9',
                    'agencia'         => 1111,
                    'conta'           => 22222,
                ],
            ],
            'unicred' => [
                'class'  => Boleto\Unicred::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => '21',
                    'convenio'        => '000000',
                    'agencia'         => 1111,
                    'agenciaDv'       => 1,
                    'conta'           => 11111,
                    'contaDv'         => 1,
                    'tipoJuro'        => 'VALOR_DIARIO',
                    'tipoMulta'       => 'VALOR_FIXO',
                ],
            ],
            'vortx' => [
                'class'  => Boleto\Vortx::class,
                'params' => $c + [
                    'numero'          => 1,
                    'numeroDocumento' => 1,
                    'carteira'        => 1,
                    'agencia'         => 1111,
                    'conta'           => 22222,
                    'contaDv'         => 9,
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{class: class-string, params: array, boleto: string}>
     */
    public static function remessasCnab400()
    {
        return [
            'abc' => [
                'class'  => Remessa400\Abc::class,
                'boleto' => 'abc',
                'params' => [
                    'agencia'       => '0001',
                    'conta'         => '7654321',
                    'carteira'      => 6,
                    'codigoCliente' => '00011234567',
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'bancoob' => [
                'class'  => Remessa400\Bancoob::class,
                'boleto' => 'bancoob',
                'params' => [
                    'agencia'      => 2606,
                    'carteira'     => '1',
                    'conta'        => 12510,
                    'convenio'     => 123123,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'banrisul' => [
                'class'  => Remessa400\Banrisul::class,
                'boleto' => 'banrisul',
                'params' => [
                    'agencia'       => 1111,
                    'conta'         => 22222,
                    'carteira'      => 1,
                    'codigoCliente' => 11112222222,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'bb' => [
                'class'  => Remessa400\Bb::class,
                'boleto' => 'bb',
                'params' => [
                    'agencia'      => 1111,
                    'carteira'     => 11,
                    'conta'        => 999999999,
                    'convenio'     => 1234567,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'bnb' => [
                'class'  => Remessa400\Bnb::class,
                'boleto' => 'bnb',
                'params' => [
                    'agencia'      => 1111,
                    'conta'        => 11111,
                    'contaDv'      => 1,
                    'carteira'     => '21',
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'bradesco' => [
                'class'  => Remessa400\Bradesco::class,
                'boleto' => 'bradesco',
                'params' => [
                    'idRemessa'     => 1,
                    'agencia'       => 1111,
                    'carteira'      => '09',
                    'conta'         => 99999999,
                    'contaDv'       => 9,
                    'codigoCliente' => 12345678901234567890,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'bv' => [
                'class'  => Remessa400\Bv::class,
                'boleto' => 'bv',
                'params' => [
                    'agencia'      => '0001',
                    'carteira'     => 500,
                    'conta'        => 12345678,
                    'convenio'     => 1234567890,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'c6' => [
                'class'  => Remessa400\C6::class,
                'boleto' => 'c6',
                'params' => [
                    'idRemessa'     => 1,
                    'agencia'       => 1111,
                    'carteira'      => '10',
                    'conta'         => 1234,
                    'contaDv'       => 5,
                    'codigoCliente' => '1893',
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'caixa' => [
                'class'  => Remessa400\Caixa::class,
                'boleto' => 'caixa',
                'params' => [
                    'agencia'       => 1111,
                    'conta'         => 123456,
                    'idremessa'     => 1,
                    'carteira'      => 'RG',
                    'codigoCliente' => 999999,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'cresol' => [
                'class'  => Remessa400\Cresol::class,
                'boleto' => 'cresol',
                'params' => [
                    'idremessa'     => 1,
                    'agencia'       => 1111,
                    'carteira'      => '09',
                    'conta'         => 22222,
                    'codigoCliente' => 999999,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'daycoval' => [
                'class'  => Remessa400\Daycoval::class,
                'boleto' => 'daycoval',
                'params' => [
                    'agencia'       => 1111,
                    'carteira'      => '6',
                    'conta'         => '7654321',
                    'contaDv'       => 9,
                    'codigoCliente' => '190600851565400',
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'delbank' => [
                'class'  => Remessa400\Delbank::class,
                'boleto' => 'delbank',
                'params' => [
                    'idRemessa'     => 1,
                    'agencia'       => 19,
                    'carteira'      => '112',
                    'conta'         => 10138,
                    'codigoCliente' => '10138DELCREDFUNDOLTD',
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'fibra' => [
                'class'  => Remessa400\Fibra::class,
                'boleto' => 'fibra',
                'params' => [
                    'agencia'       => '0001',
                    'conta'         => '1234567',
                    'contaDv'       => 9,
                    'carteira'      => 112,
                    'beneficiario'  => self::beneficiario(),
                    'codigoCliente' => '12345',
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'grafeno' => [
                'class'  => Remessa400\Grafeno::class,
                'boleto' => 'grafeno',
                'params' => [
                    'idRemessa'    => 1,
                    'agencia'      => '0001',
                    'carteira'     => '1',
                    'conta'        => '12345678',
                    'contaDv'      => '9',
                    'convenio'     => '12345678',
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'hsbc' => [
                'class'  => Remessa400\Hsbc::class,
                'boleto' => 'hsbc',
                'params' => [
                    'agencia'      => 1111,
                    'carteira'     => 'CSB',
                    'conta'        => 999999,
                    'contaDv'      => 9,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'inter' => [
                'class'  => Remessa400\Inter::class,
                'boleto' => 'inter',
                'params' => [
                    'idRemessa'    => 1,
                    'agencia'      => '0001',
                    'conta'        => '123456789',
                    'carteira'     => 112,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'itau' => [
                'class'  => Remessa400\Itau::class,
                'boleto' => 'itau',
                'params' => [
                    'agencia'      => 1111,
                    'conta'        => 99999,
                    'contaDv'      => 9,
                    'carteira'     => 112,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'ourinvest' => [
                'class'  => Remessa400\Ourinvest::class,
                'boleto' => 'ourinvest',
                'params' => [
                    'idRemessa'    => 1,
                    'agencia'      => 1111,
                    'carteira'     => '19',
                    'conta'        => 1234567,
                    'contaDv'      => 9,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'pine' => [
                'class'  => Remessa400\Pine::class,
                'boleto' => 'pine',
                'params' => [
                    'agencia'       => '0001',
                    'conta'         => '1234',
                    'contaDv'       => 9,
                    'carteira'      => 112,
                    'beneficiario'  => self::beneficiario(),
                    'codigoCliente' => '1234',
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'rendimento' => [
                'class'  => Remessa400\Rendimento::class,
                'boleto' => 'rendimento',
                'params' => [
                    'agencia'       => '0001',
                    'conta'         => '1234',
                    'contaDv'       => 9,
                    'carteira'      => 121,
                    'codigoCliente' => '5447390',
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'santander' => [
                'class'  => Remessa400\Santander::class,
                'boleto' => 'santander',
                'params' => [
                    'agencia'       => 1111,
                    'carteira'      => 101,
                    'conta'         => 99999999,
                    'codigoCliente' => 12345678,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'sicredi' => [
                'class'  => Remessa400\Sicredi::class,
                'boleto' => 'sicredi',
                'params' => [
                    'agencia'      => 2606,
                    'carteira'     => '1',
                    'conta'        => 12510,
                    'idremessa'    => 1,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'sisprime' => [
                'class'  => Remessa400\Sisprime::class,
                'boleto' => 'sisprime',
                'params' => [
                    'idremessa'    => 1,
                    'agencia'      => 1111,
                    'carteira'     => '9',
                    'conta'        => 22222,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'unicred' => [
                'class'  => Remessa400\Unicred::class,
                'boleto' => 'unicred',
                'params' => [
                    'idremessa'    => 1,
                    'agencia'      => 1111,
                    'conta'        => 11111,
                    'contaDv'      => 1,
                    'carteira'     => '21',
                    'convenio'     => '000000',
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'vortx' => [
                'class'  => Remessa400\Vortx::class,
                'boleto' => 'vortx',
                'params' => [
                    'idremessa'    => 1,
                    'agencia'      => 1111,
                    'carteira'     => 1,
                    'conta'        => 22222,
                    'contaDv'      => 9,
                    'convenio'     => '1234567',
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{class: class-string, params: array, boleto: string}>
     */
    public static function remessasCnab240()
    {
        return [
            'ailos' => [
                'class'  => Remessa240\Ailos::class,
                'boleto' => 'ailos',
                'params' => [
                    'agencia'      => 1111,
                    'agenciaDV'    => 1,
                    'conta'        => 11111,
                    'carteira'     => '1',
                    'convenio'     => '123456',
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'bancoob' => [
                'class'  => Remessa240\Bancoob::class,
                'boleto' => 'bancoob',
                'params' => [
                    'idremessa'    => 1,
                    'agencia'      => 1111,
                    'carteira'     => 1,
                    'conta'        => 11111,
                    'convenio'     => 123123,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'banrisul' => [
                'class'  => Remessa240\Banrisul::class,
                'boleto' => 'banrisul',
                'params' => [
                    'agencia'       => 1111,
                    'conta'         => 22222,
                    'carteira'      => 1,
                    'codigoCliente' => 11112222222,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'bb' => [
                'class'  => Remessa240\Bb::class,
                'boleto' => 'bb',
                'params' => [
                    'agencia'         => 1111,
                    'carteira'        => 11,
                    'conta'           => 999999999,
                    'convenio'        => 1234567,
                    'convenioLider'   => 1234567,
                    'variacaoCarteira' => 19,
                    'beneficiario'    => self::beneficiario(),
                    'dataRemessa'     => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'bradesco' => [
                'class'  => Remessa240\Bradesco::class,
                'boleto' => 'bradesco',
                'params' => [
                    'idremessa'     => 1,
                    'agencia'       => 1111,
                    'carteira'      => '09',
                    'conta'         => 99999999,
                    'contaDv'       => 9,
                    'codigoCliente' => 12345678901234567890,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'btg' => [
                'class'  => Remessa240\Btg::class,
                'boleto' => 'btg',
                'params' => [
                    'agencia'       => '0050',
                    'carteira'      => '1',
                    'conta'         => '000000000',
                    'codigoCliente' => '001100983001401000',
                    'idremessa'     => 1,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'caixa' => [
                'class'  => Remessa240\Caixa::class,
                'boleto' => 'caixa',
                'params' => [
                    'agencia'       => 1111,
                    'conta'         => 123456,
                    'idremessa'     => 1,
                    'carteira'      => 'RG',
                    'codigoCliente' => 999999,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'itau' => [
                'class'  => Remessa240\Itau::class,
                'boleto' => 'itau',
                'params' => [
                    'agencia'      => 1111,
                    'carteira'     => 112,
                    'conta'        => 99999,
                    'contaDv'      => 9,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'santander' => [
                'class'  => Remessa240\Santander::class,
                'boleto' => 'santander',
                'params' => [
                    'idremessa'     => 1,
                    'agencia'       => 1111,
                    'carteira'      => 101,
                    'conta'         => 99999999,
                    'codigoCliente' => 12345678,
                    'beneficiario'  => self::beneficiario(),
                    'dataRemessa'   => SnapshotTestCase::dataRemessa(),
                ],
            ],
            'sicredi' => [
                'class'  => Remessa240\Sicredi::class,
                'boleto' => 'sicredi',
                'params' => [
                    'agencia'      => 2606,
                    'carteira'     => 'A',
                    'conta'        => 12510,
                    'idremessa'    => 1,
                    'beneficiario' => self::beneficiario(),
                    'dataRemessa'  => SnapshotTestCase::dataRemessa(),
                ],
            ],
        ];
    }
}
