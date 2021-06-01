<?php

namespace percipiolondon\companymanagement\services;

use craft\db\Query;
use craft\db\Table;
use craft\helpers\Db;
use percipiolondon\companymanagement\models\CompanyUsers;
use yii\base\Component;
use percipiolondon\companymanagement\records\CompanyUser as CompanyUserRecord;

class CompanyUser extends Component
{
    public function saveCompanyUser($fields,$user)
    {
        $companyUser = new CompanyUsers();
        $companyUser->userId = $user->id;
        $companyUser->birthday = $fields->contactBirthday;
        $companyUser->nationalInsuranceNumber = $fields->contactRegistrationNumber;

        $record = new CompanyUserRecord();
        $record->userId = $companyUser->userId;
        $record->birthday = $companyUser->birthday;
        $record->nationalInsuranceNumber = $companyUser->nationalInsuranceNumber;

        return $record->save(false);;
    }

    public function findCompanyUser($nationalInsuranceNumber)
    {
        return (new Query())
            ->select(['userId'])
            ->from(['{{%companymanagement_users}}'])
            ->where(Db::parseParam('nationalInsuranceNumber', $nationalInsuranceNumber))
            ->column();
    }
}