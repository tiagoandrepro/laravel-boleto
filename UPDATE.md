# Atualização orientada por IA — 2025-10-25

Este documento detalha as mudanças realizadas com auxílio de IA (Junie, JetBrains) para modernizar o projeto, garantir compatibilidade com as versões mais recentes do PHP/Laravel e fortalecer pontos sensíveis do código e do CI.

Última atualização: 2025-10-25 22:24 (horário local)

## Resumo executivo
- Compatibilidade oficial com PHP 8.2+ (testado em 8.2/8.3/8.4)
- Suporte oficial ao Laravel 10, 11 e 12
- CI (GitHub Actions) atualizado para a nova matriz e sem extensões obsoletas
- Harden no parser HTTP do `Api\AbstractAPI`
- Documentação atualizada (README e docs/install)
- Suíte de testes executada com sucesso

## Alterações por arquivo

### composer.json
- php: ">=8.2"
- laravel/framework: "^10.0|^11.0|^12.0"
- chillerlan/php-qrcode: "^5.0"
- eduardokum/laravel-mail-auto-embed: "^1.0" (substitui dev-master)
- require-dev:
  - phpunit/phpunit: "^11.2"
  - orchestra/testbench: "^8.0|^9.0|^10.0"

Impacto:
- Eleva requisito mínimo de PHP para 8.2 e garante compatibilidade recente de Laravel.
- Remove dependência instável em `dev-master`.

### .github/workflows/build.yml
- Nova matriz:
  - PHP: [8.4, 8.3, 8.2]
  - Laravel: [12.*, 11.*, 10.*]
  - Mapeamento Testbench: 12→10.*, 11→9.*, 10→8.*
- unitconfig: `phpunit_gt_81.xml` para todas as versões de PHP suportadas
- Excluída combinação inválida: Laravel 10 + PHP 8.4
- Removido `mcrypt` das extensões (não é mais parte do core)

Impacto:
- CI alinhado com versões suportadas, builds mais rápidos e menos ruído.

### src/Api/AbstractAPI.php
- Endurecimento do método `parseResponse()` ao processar cabeçalhos HTTP:
  - Ignora linhas vazias ou sem separador `:`
  - Usa `explode(':', $line, 2)`
  - `trim`/`ltrim` para normalização

Trecho final relevante:
```
foreach (explode("\r\n", $retorno->headers_text) as $i => $line) {
    if ($i === 0) {
        $retorno->headers['http_code'] = $line;
        continue;
    }
    if ($line === '' || strpos($line, ':') === false) {
        continue;
    }
    [$key, $value] = explode(':', $line, 2);
    $key = trim($key);
    $value = ltrim($value, ' ');
    $retorno->headers[$key] = $value;
}
```

Impacto:
- Evita avisos/erros com respostas HTTP atípicas e torna o parser mais resiliente.

### docs/source/install.rst
- Requisitos modernizados:
  - PHP 8.2+ (testado em 8.2/8.3/8.4)
  - Extensões: intl, mbstring, curl, json, openssl
  - Opcional: Laravel 10/11/12
- Instalação simplificada: `composer require eduardokum/laravel-boleto`
- Exemplo de composer.json atualizado com `^1.0`

### README.md
- Adicionada seção no topo: "Atualização realizada por IA (2025-10-25)" com resumo das mudanças e link para este arquivo
- Requisitos/suporte destacados:
  - PHP 8.2+
  - Laravel 10/11/12

## Testes
- Comando: `vendor\bin\phpunit -c phpunit_gt_81.xml`
- Ambiente: PHP 8.4.8
- Resultado: SUCESSO
  - Tests: 116
  - Assertions: 550
  - Warnings: 1 ("No code coverage driver available")

Observação: para eliminar o aviso localmente/CI, instale `pcov` ou `xdebug`, ou desabilite a coleta de cobertura.

## Considerações adicionais
- Opcional futuro: tornar CA path/CA info configuráveis no cURL do `AbstractAPI` (útil em Windows).
- Manter SemVer nas próximas releases, dado o aumento do requisito mínimo de PHP.

## Créditos
- Atualização automatizada por IA (Junie, JetBrains), com curadoria do mantenedor.
