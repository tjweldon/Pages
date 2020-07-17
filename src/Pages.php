<?php declare(strict_types=1);

namespace Pages;

use Iterator;
use Pages\Exception\ItemException;
use Pages\Exception\LimitsException;
use Pages\Exception\PagesException;
use Traversable;

class Pages implements Iterator
{
    /**
     * When this is non-zero it will enforce a maximum number of pages,
     * the pages will be resized to satisfy this limit. The interface
     * disallows this and $pageSizeLimit from being non-zero simultaneously
     */
    private int $pageCountLimit = 0;

    /**
     * When this is non-zero it will enforce a maximum page size (item count),
     * the number of pages will increase to accommodate more items. The interface
     * disallows this and $pageCountLimit from being non-zero simultaneously
     */
    private int $pageSizeLimit = 0;

    private array $items = [];

    private ?int $key = 0;

    /**
     * @var Page[]
     */
    private array $pageCache = [];

    public static function empty(): self
    {
        return new self();
    }

    public function append($item): self
    {
        if ($item === null) {
            throw ItemException::itemWasNull();
        }
        $this->pageCache = [];
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
        if ($maxPageSize <= 0) {
            throw LimitsException::pageSizeIsLessThanOne($maxPageSize);
        }
        $this->pageCache = [];
        $this->pageSizeLimit = $maxPageSize;
        $this->pageCountLimit = 0;

        return $this;
    }

    public function limitPageCount(int $maxPageCount): self
    {
        if ($maxPageCount <= 0) {
            throw LimitsException::pageCountIsLessThanOne($maxPageCount);
        }
        $this->pageCache = [];
        $this->pageCountLimit = $maxPageCount;
        $this->pageSizeLimit = 0;

        return $this;
    }

    public function getPageCountLimit(): int
    {
        return $this->pageCountLimit;
    }

    public function getPageCount(): int
    {
        return $pageCount = $this->pageCountLimit ?: intval(ceil($this->getItemCount() / $this->pageSizeLimit));
    }

    public function getPageSizeLimit(): int
    {
        return $this->pageSizeLimit;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function current(): Page
    {
        if ($this->getPageSizeLimit() && $this->getPageCountLimit()) {
            throw PagesException::incompatibleLimits($this);
        }

        if (!$this->pageCache) {
            $this->pageCache = $this->generatePages();
        }

        return $this->pageCache[$this->key];
    }

    /**
     * @return Page[]
     */
    private function generatePages(): array
    {
        $pageSize = $this->pageSizeLimit ?: intval($this->getItemCount() / $this->pageCountLimit);

        if ($this->pageCountLimit) {
            $remainder = $this->getItemCount() % $this->pageCountLimit;
            $pageSize = !$remainder ?: $pageSize + 1;
        }

        $pageChunks = array_chunk($this->items, $pageSize, true);
        $pages = array_map(
            function ($pageChunk) {
                return new Page($pageChunk);
            },
            $pageChunks
        );

        return $pages;
    }

    private function keyInBounds(?int $key): bool
    {
        return $key !== null && array_key_exists($key, $this->items);
    }

    public function next(): void
    {
        $this->key = $this->keyInBounds($this->key + 1) ? $this->key + 1 : null;
    }

    public function key(): ?int
    {
        return $this->key;
    }

    public function valid(): bool
    {
        return $this->keyInBounds($this->key());
    }

    public function rewind(): void
    {
        $this->key = 0;
    }
}
