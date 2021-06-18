<?php

namespace percipiolondon\companymanagement\gql\interfaces\elements;

use percipiolondon\companymanagement\elements\Company as CompanyElement;
use percipiolondon\companymanagement\gql\types\generators\CompanyType;

use craft\gql\GqlEntityRegistry;
use craft\gql\interfaces\Element;
use craft\gql\TypeManager;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

/**
 * Class Company
 *
 * @author Percipio Global Ltd. <support@percipio.london>
 * @since 1.0.0
 */
class Company extends Element {

    /**
     * @inheritdoc
     */
    public static function getTypeGenerator(): string
    {
        return CompanyType::class;
    }

    /**
     * @inheritdoc
     */
    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::getName(), new InterfaceType([
            'name' => static::getName(),
            'fields' => [
                'name' => [
                    'name' => 'name',
                    'type' => Type::string(),
                    'description' => 'The company’s name',
                ],
                'info' => [
                    'name' => 'info',
                    'type' => Type::string(),
                    'description' => 'The company’s info',
                ]
            ],
            'description' => 'This is the interface implemented by all companies.',
            'resolveType' => function(CompanyElement $value) {
                return $value->getGqlTypeName();
            }
        ]));

        CompanyType::generateTypes();

        return $type;
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'CompanyInterface';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {

        return TypeManager::prepareFieldDefinitions(array_merge(parent::getFieldDefinitions(), [
                'name' => [
                    'name' => 'name',
                    'type' => Type::string(),
                    'description' => 'The company’s name',
                ],
                'info' => [
                    'name' => 'info',
                    'type' => Type::string(),
                    'description' => 'The company’s info',
                ]
        ]), self::getName());

    }

}