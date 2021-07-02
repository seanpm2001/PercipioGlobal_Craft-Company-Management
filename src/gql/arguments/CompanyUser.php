<?php

namespace percipiolondon\companymanagement\gql\arguments;

use craft\gql\base\ElementArguments;
use craft\gql\types\DateTime;
use GraphQL\Type\Definition\Type;

class CompanyUser extends ElementArguments
{
    /**pm
     * @inheritdoc
     */
    public static function getArguments(): array
    {
        return array_merge(parent::getArguments(), self::getContentArguments());
    }

    /**
     * @inheritdoc
     */

    public static function getContentArguments(): array
    {
        return parent::getContentArguments();
    }

}
