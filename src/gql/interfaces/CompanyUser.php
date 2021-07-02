<?php

namespace percipiolondon\companymanagement\gql\interfaces;

use craft\gql\GqlEntityRegistry;
use craft\gql\TypeManager;
use craft\gql\interfaces\Element;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;
use percipiolondon\companymanagement\gql\types\generators\CompanyUserType;

class CompanyUser extends Element
{
    public static function getTypeGenerator(): string
    {
        return CompanyUserType::class;
    }

    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::getName(), new InterfaceType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by the user.',
        ]));

        CompanyUserType::generateTypes();

        return $type;
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'UserCompanyInterface';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {
        return TypeManager::prepareFieldDefinitions(array_merge(parent::getFieldDefinitions(), [
            'nationalInsuranceNumber' => [
                'name' => 'name',
                'type' => Type::string(),
                'description' => 'The user’s national insurance number',
            ],
        ]), self::getName());
    }
}
