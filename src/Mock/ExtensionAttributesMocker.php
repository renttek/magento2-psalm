<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm\Mock;

use DOMDocument;
use DOMElement;
use Renttek\Magento2Psalm\FileReader\ExtensionAttributesReader;
use Stringable;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function ksort;
use function Psl\Dict\flatten;
use function Psl\Iter\reduce;
use function Psl\Str\before_last;
use function Psl\Str\Byte\after_last;
use function Psl\Vec\flat_map;
use function Psl\Vec\keys;
use function Psl\Vec\values;
use function Symfony\Component\String\s;

class ExtensionAttributesMocker extends MagentoCodeGenerationMocker
{
    private const TEMPLATE = <<<'TEMPLATE'
    <?php
    
    namespace {{namespace}};

    interface {{class_name}} extends \Magento\Framework\Api\ExtensionAttributesInterface
    {
    {{methods}}
    }
    TEMPLATE;

    private const METHOD_TEMPLATE = <<<'TEMPLATE'
    /**
     * @return \{{type}}|null
     */
    public function get{{method_name}}();

    /**
     * @param \{{type}} ${{parameter_name}}
     * @return $this
     */
    public function set{{method_name}}(${{parameter_name}});
    TEMPLATE;

    /**
     * @var array<class-string, list<array{type: string, code: string}>>|null
     */
    private ?array $definitions = null;

    public function __construct(
        private readonly Finder $finder = new Finder(),
        private readonly ExtensionAttributesReader $extensionAttributesReader = new ExtensionAttributesReader(),
    ) {
    }

    protected function canGenerateClass(string $className): bool
    {
        return str_ends_with($className, 'ExtensionInterface')
            && !str_ends_with($className, '\ExtensionInterface')
            && in_array(
                (string)$this->getBaseClassName($className),
                keys($this->getExtensionAttributes()),
                true
            );
    }

    protected function getBaseClassName(string $className): string|Stringable
    {
        return substr($className, 0, strlen($className) - strlen('ExtensionInterface')) . 'Interface';
    }

    protected function generateClass(string $baseClassName): string|Stringable
    {
        $definition = $this->getExtensionAttributes()[$baseClassName];

        $methods = [];
        foreach ($definition as $attribute) {
            $methods[$attribute['code']] = strtr(
                self::METHOD_TEMPLATE,
                [
                    '{{type}}' => $attribute['type'],
                    '{{method_name}}' => s($attribute['code'])->camel()->title(),
                    '{{parameter_name}}' => s($attribute['code'])->camel(),
                ]
            );
        }

        $className = after_last($baseClassName, '\\') ?? '';

        return strtr(
            self::TEMPLATE,
            [
                "{{namespace}}" => before_last($baseClassName, '\\') ?? $baseClassName,
                "{{class_name}}" => s($className)->trimSuffix('Interface')->ensureEnd('ExtensionInterface'),
                "{{methods}}" => implode("\n", values($methods)),
            ]
        );
    }

    /**
     * @return array<class-string, list<array{type: string, code: string}>>
     */
    private function getExtensionAttributes(): array
    {
        if ($this->definitions === null) {
            $files = $this->finder
                ->files()
                ->in(values($this->getModules()))
                ->name('extension_attributes.xml');

            $this->definitions = $this->reindexDefinitions(
                flat_map(
                    $files,
                    fn (SplFileInfo $file) => $this
                        ->extensionAttributesReader
                        ->getDefinitionsFromFile($file)
                )
            );

            ksort($this->definitions);
        }

        return $this->definitions;
    }

    /**
     * @param list<array{class: class-string, type: string, code: string}> $definitions
     *
     * @return array<class-string, list<array{type: string, code: string}>>
     */
    private function reindexDefinitions(array $definitions): array
    {
        return reduce(
            $definitions,
            static function ($acc, $val) {
                $acc[$val['class']] ??= [];
                $acc[$val['class']][] = [
                    'code' => $val['code'],
                    'type' => $val['type'],
                ];

                return $acc;
            },
            []
        );
    }
}
