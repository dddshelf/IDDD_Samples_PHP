<?php

namespace SaasOvation\Common\Domain\Model;

interface Comparable
{
    /**
     * Compares this object with the specified object for order. Returns a
     * negative integer, zero, or a positive integer as this object is less
     * than, equal to, or greater than the specified object.
     *
     * @param mixed $anObject
     *
     * @return int
     */
    public function compareTo($anObject);
}