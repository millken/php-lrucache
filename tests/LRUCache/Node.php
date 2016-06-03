<?php

use LRUCache\Node;

class NodeTest extends PHPUnit_Framework_TestCase {

    public function test_set_data () {

        $node = new Node();

        $node->set_data(12345);

        $this->assertEquals(12345, $node->get_data(), "set_data() should set the node's data");
    }
}
