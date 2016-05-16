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
     * @var Node representing the head of the list
     */
    private $head;

    /**
     * @var Node representing the tail of the list
     */
    private $tail;

    /**
     * @var int the max number of elements the cache supports
     */
    private $maxCapacity;

    /**
     * @var int the current number of elements
     */
    private $currentCapacity = 0;

    /**
     * @var Node[] representing a naive hashmap (TODO needs to pass the key through a hash function)
     */
    private $hashmap = [ ];

    /**
     * @param int $maxCapacity the max number of elements the cache allows
     */
    public function __construct ( $maxCapacity ) {

        $this->maxCapacity = $maxCapacity;
        $this->head = new Node( null, null );
        $this->tail = new Node( null, null );

        $this->head->setNext ( $this->tail );
        $this->tail->setPrevious ( $this->head );
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function exists ( $key ) {

        return isset( $this->hashmap[$key] );
    }

    /**
     * Get an element with the given key
     *
     * @param string $key the key of the element to be retrieved
     *
     * @return mixed the content of the element to be retrieved
     */
    public function get ( $key ) {

        if ( !$this->exists( $key ) ) {
            return null;
        }

        $node = $this->hashmap[$key];
        if ( count ( $this->hashmap ) == 1 ) {
            return $node->getData ();
        }

        // refresh the access
        $this->detach ( $node );
        $this->attach ( $this->head, $node );

        return $node->getData ();
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

        if ( $this->maxCapacity <= 0 ) {
            return false;
        }
        if ( isset( $this->hashmap[$key] ) && !empty( $this->hashmap[$key] ) ) {
            $node = $this->hashmap[$key];
            // update data
            $this->detach ( $node );
            $this->attach ( $this->head, $node );
            $node->setData ( $data );
        } else {
            $node = new Node( $key, $data );
            $this->hashmap[$key] = $node;
            ++$this->currentCapacity;
            $this->attach ( $this->head, $node );

            // check if cache is full
            if ( $this->currentCapacity > $this->maxCapacity ) {
                // we're full, remove the tail
                $this->remove ( $this->tail->getPrevious ()->getKey () );
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

        if ( !isset( $this->hashmap[$key] ) ) {
            return false;
        }
        $nodeToRemove = $this->hashmap[$key];
        $this->detach ( $nodeToRemove );
        unset( $this->hashmap[$nodeToRemove->getKey ()] );
        --$this->currentCapacity;

        return true;
    }

    /**
     * @param $callback
     */
    public function each ( $callback ) {
        array_walk($this->hashmap, function($node) use ($callback) {
            $callback($node);
        });
    }

    /**
     * Adds a node to the head of the list
     *
     * @param Node $head the node object that represents the head of the list
     * @param Node $node the node to move to the head of the list
     */
    private function attach ( Node $head, Node $node ) {

        $node->setPrevious ( $head );
        $node->setNext ( $head->getNext () );
        $node->getNext ()->setPrevious ( $node );
        $node->getPrevious ()->setNext ( $node );
    }

    /**
     * Removes a node from the list
     *
     * @param Node $node the node to remove from the list
     */
    private function detach ( Node $node ) {

        $node->getPrevious ()->setNext ( $node->getNext () );
        $node->getNext ()->setPrevious ( $node->getPrevious () );
    }

}
