<?php

declare(strict_types=1);

namespace PHPCollection;

use ArrayAccess;
use Countable;
use Iterator;

class Collection implements Iterator, ArrayAccess, Countable
{
    protected array $container = [];

    /**
     * Iterator
     */
    protected int $position = 0;

    public function __construct(array $values)
    {
        $this->container = $values;
    }

    /**
     * Add element by key or push to collection end
     */
    public function add($value, $index = null): Collection
    {
        if ($index === null) {
            $this->container[] = $value;
        } else {
            $this->container[$index] = $value;
        }
        return $this;
    }

    /**
     * Get element by key
     * @param string|int $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (!isset($this->container[$key])) {
            throw new \Exception('Element not exist');
        }
        return $this->container[$key];
    }

    /**
     * Remove specified elements
     * @param mixed ...$keys
     */
    public function remove(...$keys): Collection
    {
        foreach ($keys as $key) {
            unset($this->container[$key]);
        }
        return $this;
    }

    /**
     * Return collection elements count
     */
    public function count(): int
    {
        return count($this->container);
    }

    /**
     * Clear collection from values
     */
    public function clear(): Collection
    {
        $this->container = [];
        return $this;
    }

    /**
     * Modified collection by specified map
     */
    public function map(callable $callback): Collection
    {
        $result = [];
        foreach ($this->container as $key => $value) {
            $result[$key] = $callback($value);
        }
        return new Collection($result);
    }

    /**
     * Modified collection by specified filter
     */
    public function filter(callable $callback): Collection
    {
        $result = [];
        foreach ($this->container as $key => $value) {
            if ($callback($value)) {
                $result[$key] = $value;
            }
        }
        return new Collection($result);
    }

    /**
     * Fill in $startIndex from the specified position by $count with the $value
     */
    public function fill(int $startIndex, int $count, $value): Collection
    {
        $result = [];
        if ($startIndex < 0) {
            $result[$startIndex] = $value;
            $startIndex = 0;
            --$count;
        }

        for ($i = $startIndex; $i < $startIndex + $count; $i++) {
            $result[$i] = $value;
        }
        $this->container = $result;
        return $this;
    }

    /**
     * Checks whether the key exists
     */
    public function isKeyExist($key): bool
    {
        foreach ($this->container as $containerKey => $containerValue) {
            if ($key === $containerKey) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks whether the value exists
     */
    public function isValueExist($value): bool
    {
        foreach ($this->container as $containerKey => $containerValue) {
            if ($value === $containerValue) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return collection first element
     * @param callable|null $callback
     * @return mixed|null
     */
    public function getFirstElement(callable $callback = null)
    {
        if (is_null($callback)) {
            foreach ($this->container as $item) {
                return $item;
            }
        }

        foreach ($this->container as $key => $value) {
            if ($callback($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Return collection last element
     * @return mixed|null
     */
    public function getLastElement()
    {
        if (count($this->container) === 0) {
            return null;
        }
        return $this->container[array_keys($this->container)[count($this->container)-1]];
    }

    /**
     * Check element exist in collection
     * @return bool
     */
    public function contains($value): bool
    {
        foreach ($this->container as $item) {
            if ($item === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check elements exist in collection and return it
     * @param string $pattern - pattern that the search will follow
     * @param string $item - excepted match
     * @return Collection
     */
    public function match(string $pattern, string $item): Collection
    {
        preg_match_all($pattern, $item, $matches);
        return new Collection($matches[0]);
    }

    /**
     * Sort of collection container by specified order
     * @param int $order, equal 3(desc) or 4(asc)
     */
    public function sort($order = SORT_ASC): Collection
    {
        if ($order !== 3 && $order !== 4) {
            throw new \Exception('Wrong order');
        }

        $result = $this->container;
        switch ($order) {
            case SORT_ASC:
                asort($result);
                break;
            case SORT_DESC:
                arsort($result);
                break;
        }
        return new Collection($result);
    }

    /**
     * If collection container is object -
     * sort by field of this object
     * @param int $order, equal 3(desc) or 4(asc)
     */
    public function sortBy(callable $callback, $order = SORT_ASC): Collection
    {
        $result = array();
        $sortableArray = array();

        if (count($this->container) > 0) {
            foreach ($this->container as $key => $value) {
                if (is_object($value)) {
                    $sortableArray[$key] = $callback($value);
                }
            }
            switch ($order) {
                case SORT_ASC:
                    asort($sortableArray);
                    break;
                case SORT_DESC:
                    arsort($sortableArray);
                    break;
            }

            foreach ($sortableArray as $key => $value) {
                $result[$key] = $this->container[$key];
            }
        }

        return new Collection($result);
    }

    /**
     * If collection container is multidimensional array -
     * sort by field of this array
     * @param int $order, equal 3(desc) or 4(asc)
     */
    public function sortMultiArraysByField($field, $order = SORT_ASC): Collection
    {
        $result = array();
        $sortableArray = array();

        if (count($this->container) > 0) {
            foreach ($this->container as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $innerKey => $innerValue) {
                        if ($innerKey === $field) {
                            $sortableArray[$key] = $innerValue;
                        }
                    }
                } else {
                    $sortableArray[$key] = $value;
                }
            }
            switch ($order) {
                case SORT_ASC:
                    asort($sortableArray);
                    break;
                case SORT_DESC:
                    arsort($sortableArray);
                    break;
            }

            foreach ($sortableArray as $key => $value) {
                $result[$key] = $this->container[$key];
            }
        }

        return new Collection($result);
    }

    /**
     * Convert collection to array
     */
    public function toArray(): array
    {
        return $this->container;
    }

    /**
     * Convert collection to string
     */
    public function __toString(): string
    {
        $result = "{\n";
        foreach ($this->container as $key => $value) {
            $result .= "\t".$key.' => '.$value."\n";
        }
        $result .= "}";
        return $result;
    }

    /**
     * Implement current method from Iterator interface
     * @return mixed
     */
    public function current()
    {
        return $this->container[array_keys($this->container)[$this->position]];
    }

    /**
     * Implement next method from Iterator interface
     */
    public function next()
    {
        $key = array_keys($this->container);
        if (isset($key[++$this->position])) {
            return $this->container[$key[$this->position]];
        }
        return false;
    }

    /**
     * Implement key method from Iterator interface
     */
    public function key(): int
    {
        return array_keys($this->container)[$this->position];
    }

    /**
     * Implement valid method from Iterator interface
     */
    public function valid(): bool
    {
        return isset(array_keys($this->container)[$this->position]);
    }

    /**
     * Implement rewind method from Iterator interface
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Implement offsetExists method from ArrayAccess interface
     */
    public function offsetExists($offset): bool
    {
        return $this->isKeyExist($offset);
    }

    /**
     * Implement offsetGet method from ArrayAccess interface
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Implement offsetSet method from ArrayAccess interface
     */
    public function offsetSet($offset, $value): Collection
    {
        return $this->add($value, $offset);
    }

    /**
     * Implement offsetUnset method from ArrayAccess interface
     */
    public function offsetUnset($offset): Collection
    {
        return $this->remove($offset);
    }
}
