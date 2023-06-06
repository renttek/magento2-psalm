<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm;

use Renttek\Magento2Psalm\Mock\ExtensionAttributesMocker;
use Renttek\Magento2Psalm\Mock\FactoryMocker;
use Renttek\Magento2Psalm\Mock\ProxyMocker;
use SimpleXMLElement;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use Symfony\Component\Finder\Finder;

class Plugin implements PluginEntryPointInterface
{
    public function __construct(
        private readonly Finder $finder = new Finder(),
        private readonly ?string $stubDirectory = null,
    ) {
    }

    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        if ($this->getConfigFlag($config, 'enableExtensionAttributeMocker', true)) {
            (new ExtensionAttributesMocker())->registerAutoloader();
        }
        if ($this->getConfigFlag($config, 'enableFactoryMocker', true)) {
            (new FactoryMocker())->registerAutoloader();
        }
        if ($this->getConfigFlag($config, 'enableProxyMocker', true)) {
            (new ProxyMocker())->registerAutoloader();
        }
        if ($this->getConfigFlag($config, 'loadStubs', true)) {
            $this->loadStubs($registration);
        }
    }

    private function loadStubs(RegistrationInterface $psalm): void
    {
        $stubFiles = $this->finder
            ->files()
            ->in($this->getStubDirectory())
            ->name('*.php');

        foreach ($stubFiles as $stubFile) {
            $psalm->addStubFile($stubFile->getPath());
        }
    }

    private function getStubDirectory(): string
    {
        return $this->stubDirectory ?? __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'stubs';
    }

    private function getConfigFlag(?SimpleXMLElement $config, string $flag, bool $default): bool
    {
        return filter_var($config?->$flag ?? $default, FILTER_VALIDATE_BOOLEAN);
    }
}
