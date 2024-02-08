<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Data\Person;
use function PHPUnit\Framework\assertEquals;
use Illuminate\Foundation\Testing\WithFaker;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\LazyCollection;

use function PHPUnit\Framework\assertEqualsCanonicalizing;

class CollectionTest extends TestCase
{
    public function testCreateCollection()
    {
        $collection = collect([1, 2, 3]);
        $this->assertEqualsCanonicalizing(
            [1, 2, 3],
            $collection->all()
        );
    }
    public function testForEach()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        foreach ($collection as $key => $value) {
            $this->assertEquals($key + 1, $value);
        }
    }

    public function testCrud()
    {
        $collection = collect([]);
        $collection->push(1, 2, 3);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collection->all());

        $result = $collection->pop();
        $this->assertEquals(3, $result);
        $this->assertEqualsCanonicalizing([1, 2], $collection->all());
    }
    public function testMap()
    {
        $collection = collect([1, 2, 3]);
        $result = $collection->map(function ($item) {
            return $item * 2;
        });

        $this->assertEqualsCanonicalizing([2, 4, 6], $result->all());
    }

    public function testMapInto()
    {
        $collection = collect(["al"]);
        $result = $collection->mapInto(Person::class);

        $this->assertEquals([new Person('al')], $result->all());
    }

    public function testMapSpread()
    {
        $collection = collect([
            [
                "al",
                "syam"
            ],
            [
                "bur",
                "han"
            ]
        ]);

        $result = $collection->mapSpread(function ($firstName, $lastName) {
            $fullName = $firstName . ' ' . $lastName;
            return new Person($fullName);
        });

        $this->assertEquals([
            new Person("al syam"),
            new Person("bur han")
        ], $result->all());
    }

    public function testMapGroup()
    {
        $collection = collect([
            [
                "name" => "al",
                "department" => "IT",
            ],
            [
                "name" => "syam",
                "department" => "HR",
            ],
            [
                "name" => "cuy",
                "department" => "IT",
            ]
        ]);

        $result = $collection->mapToGroups(function ($item) {
            return [$item["department"] => $item["name"]];
        });

        $this->assertEquals([
            "IT" => collect(["al", "cuy"]),
            "HR" => collect(["syam"])
        ], $result->all());
    }

    public function testZip()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->zip($collection2);

        $this->assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6])
        ], $collection3->all());
    }
    public function testConcat()
    {
        $collection1 = collect([1, 2, 3]);
        $collection2 = collect([4, 5, 6]);
        $collection3 = $collection1->concat($collection2);

        $this->assertEquals([
            1, 2, 3, 4, 5, 6
        ], $collection3->all());
    }
    public function testCombine()
    {
        $collection1 = collect(["name", "country"]);
        $collection2 = collect(["Al", "India"]);
        $collection3 = $collection1->combine($collection2);

        $this->assertEquals([
            "name" => "Al",
            "country" => "India",
        ], $collection3->all());
    }

    public function testCollapse()
    {
        $collection = collect([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9]
        ]);

        $result = $collection->collapse();
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9], $result->all());
    }
    public function testFlatMap()
    {
        $collection = collect([
            [
                "name" => "al",
                "hobbies" => ["coding", "gaming"],
            ],
            [
                "name" => "syam",
                "hobbies" => ["tennis", "balet"],
            ]
        ]);

        $result = $collection->flatMap(function ($item) {
            return $item["hobbies"];
        });
        $this->assertEquals(["coding", "gaming", "tennis", "balet"], $result->all());
    }

    public function testJoin()
    {
        $collection = collect(["al", "syam", "ah"]);
        $this->assertEquals("al-syam-ah", $collection->join("-"));
        $this->assertEquals("al-syam_ah", $collection->join("-", "_"));
        $this->assertEquals("al, syam and ah", $collection->join(", ", " and "));
    }

    public function testFilter()
    {
        $collection = collect([
            "al" => 100,
            "syam" => 90,
            "cuy" => 80
        ]);

        $result = $collection->filter(function ($value, $key) {
            return  $value >= 90;
        });

        $this->assertEquals([
            "al" => 100,
            "syam" => 90,
        ], $result->all());
    }

    public function  testFilterIndex()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->filter(function ($value, $key) {
            return $value % 2 == 0;
        });

        $this->assertEqualsCanonicalizing([2, 4, 6, 8, 10], $result->all());
    }

    public function testPartition()
    {
        $collection = collect([
            "al" => 100,
            "cuy" => 80,
            "syam" => 90
        ]);
        [$result1, $result2] = $collection->partition(function ($item, $key) {
            return $item >= 90;
        });
        $this->assertEquals([
            "al" => 100,
            "syam" => 90,
        ], $result1->all());
        $this->assertEquals([
            "cuy" => 80
        ], $result2->all());
    }

    public function testTesting()
    {
        $collection = collect([
            "al",
            "syam",
            "cuy"
        ]);
        self::assertTrue($collection->contains("al"));
        self::assertTrue($collection->contains(function ($value, $key) {
            return $value == "cuy";
        }));
    }

    public function testGeouping()
    {
        $collection = collect([
            [
                "name" => "al",
                "department" => "IT",
            ],
            [
                "name" => "syam",
                "department" => "HR",
            ],
            [
                "name" => "cuy",
                "department" => "IT",
            ]
        ]);

        $result = $collection->groupBy("department");

        assertEquals([
            "IT" => collect([
                [
                    "name" => "al",
                    "department" => "IT",
                ],
                [
                    "name" => "cuy",
                    "department" => "IT",
                ]
            ]),
            "HR" => collect([
                [
                    "name" => "syam",
                    "department" => "HR",
                ]
            ])
        ], $result->all());

        $result = $collection->groupBy(function ($value, $key) {
            return strtolower($value["department"]);
        });

        assertEquals([
            "it" => collect([
                [
                    "name" => "al",
                    "department" => "IT",
                ],
                [
                    "name" => "cuy",
                    "department" => "IT",
                ]
            ]),
            "hr" => collect([
                [
                    "name" => "syam",
                    "department" => "HR",
                ]
            ])
        ], $result->all());
    }

    public function testSlice()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $result = $collection->slice(3);

        assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());
        $result = $collection->slice(3, 2);

        assertEqualsCanonicalizing([4, 5], $result->all());
    }

    public function testTake()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->take(3);
        assertEqualsCanonicalizing([1, 2, 3], $result->all());

        $result = $collection->takeUntil(function ($value, $key) {
            return $value == 3;
        });
        assertEqualsCanonicalizing([1, 2], $result->all());

        $result = $collection->takeWhile(function ($value, $key) {
            return $value < 3;
        });
        assertEqualsCanonicalizing([1, 2], $result->all());
    }

    public function testSkip()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collection->skip(8);
        assertEqualsCanonicalizing([9], $result->all());

        $result = $collection->skipUntil(function ($value, $key) {
            return $value == 7;
        });
        assertEqualsCanonicalizing([7, 8, 9], $result->all());

        $result = $collection->skipWhile(function ($value, $key) {
            return $value < 8;
        });
        assertEqualsCanonicalizing([8, 9], $result->all());
    }

    public function testChunked()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->chunk(3);

        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all()[0]->all());
        $this->assertEqualsCanonicalizing([4, 5, 6], $result->all()[1]->all());
        $this->assertEqualsCanonicalizing([7, 8, 9], $result->all()[2]->all());
        $this->assertEqualsCanonicalizing([10], $result->all()[3]->all());
    }

    public function testFirst()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->first();
        $this->assertEquals(1, $result);

        $result = $collection->first(function ($value, $key) {
            return $value > 3;
        });

        $this->assertEquals(4, $result);
    }


    public function testLast()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->last();
        $this->assertEquals(10, $result);

        $result = $collection->last(function ($value, $key) {
            return $value < 3;
        });

        $this->assertEquals(2, $result);
    }

    public function testRandom()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collection->random();
        $this->assertTrue(in_array($result, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]));

        // $result = $collection->random(5);
        // $this->assertTrue(in_array($result, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]));
    }

    public function testCheckingExistence()
    {
        $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertTrue($collection->isNotEmpty());
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->contains(9));
        $this->assertFalse($collection->contains(78));
        $this->assertTrue($collection->contains(function ($value, $key) {
            return $value == 10;
        }));
    }

    public function testOrdering()
    {
        $collection = collect([1, 4, 3, 2]);
        $result = $collection->sort();
        $this->assertEqualsCanonicalizing([1, 2, 3, 4], $result->all());

        $result = $collection->sortDesc();
        $this->assertEqualsCanonicalizing([4, 3, 2, 1], $result->all());
    }

    public function testAgregate()
    {
        $collection = collect([1, 2, 3, 4, 5]);
        $result = $collection->sum();
        $this->assertEquals(15, $result);

        $result = $collection->avg();
        $this->assertEquals(3, $result);

        $result = $collection->min();
        $this->assertEquals(1, $result);

        $result = $collection->max();
        $this->assertEquals(5, $result);
    }
    public function testReduce()
    {
        $collection = collect([1, 2, 3, 4, 5]);
        $result = $collection->reduce(function ($carry, $item) {
            return $carry + $item;
        });
        $this->assertEquals(15, $result);
    }

    public function testLazyCollection()
    {
        $collection = LazyCollection::make(function () {
            $value = 0;

            while (true) {
                yield $value;
                $value++;
            }
        });

        $result = $collection->take(5);

        $this->assertEqualsCanonicalizing([0, 1, 2, 3, 4], $result->all());
    }
}
