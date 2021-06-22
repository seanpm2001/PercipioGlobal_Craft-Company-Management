<?php
namespace percipiolondon\companymanagement\behaviors;

use Craft;
use percipiolondon\companymanagement\elements\Company;
use percipiolondon\companymanagement\elements\db\CompanyQuery;
use yii\base\Behavior;

/**
 * Adds a `craft.products()` function to the templates (like `craft.entries()`)
 */
class CraftVariableBehavior extends Behavior
{
    public function companies($criteria = null): CompanyQuery
    {
        $query = Company::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }
}