<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm\Mock;

use Stringable;

use function Psl\Str\before_last;

class ProxyMocker extends MagentoCodeGenerationMocker
{
    private const TEMPLATE = <<<'TEMPLATE'
    <?php
    
    namespace {{namespace}};
    
    {{type}} {{class_name}} extends {{base_class}}
    {
    }
    TEMPLATE;

    protected function canGenerateClass(string $className): bool
    {
        return str_ends_with($className, '\Proxy');
    }

    protected function getBaseClassName(string $className): string|Stringable
    {
        return before_last($className, '\\') ?? $className;
    }

    protected function generateClass(string $baseClassName): string|Stringable
    {
        $type = interface_exists($baseClassName)
            ? 'interface'
            : 'class';

        return strtr(
            self::TEMPLATE,
            [
                '{{namespace}}' => $baseClassName,
                '{{type}}' => $type,
                '{{class_name}}' => 'Proxy',
                '{{base_class}}' => '\\' . $baseClassName,
            ]
        );
    }
}
