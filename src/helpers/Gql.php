<?php

namespace percipiolondon\companymanagement\helpers;

use craft\helpers\Gql as GqlHelper;

/**
 * Class CompanyManagement Gql
 *
 * @author Percipio Global Ltd. <support@percipio.london>
 * @since 1.0.0
 */
class Gql extends GqlHelper {

    /**
     * Return true if active schema can query companies.
     *
     * @return bool
     */
    public static function canQueryCompanies(): bool
    {
        $allowedEntities = self::extractAllowedEntitiesFromSchema();
        return isset($allowedEntities['companyTypes']);
    }

    /**
     * Return true if active schema can query companies.
     *
     * @return bool
     */
    public static function canQueryEmployees(): bool
    {
        $allowedEntities = self::extractAllowedEntitiesFromSchema();
        return isset($allowedEntities['employee']);
    }

}
