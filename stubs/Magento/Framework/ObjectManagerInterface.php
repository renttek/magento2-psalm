<?php

declare(strict_types=1);

namespace Magento\Framework;

interface ObjectManagerInterface
{
    /**
     * @template T
     * @param class-string<T> $type
     * @return T
     */
    public function create(string $type, array $arguments = []);

    /**
     * @template T
     * @param class-string<T> $type
     * @return T
     */
    public function get(string $type);

    /**
     * @param array $configuration
     */
    public function configure(array $configuration): void;
}
