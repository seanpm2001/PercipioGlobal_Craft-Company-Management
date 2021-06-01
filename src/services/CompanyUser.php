<?php

namespace percipiolondon\companymanagement\services;

use craft\db\Query;
use craft\db\Table;
use craft\helpers\Db;
use yii\base\Component;
use percipiolondon\companymanagement\records\CompanyUser as CompanyUserRecord;

class CompanyUser extends Component
{
    public function saveCompanyUser($fields,$user)
    {
        $record = new CompanyUserRecord();
        $record->userId = $user->id;
        $record->birthday = $fields->contactBirthday;
        $record->nationalInsuranceNumber = $fields->contactRegistrationNumber;

        return $record->save(false);;
    }

    public function findCompanyUser($nationalInsuranceNumber)
    {
        return (new Query())
            ->select(['*'])
            ->from(['{{%companymanagement_users}}'])
            ->where(Db::parseParam('nationalInsuranceNumber', $nationalInsuranceNumber))
            ->column();
    }
}