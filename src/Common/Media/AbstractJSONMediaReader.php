<?php

namespace SaasOvation\Common\Media;

use DateTime;
use InvalidArgumentException;
use stdClass;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractJSONMediaReader
{
    /**
     * @var stdClass
     */
    private $representation;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * @param $aJSONRepresentation
     * @return $this
     */
    public static function fromString($aJSONRepresentation)
    {
        $instance = new static();

        $instance->initialize($aJSONRepresentation);

        return $instance;
    }

    public function arrayValue()
    {
        $args = array_merge(
            [
                $this->representation()
            ],
            func_get_args()
        );

        return call_user_func_array([$this, 'navigateTo'], $args);
    }

    public function booleanValue()
    {
        $stringValue = call_user_func_array([$this, 'stringValue'], func_get_args());

        return $stringValue === null ? null : boolval($stringValue);
    }

    public function dateValue()
    {
        $stringValue = call_user_func_array([$this, 'stringValue'], func_get_args());

        return $stringValue === null ? null : new DateTime($stringValue);
    }

    public function doubleValue()
    {
        $stringValue = call_user_func_array([$this, 'stringValue'], func_get_args());

        return $stringValue === null ? null : doubleval($stringValue);
    }

    public function floatValue()
    {
        $stringValue = call_user_func_array([$this, 'stringValue'], func_get_args());

        return null === $stringValue ? null : floatval($stringValue);
    }

    public function integerValue()
    {
        $stringValue = call_user_func_array([$this, 'stringValue'], func_get_args());

        return null === $stringValue ? null : intval($stringValue);
    }

    public function longValue()
    {
        $stringValue = call_user_func_array([$this, 'stringValue'], func_get_args());

        return $stringValue === null ? null : doubleval($stringValue);
    }

    public function stringValue()
    {
        $args = [
            $this->representation()
        ];

        foreach (func_get_args() as $arg) {
            $args[] = $arg;
        }

        return call_user_func_array([$this, 'getStringValue'], $args);
    }

    protected function navigateTo($aStartingJsonObject)
    {
        $aKeys = array_slice(func_get_args(), 1);

        if (0 === count($aKeys)) {
            throw new InvalidArgumentException('Must specify one or more keys.');
        } elseif (count($aKeys) === 1 && ('/' === $aKeys[0]{0} || false !== strpos($aKeys[0], '.'))) {
            $aKeys = $this->parsePath($aKeys[0]);
        }

        $propertyPath = implode('.', $aKeys);

        $result = null;

        try {
            $result = $this->accessor->getValue(
                $aStartingJsonObject,
                $propertyPath
            );
        } catch (NoSuchPropertyException $e) {
            // Do nothing, just return null
        }

        return $result;
    }

    protected function representation()
    {
        return $this->representation;
    }

    protected function getStringValue()
    {
        return call_user_func_array([$this, 'navigateTo'], func_get_args());
    }

    private function initialize($aJSONRepresentation)
    {
        $propertyAccessorBuilder = PropertyAccess::createPropertyAccessorBuilder();
        $accessor = $propertyAccessorBuilder
            ->disableExceptionOnInvalidIndex()
            ->getPropertyAccessor()
        ;

        $this->setAccessor($accessor);
        $this->setRepresentation(!is_object($aJSONRepresentation)? json_decode($aJSONRepresentation) : $aJSONRepresentation);
    }

    private function setRepresentation($aRepresentation)
    {
        $this->representation = $aRepresentation;
    }

    private function parsePath($aPropertiesPath)
    {
        $startsWithSlash = '/' === $aPropertiesPath{0};

        $propertyNames = null;

        if ($startsWithSlash) {
            $propertyNames = explode('/', substr($aPropertiesPath, 1));
        } else {
            $propertyNames = preg_split('#\.#', $aPropertiesPath);
        }

        return $propertyNames;
    }

    private function setAccessor(PropertyAccessor $aPropertyAccessor)
    {
        $this->accessor = $aPropertyAccessor;
    }
}
