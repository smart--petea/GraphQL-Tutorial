<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use \GraphQL\Type\Definition\ObjectType;
use \GraphQL\Type\Definition\Type;
use \GraphQL\Type\Schema;
use \GraphQL\GraphQL;

use App\Document\Book;
use App\Document\Author;

class DefaultController extends AbstractController
{
    /**
     * @Route("/graphql", name="graphql")
     */
    public function index(Request $request)
    {

        try {
            $query = $this->getGraphQLQuery($request);
        } catch (\Exception $ex) {
            return new JsonResponse(array(
                'status' => 'error',
                'message' => $ex->getMessage()
            ));
        }

        $mongoManager = $this->get('doctrine_mongodb')->getManager();

        $bookType = null; //be carefull with this statement

        $authorType = new ObjectType([
            'name' => 'Author',
            'fields' => function () use (&$bookType, $mongoManager) {
                return [
                    'id' => Type::id(),
                    'name' => Type::string(),
                    'age' => Type::int(),
                    'books' => array(
                        'type' => Type::listOf($bookType),
                        'resolve' => function($root, $args) use ($mongoManager) {

                            $booksMongo = $mongoManager->getRepository(Book::class)->findBy(array('authorId' => $root['id']));
                            if(empty($booksMongo)) {
                                return array();
                            }

                            $books = array();
                            foreach($booksMongo as $bookMongo) {
                                $books[] = $bookMongo->toArray();
                            }

                            return $books;
                        }
                    ),
                ];
            }
        ]);

        $bookType = new ObjectType([
            'name' => 'Book',
            'fields' => [
                'id' => Type::id(),
                'name' => Type::string(),
                'genre' => Type::string(),
                'author' => [
                    'type' => $authorType,
                    'resolve' => function($root, $args) use($mongoManager) {
                        $author = $mongoManager->getRepository(Author::class)->find($root['authorId']);
                        return empty($author) ? null : $author->toArray();
                    }
                ]
            ]
        ]);

        $rootQuery = new ObjectType([
            'name' => 'RootQueryType',
            'fields' => [
                'book' => [
                        'type' => $bookType,
                        'args' => [
                            'id' => Type::id()
                        ],
                        'resolve' => function($parent, $args) use ($mongoManager) {
                            $book = $mongoManager->getRepository(Book::class)->find($args['id']);

                            return empty($book) ? $book : $book->toArray();
                        }
                    ],
                'author' => [
                    'type' => $authorType,
                    'args' => [
                        'id' => Type::id()
                    ],
                    'resolve' => function($parent, $args) use ($mongoManager) {

                        $author = $mongoManager->getRepository(Author::class)->find($args['id']);
                        if(empty($author))
                        {
                            return null;
                        }

                        return $author->toArray();
                    }
                ],
                'books' => [
                    'type' => Type::listOf($bookType),
                    'resolve' => function($root, $args) use ($mongoManager) {
                        $booksMongo = $mongoManager->getRepository(Book::class)->findAll();
                        if(empty($booksMongo))
                        {
                            return array();
                        }

                        $books = array();
                        foreach($booksMongo as $bookMongo)
                        {
                            $books[] = $bookMongo->toArray();
                        }

                        return $books;
                    }
                ],
                'authors' => [
                    'type' => Type::listOf($authorType),
                    'resolve' => function($root, $args) use ($mongoManager) {
                        $authorsMongo = $mongoManager->getRepository(Author::class)->findAll();
                        if(empty($authorsMongo))
                        {
                            return array();
                        }

                        $authors = array();
                        foreach($authorsMongo as $authorMongo)
                        {
                            $authors[] = $authorMongo->toArray();
                        }

                        return $authors;
                    }
                ],
            ]
        ]);

        $schema = new Schema([
            'query' => $rootQuery
        ]);

        $result = GraphQL::executeQuery($schema, $query, null, null, null);

        return new JsonResponse(
            $result->toArray()
        );
    }

    private function getGraphQLQuery(Request $request) {
        $content = $request->getContent();
        $content = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', ' ', $content);
        $jsonInput = json_decode($content, true);
        if(is_null($jsonInput)) {
            throw new \Exception(json_last_error_msg());
        }

        return $jsonInput['query'];
    }
}
