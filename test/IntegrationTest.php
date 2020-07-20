<?php declare(strict_types=1);

namespace Pages\Tests;

use Faker\Factory;
use Faker\Generator;
use Pages\Pages;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    private Generator $faker;
    private array $items = [];
    private ?Pages $pages;

    private function generateResult(): array
    {
        return [
            "name" => $this->faker->name,
            "telephone_number" => $this->faker->phoneNumber,
            "email_address" => $this->faker->safeEmail,
        ];
    }

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->items = $this->getResults(101);
        $this->pages = Pages::empty()
            ->appendCollection($this->items)
        ;
    }

    private function getResults(int $count): array
    {
        $generator = function ($count)  {
            for ($i = 0; $i < $count; $i++) {
                yield $this->generateResult();
            }
        };

        $results = [];
        foreach ($generator($count) as $pageItem) {
            $results[] = $pageItem;
        }

        return $results;
    }

    public function paginationDataProvider()
    {
        return[
            // First page with items indexed from zero, and relative to pagination
            [10, 0, false],
            [10, 0, true],

            // Second page with items indexed from zero, and relative to pagination
            [10, 1, false],
            [10, 1, true],

            // Final page (one  item) with items indexed from zero, and relative to pagination
            [10, 10, true],
            [10, 10, false],
        ];
    }

    /** @dataProvider paginationDataProvider */
    public function testPagination(int $pageSize, int $pageNumber, bool $indexRelativeToPagination): void
    {
        $page = $this->pages
            ->limitPageSize($pageSize)
            ->getPage($pageNumber)
        ;

        $actualItems = $page
            ->indexRelativeToPagination($indexRelativeToPagination)
            ->getItems()
        ;

        $expectedItems = array_slice(
            $this->items,
            $pageNumber * $pageSize, $pageSize,
            $indexRelativeToPagination)
        ;
        $this->assertSame($expectedItems, $actualItems);
    }
}
