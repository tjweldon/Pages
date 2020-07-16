<?php declare(strict_types=1);

namespace Pages;

use Iterator;
use Pages\Exception\ItemException;
use Traversable;

class Pages implements Iterator
{
    private int $pagesLimit = 0;
    private int $pageSizeLimit = 0;
    private array $items = [];

    public static function empty(): self
    {
        return new self();
    }

    public function append($item): self
    {
        if ($item === null) {
            throw ItemException::itemWasNull();
        }
        $this->items[] = $item;

        return $this;
    }

    /**
     * @var array|Traversable $itemCollection
     */
    public function appendCollection(iterable $itemCollection): self
    {
        $nullKeys = [];
        foreach ($itemCollection as $key => $item) {
            if ($item === null) {
                $nullKeys[] = $key;
                continue;
            }

            $this->append($item);
        }
        if ($nullKeys) {
            throw ItemException::itemCollectionContainsNulls($nullKeys);
        }

        return $this;
    }

    public function limitPageSize(int $maxPageSize): self
    {
        return new self();
    }

    public function limitNumberOfPages(int $maxPages): self
    {
        return new self();
    }

    public function getPagesLimit(): int
    {
        return $this->pagesLimit;
    }

    public function getPageSizeLimit(): int
    {
        return $this->pageSizeLimit;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getPageNumber(): int
    {
        return $this->key();
    }

    public function current(): Page
    {
        return new Page([]);
    }

    public function next(): Page
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
