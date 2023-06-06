<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm\Mock;

use function Psl\Str\before_last;
use function Psl\Str\Byte\after_last;
use Stringable;
use function Symfony\Component\String\s;

class FactoryMocker extends MagentoCodeGenerationMocker
{
    private const TEMPLATE = <<<'TEMPLATE'
    <?php
    
    namespace {{namespace}};
    
    class {{class_name}}
    {
        /**
         * @param array<string, mixed>
         */
        public function create(array $params = []): {{return_class}}
        {
            // Stubbed file
        }
    }
    TEMPLATE;

    protected function canGenerateClass(string $className): bool
    {
        return str_ends_with($className, 'Factory')
            && !str_ends_with($className, '\Factory');
    }

    protected function getBaseClassName(string $className): string|Stringable
    {
        return s($className)->trimSuffix('Factory');
    }

    protected function generateClass(string $baseClassName): string|Stringable
    {
        $className = after_last($baseClassName, '\\') ?? '';

        return strtr(
            self::TEMPLATE,
            [
                "{{namespace}}" => before_last($baseClassName, '\\') ?? $baseClassName,
                "{{class_name}}" => s($className)->ensureEnd('Factory'),
                "{{return_class}}" => '\\' . $baseClassName,
            ]
        );
    }
}
