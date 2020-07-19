<?php declare(strict_types=1);

namespace Pages\Exception;

use DomainException;
use Pages\Pages;

class PagesException extends DomainException
{
    public static function bothLimitsSet(Pages $pages): self
    {
        return new self(
            "Pages has non-zero limits for both pageCount: {$pages->getPageCountLimit()} and pageSize:" .
            " {$pages->getPageSizeLimit()}. Have you been using reflection?"
        );
    }
}
