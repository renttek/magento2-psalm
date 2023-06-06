<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm\Mock;

use Magento\Framework\Component\ComponentRegistrar;
use Renttek\Magento2Psalm\Exception\CouldNotCreateTemporaryFile;
use RuntimeException;
use Stringable;

use function Psl\Dict\map_keys;
use function Psl\Iter\any;
use function Psl\Str\before_last;
use function Psl\Vec\keys;
use function Psl\Vec\map;

abstract class MagentoCodeGenerationMocker
{
    /**
     * @var list<resource>
     */
    private array $tempFiles = [];

    /**
     * @var list<string>|null
     */
    private ?array $moduleNamespaces = null;

    /**
     * @var array<string, string>|null
     */
    private ?array $modules = null;

    /**
     * @param class-string $className
     */
    abstract protected function canGenerateClass(string $className): bool;

    abstract protected function getBaseClassName(string $className): string|Stringable;

    abstract protected function generateClass(string $baseClassName): string|Stringable;

    public function registerAutoloader(): void
    {
        spl_autoload_register(function (string $className) {
            /** @var class-string $className */
            if (!$this->canGenerateClass($className)) {
                return;
            }

            $this->moduleNamespaces ??= keys($this->getModules());
            if (!$this->isInMagentoModuleNamespaces($this->moduleNamespaces, $className)) {
                return;
            }

            $baseClassName = (string)$this->getBaseClassName($className);
            if (!class_exists($baseClassName) && !interface_exists($baseClassName)) {
                return;
            }

            $this->addStubFile(
                (string)$this->generateClass($baseClassName)
            );
        });
    }

    private function addStubFile(string $content): void
    {
        [
            'path' => $filePath,
            'file' => $fileHandle,
        ] = $this->createTempFile();

        fwrite($fileHandle, $content);

        require_once $filePath;
    }

    /**
     * @return array<string, string>
     */
    protected function getModules(): array
    {
        return $this->modules ??= map_keys(
            (new ComponentRegistrar())->getPaths(ComponentRegistrar::MODULE),
            fn (string $name) => str_replace('_', '\\', $name)
        );
    }

    /**
     * @param list<string> $namespaces
     * @param class-string $className
     */
    private function isInMagentoModuleNamespaces(array $namespaces, string $className): bool
    {
        return any($namespaces, fn (string $namespace) => str_starts_with($className, $namespace))
            || str_starts_with($className, 'Magento\\Framework\\');
    }

    /**
     * @return array{path: string, file: resource}
     *
     * @throws RuntimeException
     */
    private function createTempFile(): array
    {
        $tempFile = tmpfile();
        if ($tempFile === false) {
            throw CouldNotCreateTemporaryFile::couldNotCreateFile();
        }

        $this->tempFiles[] = $tempFile;

        /** @see https://www.php.net/manual/en/function.tmpfile.php#122678 */
        $tempFileMetaData = stream_get_meta_data($tempFile);

        return [
            'path' => $tempFileMetaData['uri'],
            'file' => $tempFile,
        ];
    }
}
