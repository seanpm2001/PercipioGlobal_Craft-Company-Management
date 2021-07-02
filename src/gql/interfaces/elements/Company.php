<?php

namespace percipiolondon\companymanagement\gql\interfaces\elements;

use craft\elements\User as UserElement;
use craft\gql\base\ObjectType;
use craft\gql\interfaces\elements\User as UserInterface;
use craft\gql\types\DateTime;
use craft\gql\types\elements\User;
use craft\gql\types\generators\UserType;
use craft\gql\types\Query;
use craft\gql\types\TableRow;
use craft\helpers\ArrayHelper;
use GraphQL\Type\Definition\ResolveInfo;
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
            'fields' => self::class . '::getFieldDefinitions',
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
                ],
                'address' => [
                    'name' => 'address',
                    'type' => Type::string(),
                    'description' => 'The company’s address',
                ],
                'postcode' => [
                    'name' => 'postcode',
                    'type' => Type::string(),
                    'description' => 'The company’s postcode',
                ],
                'town' => [
                    'name' => 'town',
                    'type' => Type::string(),
                    'description' => 'The company’s town',
                ],
                'website' => [
                    'name' => 'website',
                    'type' => Type::string(),
                    'description' => 'The company’s website',
                ],
                'userId' => [
                    'name' => 'userId',
                    'type' => Type::int(),
                    'description' => 'The company’s user admin id',
                ],
                'companyAdmin' => [
                    'name' => 'companyAdmin',
                    'type' => UserType::generateType(\Craft::$app->getFields()->getLayoutByType(UserElement::class)),
                    'resolve' => function ($source, array $arguments, $context, ResolveInfo $resolveInfo) {

                        $user = \craft\elements\User::find()
                            ->id($source['userId'])
                            ->one();

                        return $user;
                    }
                ]
        ]), self::getName());

    }

}
