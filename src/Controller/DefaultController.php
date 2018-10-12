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
        try {
            $query = $this->getGraphQLQuery($request);
        } catch (\Exception $ex) {
            return new JsonResponse(array(
                'status' => 'error',
                'message' => $ex->getMessage()
            ));
        }

        $bookType = new ObjectType([
            'name' => 'Book',
            'fields' => [
                'id' => Type::string(),
                'name' => Type::string(),
                'genre' => Type::string()
            ]
        ]);

        $rootQuery = new ObjectType([
            'name' => 'RootQueryType',
            'fields' => [
                'book' => [
                    'type' => $bookType,
                    'args' => [
                        'id' => Type::string()
                    ],
                    'resolve' => function($parent, $args) {
                        return array(
                            'id' => '1',
                            'name' => 'Petr pervii',
                            'genre' => 'Imperator'
                        );
                    }
                ]
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
