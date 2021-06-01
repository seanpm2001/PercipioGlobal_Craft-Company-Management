<?php

namespace percipiolondon\companymanagement\services;

use craft\db\Query;
use craft\helpers\Db;
use percipiolondon\companymanagement\models\CompanyUsers;
use yii\base\Component;
use percipiolondon\companymanagement\records\CompanyUser as CompanyUserRecord;

class CompanyUser extends Component
{
    public function saveCompanyUser($fields,$user)
    {
        $companyUser = new CompanyUsers();
        $companyUser->id = $fields->id;
        $companyUser->userId = $user->id;
        $companyUser->birthday = $fields->contactBirthday;
        $companyUser->nationalInsuranceNumber = $fields->contactRegistrationNumber;

        if(count($this->findCompanyUser($companyUser->userId)) > 0) {
            $record = CompanyUserRecord::findOne($companyUser->id);

            if (!$record) {
                throw new Exception('Invalid company user ID: ' . $companyUser->id);
            }
        }else{
            $record = new CompanyUserRecord();
        }

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