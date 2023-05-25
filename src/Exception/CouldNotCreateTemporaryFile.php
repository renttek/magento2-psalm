<?php

declare(strict_types=1);

namespace Renttek\Magento2Psalm\Exception;

use RuntimeException;

class CouldNotCreateTemporaryFile extends RuntimeException
{
    public static function couldNotCreateFile(): self
    {
        return new self('Could not create temp file');
    }

    public static function couldNotGetPath(): self
    {
        return new self('Temp file path not found in metadata');
    }
}
