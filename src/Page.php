<?php declare(strict_types=1);

namespace Pages;

use Iterator;

class Page implements Iterator
{
    public function __construct(iterable $items)
    {
    }

    public function getPageNumber(): int
    {
        return 0;
    }

    public function getSize(): int
    {
        return 0;
    }

    public function absoluteKey(): int
    {
        return 0;
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
