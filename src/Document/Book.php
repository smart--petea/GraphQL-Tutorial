<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
* @MongoDB\Document
*/
class Book {
    /**
     * @MongoDB\Id
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    /**
    * @MongoDB\Field(type="string")
    */
    protected $name;

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @MongoDB\Field(type="string")
     */
    protected $genre;

    public function getGenre() : string
    {
        return $this->genre;
    }

    public function setGenre(string $genre)
    {
        $this->genre = $genre;
    }

    /**
     * @MongoDB\Field(type="id")
     */
    protected $authorId;

    public function getAuthorId()
    {
        return $this->$authorId;
    }

    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;
    }
}
