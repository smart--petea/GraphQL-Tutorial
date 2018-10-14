<?php
namespace App\DataFixtures\MongoDB;

use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Document\Book;
use App\Document\Author;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $booksData = [
            1 => [
                "name" => "Name of the Wing",
                "genre" => "Fantasy",
                "id" => "1",
                'authorId' => 1
            ],
            2 => [
                "name" => "The Final Empire",
                "genre" => "Fantasy",
                "id" => "2",
                'authorId' => 2
            ],
            3 => [
                "name" => "The Long Earth",
                "genre" => "Sci-Fi",
                "id" => "3",
                'authorId' => 3
            ],
        ];

        $authorsData = [
            1 => [
                "name"=> "Patrick Rothfuss",
                "age" => 42,
                "id" => "1"
            ],
            2 => [
                "name"=> "Brandon Sanderson",
                "age" => 42,
                "id" => "2"
            ],
            3 => [
                "name"=> "Terry Pratchett",
                "age" => 66,
                "id" => "3"
            ],
        ];

        foreach($booksData as $bookData)
        {

            $authorData = $authorsData[$bookData['authorId']];
            $author = new Author();
            $author->setName($authorData['name']);
            $author->setAge($authorData['age']);
            $manager->persist($author);

            $book = new Book();
            $book->setName($bookData['name']);
            $book->setGenre($bookData['genre']);
            $book->setAuthorId($author->getId());
            $manager->persist($book);
        }

        $manager->flush();
    }
}
