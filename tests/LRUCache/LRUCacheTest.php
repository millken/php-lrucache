<?php

use LRUCache\LRUCache;

class LRUCacheTest extends PHPUnit_Framework_TestCase {

    public function test_put () {

        $this->assertFalse((new LRUCache(0))->put(1, 12345), "putting to zero sized cache should fail");
        $this->assertFalse((new LRUCache(-1))->put(1, 12345), "putting to negative sized cache should fail");
    }

    public function test_iterator ( ) {

        $cache = new LRUCache(10);
        $cache->put(1, 12345);
        $cache->put(2, 23456);
        $cache->put(3, 34567);
        $cache->put(2, 23457);

        $expectedOrder = [
            2 => 23457,
            3 => 34567,
            1 => 12345,
        ];

        $actualOrder = [];

        foreach ( $cache as $key => $value ) {
            $actualOrder[$key] = $value;
        }

        $this->assertEquals($expectedOrder, $actualOrder, "iterator should iterate in lru order");
    }
}
