<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use \GraphQL\Type\Definition\ObjectType;
use \GraphQL\Type\Definition\Type;
use \GraphQL\Type\Schema;
use \GraphQL\GraphQL;

class DefaultController extends AbstractController
{
    /**
     * @Route("/graphql", name="graphql")
     */
    public function index(Request $request)
    {
        $books = [
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

        $authors = [
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

        try {
            $query = $this->getGraphQLQuery($request);
        } catch (\Exception $ex) {
            return new JsonResponse(array(
                'status' => 'error',
                'message' => $ex->getMessage()
            ));
        }

        $bookType = null; //be carefull with this statement

        $authorType = new ObjectType([
            'name' => 'Author',
            'fields' => function () use (&$bookType, $books) {
                return [
                    'id' => Type::id(),
                    'name' => Type::string(),
                    'age' => Type::int(),
                    'books' => array(
                        'type' => Type::listOf($bookType),
                        'resolve' => function($root, $args) use ($books) {
                            $auths = array();
                            foreach($books as $book) {
                                if($root['id'] == $book['authorId']) {
                                    $auths[] = $book;
                                }
                            }

                            return $auths;
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
                    'resolve' => function($root, $args) use($books, $authors) {
                        return $authors[$root['authorId']];
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
                        'resolve' => function($parent, $args) use ($books) {
                            $id = (int) $args['id'];
                            if(isset($books[$id])) {
                                return $books[$id];
                            }

                            return null;
                        }
                    ],
                'author' => [
                    'type' => $authorType,
                    'args' => [
                        'id' => Type::id()
                    ],
                    'resolve' => function($parent, $args) use ($authors) {
                        $id = (int) $args['id'];
                        if(empty($authors[$id])) {
                            return null;
                        }

                        return $authors[$id];
                    }
                ],
                'books' => [
                    'type' => Type::listOf($bookType),
                    'resolve' => function($root, $args) use ($books) {
                        return $books;
                    }
                ],
                'authors' => [
                    'type' => Type::listOf($authorType),
                    'resolve' => function($root, $args) use ($authors) {
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
