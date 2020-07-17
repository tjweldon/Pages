<?php declare(strict_types=1);

namespace Pages\Exception;

use InvalidArgumentException;

class LimitsException extends InvalidArgumentException
{
    public static function pageSizeIsLessThanOne(int $maxPageSize): self
    {
        return new self(
            "The page size limit supplied {$maxPageSize} was negative or zero," .
            " please supply a value of one or greater"
        );
    }

    public static function pageCountIsLessThanOne(int $maxPageCount): self
    {
        return new self(
            "The page count limit supplied {$maxPageCount} was negative or zero," .
            " please supply a value of one or greater"
        );
    }
}
