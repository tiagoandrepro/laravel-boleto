# Guia de Upgrade — 5.0 (modernização PHP 8.2+ / Laravel 11–13)

## Requisitos novos

| | Antes | Agora |
|---|---|---|
| PHP | > 5.5 | **^8.2** (Laravel 13 exige 8.3+) |
| ext-gd | implícito | **declarado** (QR Code PIX com php-qrcode ^5) |
| Laravel | 6–12 | **11, 12 ou 13** (o 10 está EOL e todas as versions 10.x têm security advisories que o Composer atual bloqueia por padrão) |
| chillerlan/php-qrcode | ^3\|^4\|^5 | **^5.0** |
| eduardokum/laravel-mail-auto-embed | dev-master | **^2.13** |
| PHPUnit (dev) | ^6–^11 | ^10.5\|^11 |

## Breaking changes

### CalculoDV agora retorna `string` (ou `?string`)
Todos os métodos de `CalculoDV` retornam `string` — o DV é sempre gravado em
campo de largura fixa, e alguns bancos usam dígitos alfabéticos (`'X'`, `'P'`).
Os **valores são idênticos** (verificado por golden files); somente o tipo mudou.

- Se você compara com `===` contra inteiros (ex.: `CalculoDV::itauNossoNumero(...) === 5`),
  troque para comparação de string (`=== '5'`) ou `==`.
- `bbNossoNumero()` retorna `?string` (null quando o nosso número tem 17+ dígitos).
- `ourinvestAgencia()` retorna `?string`.

### Mail / LaravelBoletoMailer
- Caminho Swift_Mailer (Laravel < 9) removido — somente Symfony Mailer.
- `LaravelBoletoMailer::isLaravel9Plus()` removido.
- `Mail::send()` agora declara `?Boleto $boleto = null` (nullable explícito).

### AbstractRetorno (Countable / SeekableIterator)
Métodos de iteração ganharam tipos de retorno nativos (`current(): mixed`,
`next(): void`, `key(): mixed`, `valid(): bool`, `rewind(): void`,
`count(): int`, `seek($offset): void`). Se você sobrescreve algum deles,
adicione as mesmas assinaturas.

### UnauthorizedException
Não armazena mais certificado, chave nem senha (material PEM vazava quando a
exception era serializada por handlers de erro). Construtor agora é
`($baseUrl, $conta)`; `getCertificado*()`/`setCertificado*()` removidos.

### Util::file2array()
Lança `ValidationException` quando recebe um caminho de arquivo ilegível
(antes retornava `[]` silenciosamente e um retorno corrompido era processado
como "zero detalhes").

### AbstractWebhook
Corrigido bug em que os headers sobrescreviam o post no construtor —
`getPost()`/`getHeaders()` agora retornam o que foi passado. O webhook do
Inter passa a funcionar com headers.

### AbstractRemessa
- `save()`: cria diretórios com permissão `0755` (antes `0777`) e lança
  exceção quando o `mkdir` falha.
- `download()`: o nome do arquivo é sanitizado via novo
  `Util::sanitizeFilename()` (CRLF/path traversal); caracteres fora de
  `[A-Za-z0-9._-]` viram `_`. O mesmo vale para o nome do PDF em
  `Pdf::gerarBoleto()` com `OUTPUT_DOWNLOAD`/`OUTPUT_STANDARD`.

## Mudanças de comportamento (não-breaking)

- `Util::fetchPixLocation()`: força HTTPS, valida o certificado do servidor,
  aplica timeouts e retorna `[]` em erro de rede (antes seguia com `false`).
- `Util::fatorVencimento()`: a base FEBRABAN (07/10/1997) é ancorada no
  timezone da própria data, eliminando off-by-one entre timezones/DST.
- `AbstractBoleto::getMoraDia()`: arredondado a 2 casas na origem via novo
  `Util::moneyRound()` (antes o arredondamento só ocorria no formatCnab).
- Logs de debug da API têm `Authorization`/`Bearer`/cookies redigidos.
- Certificados temporários são criados com `chmod 0600`.

## Novidades opt-in

- `Pessoa::validarDocumentos()` — habilita validação de dígito verificador
  de CPF/CNPJ em `setDocumento()` (desligado por padrão para não quebrar
  dados de teste).

## Para mantenedores

- Golden files em `tests/Snapshot/fixtures/` travam código de barras, linha
  digitável, remessas CNAB 240/400, retornos e vetores de DV de todos os
  bancos. Qualquer mudança de comportamento exige `UPDATE_SNAPSHOTS=1` e
  revisão manual do diff.
- `rector.php` guarda a regra de nullables explícitos (PHP 8.4+).
- CI: matriz PHP 8.2–8.5 × Laravel 11–13 (`.github/workflows/build.yml`).
