<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm;

use Renttek\Magento2Psalm\Mock\FactoryMocker;
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
        (new FactoryMocker())->registerAutoloader();

        $this->loadStubs($registration);
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
}
