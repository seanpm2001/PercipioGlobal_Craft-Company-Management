<?php

namespace percipiolondon\companymanagement\services;

use craft\db\Query;
use craft\helpers\Db;
use percipiolondon\companymanagement\models\CompanyUsers as CompanyUsersModel;
use yii\base\Component;
use percipiolondon\companymanagement\records\CompanyUser as CompanyUserRecord;
use Craft;

class CompanyUser extends Component
{
    public function saveCompanyUser($fields,$user)
    {
        $companyUser = new CompanyUsers();
        $companyUser->id = $fields->id;
        $companyUser->userId = $user->id;
        $companyUser->birthday = $fields->contactBirthday;
        $companyUser->nationalInsuranceNumber = $fields->contactRegistrationNumber;

        if(count($this->getCompanyUserByNin($companyUser->userId)) > 0) {
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

    public function getCompanyUserByNin($nationalInsuranceNumber)
    {
        return (new Query())
            ->select(['userId'])
            ->from(['{{%companymanagement_users}}'])
            ->where(Db::parseParam('nationalInsuranceNumber', $nationalInsuranceNumber))
            ->column();
    }

    public function getCompanyUserById($id)
    {
        return (new Query())
            ->select('*')
            ->from(['{{%companymanagement_users}}'])
            ->where(Db::parseParam('userId', $id))
            ->all();
    }

    public function addEditUserCustomFieldTab(array &$context)
    {
        $context['tabs']['companyManagement'] = [
            'label' => Craft::t('company-management', 'Company Management'),
            'url' => '#companyManagement'
        ];
    }

    public function addEditUserCustomFieldContent(array &$context)
    {

        $query = $context['user'] ? static::getCompanyUserById($context['user']->id) : null;
        $companyUser = null;

        if($query) {
            $query = $query[0];

            $companyUser = new CompanyUsersModel();
            $companyUser->userId = $query["userId"];
            $companyUser->employeeStartDate = $query["employeeStartDate"];
            $companyUser->employeeEndDate = $query["employeeEndDate"];
            $companyUser->birthday = $query["birthday"];
            $companyUser->nationalInsuranceNumber = $query["nationalInsuranceNumber"];
            $companyUser->grossIncome = $query["grossIncome"];
            $companyUser->documents = [];
        }

        return Craft::$app->getView()->renderTemplate('company-management/_includes/_editUserTab', [
            'user' => $context['user'] ?? null,
            'companyUser' => $companyUser,
        ]);
    }
}