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
            [["foo", "bar"], 1, 100],
            [[], 0, 0],
            [["item"], 10, 10],
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
    public function testConstructor(array $items, $pageNumber, $initialIndex = null)
    {
        $page = new Page($items, $pageNumber, $initialIndex);

        $this->assertEquals(count($items), $page->getItemCount());
        $this->assertEquals($pageNumber, $page->getPageNumber());
        $this->assertEquals($initialIndex, $page->getInitialIndex());
    }

    /** @dataProvider invalidItemsProvider */
    public function testConstructorWithInvalidArgs(array $items, $pageNumber, Exception $expectedException)
    {
        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());

        new Page($items, $pageNumber);
    }

    public function testIndexingRelativeToPage()
    {
        $page = new Page(
            array_fill(0, 3, "foo"),
            1,
            3
        );

        $expectedIndices = [];
        $actualIndices = [];
        $expectedIndex = 0;
        foreach ($page as $index => $item) {
            $actualIndices[] = $index;
            $expectedIndices[] = $expectedIndex;
            $expectedIndex++;
        }
        $this->assertEquals($expectedIndices, $actualIndices);
    }

    public function testIndexingRelativeToPagination()
    {
        $page = new Page(
            array_fill(0, 3, "foo"),
            1,
            3
        );

        $expectedIndices = [];
        $actualIndices = [];
        $expectedIndex = 3;
        foreach ($page->indexRelativeToPagination(true) as $index => $item) {
            $actualIndices[] = $index;
            $expectedIndices[] = $expectedIndex;
            $expectedIndex++;
        }
        $this->assertEquals($expectedIndices, $actualIndices);
    }
}
