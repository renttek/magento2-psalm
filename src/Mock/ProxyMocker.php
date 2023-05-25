<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm\Mock;

use function Psl\Str\before_last;

class ProxyMocker extends MagentoCodeGenerationMocker
{
    private const TEMPLATE = <<<'TEMPLATE'
    <?php
    
    namespace {{namespace}};
    
    class {{class_name}} extends {{base_class}}
    {
    }
    TEMPLATE;

    protected function canGenerateClass(string $className): bool
    {
        return str_ends_with($className, '\Proxy');
    }

    protected function getBaseClassName(string $className): string
    {
        return before_last($className, '\\') ?? $className;
    }

    protected function generateClass(string $baseClassName): string
    {
        return strtr(
            self::TEMPLATE,
            [
                "{{namespace}}" => $baseClassName,
                "{{class_name}}" => "Proxy",
                "{{base_class}}" => '\\' . $baseClassName,
            ]
        );
    }
}
