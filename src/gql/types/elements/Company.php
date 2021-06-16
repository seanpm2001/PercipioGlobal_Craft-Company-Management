<?php

namespace percipiolondon\companymanagement\gql\types\elements;

use percipiolondon\companymanagement\elements\Company as CompanyElement;
use percipiolondon\companymanagement\gql\interfaces\elements\Company as CompanyInterface;
use percipiolondon\companymanagement\gql\types\elements\Company as CompanyTypeElement;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class CompanyType
 *
 * @author Percipio Global Ltd. <support@percipio.london>
 * @since 1.0.0
 */

class Company extends ElementType
{
    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $config['interfaces'] = [
            CompanyInterface::getType(),
        ];

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        /**
    }
}