<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;
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
}
