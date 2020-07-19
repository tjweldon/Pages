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
    private bool $indexRelativeToPagination = false;

    public function __construct(array $items, int $pageNumber, int $initialIndex = null)
    {
        if ($pageNumber < 0) {
            throw PageNumberException::pageNumberWasNegative($pageNumber);
        }
        if ($nullKeys = array_keys($items, null, true)) {
            throw ItemException::itemCollectionContainsNulls($nullKeys);
        }

        $this->items = array_values($items);
        $this->key = $items ? 0 : null;
        $this->pageNumber = $pageNumber;
        if ($initialIndex !== null) {
            $this->initialIndex = $initialIndex;
        }
        if ($initialIndex === null && $items) {
            $this->initialIndex = array_keys($items)[0];
        }
    }

    public function indexRelativeToPagination(bool $indexRelativeToPagination) : self
    {
        $this->indexRelativeToPagination = $indexRelativeToPagination;

        return $this;
    }

    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function getInitialIndex(): int
    {
        return $this->initialIndex;
    }

    private function getKey(): ?int
    {
        return $this->key + intval($this->indexRelativeToPagination) * $this->initialIndex;
    }

    private function keyIsValid(?int $key): bool
    {
        return $key !== null && array_key_exists($key, $this->items);
    }

    public function current()
    {
        return $this->keyIsValid($this->key) ?
            $this->items[$this->key] :
            null
        ;
    }

    public function next(): void
    {
        $this->key = $this->keyIsValid($this->key) && $this->keyIsValid($this->key + 1) ?
            $this->key + 1 :
            null
        ;
    }

    public function key(): ?int
    {
        return $this->getKey();
    }

    public function valid(): bool
    {
        return $this->keyIsValid($this->key);
    }

    public function rewind(): void
    {
        $this->key = $this->keyIsValid(0) ?
            0 :
            null
        ;
    }
}
