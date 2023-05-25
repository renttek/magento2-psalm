<?php

declare(strict_types=1);

namespace Magento\Framework\Component;

interface ComponentRegistrarInterface
{
    /**
     * @psalm-return array<string, string>
     */
    public function getPaths(string $type): array;
}
