<?php

namespace SaasOvation\Common\Media;

use InvalidArgumentException;

class Link
{
    /**
     * @var string
     */
    private $href;

    /**
     * @var string
     */
    private $rel;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $type;
    
    public function __construct($anHref, $aRel, $aTitle = null, $aType = null)
    {
        $this->setHref($anHref);
        $this->setRelationship($aRel);
        $this->setTitle($aTitle);
        $this->setType($aType);
    }

    public function href()
    {
        return $this->getHref();
    }

    public function getHref()
    {
        return $this->href;
    }

    public function rel()
    {
        return $this->getRel();
    }

    public function getRel()
    {
        return $this->rel;
    }

    public function title()
    {
        return $this->getTitle();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function type()
    {
        return $this->getType();
    }

    public function getType()
    {
        return $this->type;
    }

    public function __toString()
    {
        // see http://www.w3.org/Protocols/9707-link-header.html

        $builder = '';

        $builder .= '<';
        $builder .= $this->getHref();
        $builder .= '>; rel=';
        $builder .= $this->getRel();

        // title is optional
        if (null !== $this->getTitle()) {
            $builder .= '; title=';
            $builder .= $this->getTitle();
        }

        // per LINK extension, type is optionally permitted
        if (null !== $this->getType()) {
            $builder .= '; type=';
            $builder .= $this->getType();
        }

        return $builder;
    }

    private function setHref($anHref)
    {
        if (null === $anHref) {
            throw new InvalidArgumentException('Href must not be null.');
        }

        $this->href = $anHref;
    }

    private function setRelationship($aRel)
    {
        if (null === $aRel) {
            throw new InvalidArgumentException('Rel must not be null.');
        }

        $this->rel = $aRel;
    }

    private function setTitle($aTitle)
    {
        $this->title = $aTitle;
    }

    private function setType($aType)
    {
        $this->type = $aType;
    }
}
