<?php

namespace LRUCache;

/**
 * Class that implements the concept of an LRU Cache
 * using an associative array as a naive hashmap, and a doubly linked list
 * to control the access and insertion order.
 * @author  Rogério Vicente
 * @license MIT (see the LICENSE file for details)
 */
class LRUCache {

    /**
     * @var int the current number of elements
     */
    private $currentCapacity = 0;

    /**
     * @var Node[] representing a naive hashmap
     */
    private $hashmap = [ ];

    /**
     * @var Node representing the head of the list
     */
    private $head;

    /**
     * @var int the max number of elements the cache supports
     */
    private $maxCapacity;

    /**
     * @var Node representing the tail of the list
     */
    private $tail;

    /**
     * @param int $maxCapacity the max number of elements the cache allows
     */
    public function __construct ( $maxCapacity ) {

        $this->maxCapacity = $maxCapacity;
        $this->head = new Node( null, null );
        $this->tail = new Node( null, null );

        $this->head->set_next( $this->tail );
        $this->tail->set_previous( $this->head );
    }

    /**
     * Performs an out of order walk of the cache
     *
     * @param $callback
     */
    public function each ( $callback ) {

        array_walk( $this->hashmap, function ( $node ) use ( $callback ) {
            $callback( $node->get_data() );
        } );
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function exists ( $key ) {

        return isset($this->hashmap[$key]);
    }

    /**
     * Get an element with the given key
     *
     * @param string $key the key of the element to be retrieved
     *
     * @return mixed the content of the element to be retrieved
     */
    public function get ( $key ) {

        // can't get something that isn't there
        if( !$this->exists( $key ) ) {
            return null;
        }

        // the node we are returning
        $node = $this->hashmap[$key];

        // if this is the only node, just return the data
        if( $this->currentCapacity === 1 ) {

            return $node->get_data();
        }

        // put node at head
        $this->detach( $node );
        $this->attach( $this->head, $node );

        return $node->get_data();
    }

    /**
     * Inserts a new element into the cache
     *
     * @param string $key  the key of the new element
     * @param string $data the content of the new element
     *
     * @return boolean true on success, false if cache has zero capacity
     */
    public function put ( $key, $data ) {

        // can't put to a cache with no size
        if( $this->maxCapacity <= 0 ) {
            return false;
        }

        // does this key exist?
        if( $this->exists( $key ) ) {

            // get the saved node
            $node = $this->hashmap[$key];

            // put node at head
            $this->detach( $node );
            $this->attach( $this->head, $node );

            // update data
            $node->set_data( $data );

        } else {

            // create a new node
            $node = new Node( $key, $data );

            // save the node
            $this->hashmap[$key] = $node;

            // increment capacity
            ++$this->currentCapacity;

            // put node at head
            $this->attach( $this->head, $node );

            // check if cache is full
            if( $this->currentCapacity > $this->maxCapacity ) {
                // we're full, remove the tail
                $this->remove( $this->tail->get_previous()->get_key() );
            }
        }

        return true;
    }

    /**
     * Removes a key from the cache
     *
     * @param string $key key to remove
     *
     * @return bool true if removed, false if not found
     */
    public function remove ( $key ) {

        // can't remove something that isn't there
        if( !$this->exists( $key ) ) {
            return false;
        }

        // get the node to remove
        $nodeToRemove = $this->hashmap[$key];

        // remove the node from the list
        $this->detach( $nodeToRemove );

        // remove node from hash
        unset($this->hashmap[$nodeToRemove->get_key()]);

        // decrement capacity
        --$this->currentCapacity;

        return true;
    }

    /**
     * Adds a node to the head of the list
     *
     * @param Node $head the node object that represents the head of the list
     * @param Node $node the node to move to the head of the list
     */
    private function attach ( Node $head, Node $node ) {

        // the soon to be second node in our list, currently the first one
        $secondNode = $head->get_next();

        // tell this node it's right after the head
        $node->set_previous( $head );

        // tell this node what the second node is
        $node->set_next( $secondNode );

        // tell the second node that this node comes before it now
        $secondNode->set_previous( $node );

        // tell head we are the next node
        $head->set_next( $node );
    }

    /**
     * Removes a node from the list
     *
     * @param Node $node the node to remove from the list
     */
    private function detach ( Node $node ) {

        // point previous node to next node
        $node->get_previous()->set_next( $node->get_next() );

        // point next node to previous node
        $node->get_next()->set_previous( $node->get_previous() );

        // $node is now dangling
    }

}
