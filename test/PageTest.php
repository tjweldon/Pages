<?php declare(strict_types=1);

namespace Pages\Tests;

use Exception;
use Pages\Exception\ItemException;
use Pages\Exception\PageNumberException;
use Pages\Page;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    public function validItemsProvider()
    {
        return [
            [["foo", "bar"], 0],
            [[], 0],
            [["item"], 10],
        ];
    }

    public function invalidItemsProvider()
    {
        return [
            [["foo", "bar"], -1, PageNumberException::pageNumberWasNegative(-1)],
            [[3 => null, 4 => "bar"], 1, ItemException::itemCollectionContainsNulls([3])],
        ];
    }

    /** @dataProvider validItemsProvider */
    public function testConstructor(array $items, $pageNumber)
    {
        $page = new Page($items, $pageNumber);

        $this->assertEquals(count($items), $page->getItemCount());
        $this->assertEquals($pageNumber, $page->getPageNumber());
    }

    /** @dataProvider invalidItemsProvider */
    public function testConstructorWithInvalidArgs(array $items, $pageNumber, Exception $expectedException)
    {
        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());

        $page = new Page($items, $pageNumber);
    }
}
