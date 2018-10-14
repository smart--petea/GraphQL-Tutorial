<?php
namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
* @MongoDB\Document
*/
class Author
{
    /**
     * @MongoDB\Id
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @MongoDB\Field(type="string")
     */
    private $name;

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @MongoDB\Field(type="int")
     */
    private $age;

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAge(int $age)
    {
        $this->age = $age;
    }
}
