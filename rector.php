<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withSkip([
        // Views blade não são código de aplicação comum
        __DIR__ . '/src/Boleto/Render/view',
    ])
    // Apenas a correção objetiva exigida pelo PHP 8.4+:
    // `Type $x = null` => `?Type $x = null`
    ->withPhpVersion(Rector\ValueObject\PhpVersion::PHP_82)
    ->withRules([
        Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector::class,
    ]);
