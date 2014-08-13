<?php

namespace SaasOvation\Common\Notification;

use BadMethodCallException;
use Closure;
use Doctrine\Common\Collections\Collection;
use Iterator;
use OutOfBoundsException;
use SaasOvation\Common\Media\AbstractJSONMediaReader;
use SaasOvation\Common\Media\Link;
use SaasOvation\Common\Media\RepresentationReader;

class NotificationLogReader
    extends AbstractJSONMediaReader
    implements Collection
{
    /**
     * @var array
     */
    private $array;

    public static function fromString($aJSONRepresentation)
    {
        $instance = parent::fromString($aJSONRepresentation);

        $instance->array = $instance->arrayValue('notifications');

        if (null === $instance->array) {
            $instance->array = [];
        }

        return $instance;
    }

    public function isArchived()
    {
        return $this->booleanValue('archived');
    }

    public function id()
    {
        return $this->stringValue('notification_log_id');
    }

    public function notifications()
    {
        return $this->getIterator();
    }

    public function hasNext()
    {
        return null !== $this->next();
    }

    public function next()
    {
        return $this->linkNamed('next');
    }

    public function hasPrevious()
    {
        return null !== $this->previous();
    }

    public function previous()
    {
        return $this->linkNamed('previous');
    }

    public function hasSelf()
    {
        return null !== $this->self();
    }

    public function self()
    {
        return $this->linkNamed('self');
    }

    ///////////////////////////////////////////////
    // Iterable and Collection implementations
    ///////////////////////////////////////////////

    public function getIterator()
    {
        return new NotificationReaderIterator($this);
    }

    public function count()
    {
        return count($this->array);
    }

    public function isEmpty()
    {
        return $this->count() > 0;
    }

    public function contains($o)
    {
        throw new BadMethodCallException('Cannot ask contains.');
    }

    public function toArray()
    {
        $readers = [];

        foreach ($this as $reader) {
            $readers[] = $reader;
        }

        return $readers;
    }

    public function add($element)
    {
        throw new BadMethodCallException('Cannot add.');
    }

    public function remove($o)
    {
        throw new BadMethodCallException('Cannot remove.');
    }

    public function clear()
    {
        throw new BadMethodCallException('Cannot clear.');
    }

    public function get($index)
    {
        if (!isset($this->array[$index])) {
            return;
        }

        return NotificationReader::fromString($this->array[$index]);
    }

    public function set($index, $element)
    {
        throw new BadMethodCallException('Cannot set.');
    }

    public function indexOf($o)
    {
        throw new BadMethodCallException('Cannot ask indexOf.');
    }

    private function linkNamed($aLinkName)
    {
        $link = null;

        $linkElement = $this->navigateTo($this->representation(), "_links.$aLinkName");

        if ($linkElement) {
            $rep = RepresentationReader::fromString($linkElement);

            $link = new Link(
                $rep->stringValue('href'),
                $rep->stringValue('rel'),
                $rep->stringValue('title'),
                $rep->stringValue('type')
            );
        }

        return $link;
    }

    /**
     * Removes the specified element from the collection, if it is found.
     *
     * @param mixed $element The element to remove.
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    function removeElement($element)
    {
        throw new BadMethodCallException('Cannot remove.');
    }

    /**
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param string|integer $key The key/index to check for.
     *
     * @return boolean TRUE if the collection contains an element with the specified key/index,
     *                 FALSE otherwise.
     */
    function containsKey($key)
    {
        throw new BadMethodCallException('Cannot ask contains.');
    }

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array The keys/indices of the collection, in the order of the corresponding
     *               elements in the collection.
     */
    function getKeys()
    {
        throw new BadMethodCallException('Cannot ask keys.');
    }

    /**
     * Gets all values of the collection.
     *
     * @return array The values of all elements in the collection, in the order they
     *               appear in the collection.
     */
    function getValues()
    {
        throw new BadMethodCallException('Cannot ask values.');
    }

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     */
    function first()
    {
        throw new BadMethodCallException();
    }

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     */
    function last()
    {
        throw new BadMethodCallException();
    }

    /**
     * Gets the key/index of the element at the current iterator position.
     *
     * @return int|string
     */
    function key()
    {
        throw new BadMethodCallException();
    }

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return mixed
     */
    function current()
    {
        throw new BadMethodCallException();
    }

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param Closure $p The predicate.
     *
     * @return boolean TRUE if the predicate is TRUE for at least one element, FALSE otherwise.
     */
    function exists(Closure $p)
    {
        throw new BadMethodCallException();
    }

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @param Closure $p The predicate used for filtering.
     *
     * @return Collection A collection with the results of the filter operation.
     */
    function filter(Closure $p)
    {
        throw new BadMethodCallException();
    }

    /**
     * Tests whether the given predicate p holds for all elements of this collection.
     *
     * @param Closure $p The predicate.
     *
     * @return boolean TRUE, if the predicate yields TRUE for all elements, FALSE otherwise.
     */
    function forAll(Closure $p)
    {
        throw new BadMethodCallException();
    }

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param Closure $func
     *
     * @return Collection
     */
    function map(Closure $func)
    {
        throw new BadMethodCallException();
    }

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param Closure $p The predicate on which to partition.
     *
     * @return array An array with two elements. The first element contains the collection
     *               of elements where the predicate returned TRUE, the second element
     *               contains the collection of elements where the predicate returned FALSE.
     */
    function partition(Closure $p)
    {
        throw new BadMethodCallException();
    }

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     *
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Keys have to be preserved by this method. Calling this method will only return the
     * selected slice and NOT change the elements contained in the collection slice is called on.
     *
     * @param int $offset The offset to start from.
     * @param int|null $length The maximum number of elements to return, or null for no limit.
     *
     * @return array
     */
    function slice($offset, $length = null)
    {
        throw new BadMethodCallException();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return true;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        throw new BadMethodCallException();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        throw new BadMethodCallException();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        throw new BadMethodCallException();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException();
    }
}

class NotificationReaderIterator implements Iterator
{
    /**
     * @var NotificationLogReader
     */
    private $notificationLogReader;

    /**
     * @var int
     */
    private $index;

    public function __construct(NotificationLogReader $notificationLogReader)
    {
        $this->notificationLogReader = $notificationLogReader;
    }

    public function hasNext()
    {
        return $this->nextIndex() < $this->notificationLogReader->count();
    }

    public function next()
    {
        if (!$this->hasNext()) {
            throw new OutOfBoundsException('No such next element.');
        }

        return $this->notificationLogReader->get($this->index++);
    }

    public function remove()
    {
        throw new BadMethodCallException('Cannot remove.');
    }

    public function hasPrevious()
    {
        return $this->previousIndex() >= 0;
    }

    public function previous()
    {
        if (!$this->hasPrevious()) {
            throw new OutOfBoundsException('No such previous element.');
        }

        $reader = $this->notificationLogReader->get(--$this->index);

        return $reader;
    }

    public function nextIndex()
    {
        return $this->index;
    }

    public function previousIndex()
    {
        return $this->index - 1;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->notificationLogReader->get($this->index);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return null !== $this->current();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->index = 0;
    }
}
