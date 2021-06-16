<?php

namespace percipiolondon\companymanagement\gql\arguments\elements;

use percipiolondon\companymanagement\elements\Company as CompanyElement;
use percipiolondon\companymanagement\Plugin;

use Craft;
use craft\gql\base\ElementArguments;
use craft\gql\types\QueryArgument;
use GraphQL\Type\Definition\Type;

/**
 * Class Company.php
 *
 * @author Percipio Global Ltd. <support@percipio.london>
 * @since 1.0.0
 */
class Company extends ElementArguments
{
    /**
     * @inheritdoc
     */
    public static function getArguments(): array
    {
        return array_merge(parent::getArguments(), self::getContentArguments(), [
            'type' => [
                'name' => 'type',
                'type' => Type::listOf(Type::string()),
                'description' => 'Narrows the query results based on the company type the companies belong to per the company type\'s handle'
            ],
            'typeId' => [
                'name' => 'typeId',
                'type' => Type::listOf(QueryArgument::getType()),
                'description' => 'Narrow the query results based on the company types the companies belong to, per company type IDs.'
            ]
        ]);
    }

    /**
     * @inheritdoc
     */

    public static function getContentArguments(): array
    {
        $companyTypeFieldArguments = Craft::$app->getGql()->getContentArguments(CompanyManagement::$plugin->companyTypes->getComapnyTypes()->getAllCompanyTypes(), CompanyElement::class);

        return array_merge(parent::getContentArguments(), $companyTypeFieldArguments);
    }
}