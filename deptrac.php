<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\DirectoryConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

return static function (DeptracConfig $config): void {
    $config
        ->paths('./src')
        ->excludeFiles('#.*test.*#')
        ->layers(
            $contracts  = Layer::withName('Contracts')->collectors(DirectoryConfig::create('src/Contracts/.*')),
            $dto        = Layer::withName('Dto')->collectors(DirectoryConfig::create('src/Dto/.*')),
            $exceptions = Layer::withName('Exceptions')->collectors(DirectoryConfig::create('src/Exceptions/.*')),
            $support    = Layer::withName('Support')->collectors(DirectoryConfig::create('src/Support/.*')),
            $storage    = Layer::withName('Storage')->collectors(DirectoryConfig::create('src/Storage/.*')),
            $providers  = Layer::withName('Providers')->collectors(DirectoryConfig::create('src/Providers/.*')),
            $core       = Layer::withName('Core')->collectors(
                DirectoryConfig::create('src/PageTranslator.php'),
                DirectoryConfig::create('src/TranslationLoader.php'),
            ),
        )
        ->rulesets(
            
            Ruleset::forLayer($exceptions),
            Ruleset::forLayer($dto)->accesses($exceptions),
            Ruleset::forLayer($contracts)->accesses($dto, $exceptions),
            Ruleset::forLayer($support)->accesses($contracts, $dto, $exceptions),
            Ruleset::forLayer($storage)->accesses($contracts, $dto, $exceptions),
            Ruleset::forLayer($providers)->accesses($contracts, $dto, $exceptions, $support),
            Ruleset::forLayer($core)->accesses($contracts, $dto, $exceptions, $support, $providers, $storage),
        )
    ;
};