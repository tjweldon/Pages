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
        $this->populatePageCache();

        return $this->pageCache ? $this->pageCache[$this->key()] : new Page([], 0);
    }

    /**
     * Given that pagination typically is used for returning a page from a much larger result set,
     * the Page objects are retained in object state. This means that full the paginated result set
     * can be cached using a document store such as redis.
     */
    private function populatePageCache(): void
    {
        if (!$this->pageCache && $this->items) {
            if ($this->getPageSizeLimit() && $this->getPageCountLimit()) {
                throw PagesException::bothLimitsSet($this);
            }

            // If neither limit is set then all items occupy a single page
            $pageCountLimit = $this->pageCountLimit;
            if (!$this->getPageSizeLimit() && !$this->getPageCountLimit()) {
                $pageCountLimit++;
            }

            $pageSize = $this->pageSizeLimit ?: intval($this->getItemCount() / $pageCountLimit);

            if ($pageCountLimit) {
                $remainder = $this->getItemCount() % $pageCountLimit;
                $pageSize = !$remainder ? $pageSize : $pageSize + 1;
            }

            $pageChunks = array_chunk($this->items, $pageSize, true);
            $this->pageCache = array_map(
                function ($pageChunk, $pageNumber) {
                    return new Page($pageChunk, $pageNumber);
                },
                $pageChunks,
                array_keys($pageChunks)
            );
        }
    }

    private function keyInBounds(?int $key): bool
    {
        $this->populatePageCache();

        return $key !== null && array_key_exists($key, $this->pageCache);
    }

    public function next(): void
    {
        $this->populatePageCache();

        $this->key = $this->keyInBounds($this->key + 1) ? $this->key + 1 : null;
    }

    public function key(): ?int
    {
        return $this->key;
    }

    public function valid(): bool
    {
        $this->populatePageCache();

        return $this->key === 0 || $this->keyInBounds($this->key);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }
}
