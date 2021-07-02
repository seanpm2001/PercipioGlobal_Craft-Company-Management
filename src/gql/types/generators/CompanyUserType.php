<?php

namespace percipiolondon\companymanagement\gql\types\generators;

use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CompanyUserType implements GeneratorInterface
{
    public static function generateTypes($context = null): array
    {
        $gqlTypes = [];

        /*// Generate a type for each company type
        $gqlTypes[$typeName] = GqlEntityRegistry::getEntity() ?: GqlEntityRegistry::createEntity($typeName, new ObjectType([
            'name' => $typeName,
            'fields' => [
                'test' => [
                    'name' => 'test',
                    'type' => Type::string(),
                    'description' => 'this is just a test'
                ]
            ]
        ]));*/

        return $gqlTypes;
    }
}
