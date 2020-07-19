<?php declare(strict_types=1);

namespace Pages;

use Iterator;
use Pages\Exception\ItemException;
use Pages\Exception\PageNumberException;

class Page implements Iterator
{
    private array $items = [];
    private int $pageNumber = 0;
    private ?int $key;
    private int $initialIndex = 0;

    public function __construct(array $items, int $pageNumber, int $initialIndex = null)
    {
        if ($pageNumber < 0) {
            throw PageNumberException::pageNumberWasNegative($pageNumber);
        }
        if ($nullKeys = array_keys($items, null, true)) {
            throw ItemException::itemCollectionContainsNulls($nullKeys);
        }

        $this->items = $items;
        $this->key = $items ? 0 : null;
        $this->pageNumber = $pageNumber;
        if ($initialIndex !== null) {
            $this->initialIndex = $initialIndex;
        }
        if ($initialIndex === null && $items) {
            $this->initialIndex = array_keys($items)[0];
        }
    }

    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    /**
     * @return mixed|void
     */
    public function current()
    {
        // TODO: Implement current() method.
    }

    public function next(): void
    {
        // TODO: Implement next() method.
    }

    public function key(): int
    {
        // TODO: Implement key() method.
    }

    public function valid(): bool
    {
        // TODO: Implement valid() method.
    }

    public function rewind(): void
    {
        // TODO: Implement rewind() method.
    }
}
