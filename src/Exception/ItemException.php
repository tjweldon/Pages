<?php declare(strict_types=1);

namespace Pages\Exception;

use InvalidArgumentException;

class ItemException extends InvalidArgumentException
{
    public static function itemWasNull(): self
    {
        return new self("An item passed was null, Items must not be null.");
    }

    public static function itemCollectionContainsNulls(array $nullKeys): self
    {
        $nullKeysString = implode(", ", $nullKeys);

        return new self("The item collection contained null values at the following keys {$nullKeysString}");
    }
}
