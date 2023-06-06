<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm\Mock;

use Magento\Framework\Component\ComponentRegistrar;
use Renttek\Magento2Psalm\Exception\CouldNotCreateTemporaryFile;
use RuntimeException;

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
     * @param class-string $className
     */
    abstract protected function canGenerateClass(string $className): bool;

    abstract protected function getBaseClassName(string $className): string;

    abstract protected function generateClass(string $baseClassName): string;

    public function registerAutoloader(): void
    {
        spl_autoload_register(function (string $className) {
            /** @var class-string $className */
            if (!$this->canGenerateClass($className)) {
                return;
            }

            $this->moduleNamespaces ??= $this->getModuleNamespaces();
            if (!$this->isInMagentoModuleNamespaces($this->moduleNamespaces, $className)) {
                return;
            }

            $baseClassName = $this->getBaseClassName($className);

            if (!class_exists($baseClassName) && !interface_exists($baseClassName)) {
                return;
            }

            $content = $this->generateClass($baseClassName);
            $this->addStubFile($content);
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
     * @return list<string>
     */
    private function getModuleNamespaces(): array
    {
        $registrar   = (new ComponentRegistrar());
        $modulePaths = $registrar->getPaths(ComponentRegistrar::MODULE);
        $moduleNames = keys($modulePaths);

        /** @psalm-suppress MixedArgumentTypeCoercion */
        return map($moduleNames, fn (string $name) => str_replace('_', '\\', $name));
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
