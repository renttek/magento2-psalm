<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm\Mock;

use function Psl\Str\before_last;

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

    protected function getBaseClassName(string $className): string
    {
        return substr($className, strlen($className) - 7);
    }

    protected function generateClass(string $baseClassName): string
    {
        return strtr(
            self::TEMPLATE,
            [
                "{{namespace}}" => before_last($baseClassName, '\\') ?? $baseClassName,
                "{{class_name}}" => "{$baseClassName}Factory",
                "{{return_class}}" => '\\' . $baseClassName,
            ]
        );
    }
}
