<?php

namespace percipiolondon\companymanagement\gql\resolvers;

use craft\gql\base\ElementResolver;
use jamesedmonston\graphqlauthentication\GraphqlAuthentication;
use percipiolondon\companymanagement\elements\db\EmployeeQuery;
use percipiolondon\companymanagement\helpers\Gql as GqlHelper;

class Employee extends ElementResolver
{
    public static function prepareQuery($source, array $arguments, $fieldName = null)
    {
        // If this is the beginning of a resolver chain, start fresh
        if($source === null) {
            $query = new EmployeeQuery(static::class);
        } else {
            $query = $source->$fieldName;
        }

        // If it's preloaded, it's preloaded.
        if (is_array($query)) {
            return $query;
        }

        foreach ($arguments as $key => $value) {
            if (method_exists($query, $key)) {
                $query->$key($value);
            } elseif (property_exists($query, $key)) {
                $query->$key = $value;
            } else {
                // Catch custom field queries
                $query->$key($value);
            }
        }

        if (!GqlHelper::canQueryEmployees()) {
            return [];
        }

        $restrictionService = GraphqlAuthentication::$restrictionService;

        if ($restrictionService->shouldRestrictRequests()) {

            $user = GraphqlAuthentication::$tokenService->getUserFromToken();
        }

        return $query;
    }
}
