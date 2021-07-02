<?php

namespace percipiolondon\companymanagement\gql\resolvers\elements;

use jamesedmonston\graphqlauthentication\GraphqlAuthentication;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\db\Table;
use percipiolondon\companymanagement\elements\Company as CompanyElement;
use percipiolondon\companymanagement\gql\arguments\elements\Company as CompanyArguments;
use percipiolondon\companymanagement\helpers\Gql as GqlHelper;

use craft\gql\base\ElementResolver;
use craft\helpers\Db;

/**
 * Class Company
 *
 * @author Percipio Global Ltd. <support@percipio.london>
 * @since 1.0.0
 */
class Company extends ElementResolver {

    /**
     * @inheritdoc
     */
    public static function prepareQuery($source, array $arguments, $fieldName = null)
    {
        // If this is the beginning of a resolver chain, start fresh
        if($source === null) {
            $query = CompanyElement::find();
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

        $pairs = GqlHelper::extractAllowedEntitiesFromSchema('read');

        if (!GqlHelper::canQueryCompanies()) {
            return [];
        }

        $restrictionService = GraphqlAuthentication::$restrictionService;

        if ($restrictionService->shouldRestrictRequests()) {

            $user = GraphqlAuthentication::$tokenService->getUserFromToken();

            if(!CompanyManagement::$plugin->userPermissions->applyCanParam("access:company", $user->id, $arguments['id'][0]) ) {
                throw new \yii\web\HttpException(401, 'Unauthorized');
            }

            $query->andWhere(['in', 'companymanagement_companies.typeId', array_values(Db::idsByUids(Table::CM_COMPANYTYPES, $pairs['companyTypes']))]);
            return $query;
        }

        return [];

    }

}
