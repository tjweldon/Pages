<?php declare(strict_types=1);

namespace Pages\Exception;

use InvalidArgumentException;

class PageNumberException extends InvalidArgumentException
{
    public static function pageNumberWasNegative($pageNumber): self
    {
        return new self(
            "The page number supplied: {$pageNumber} was negative. Supply a number of zero or greater."
        );
    }
}
