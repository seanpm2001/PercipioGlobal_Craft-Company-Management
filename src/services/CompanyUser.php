<?php

namespace percipiolondon\companymanagement\services;

use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\models\CompanyUsers as CompanyUsersModel;
use yii\base\Component;
use percipiolondon\companymanagement\records\CompanyUser as CompanyUserRecord;
use percipiolondon\companymanagement\helpers\CompanyUser as CompanyUserHelper;
use Craft;
use yii\base\Exception;

class CompanyUser extends Component
{
    public function saveCompanyUser(CompanyUsersModel $companyUser, int $userId)
    {
        $companyUserRecord = CompanyUserRecord::findOne(['userId' => $companyUser->userId]);

        if($companyUserRecord) {
            $record = $companyUserRecord;

            if (!$record) {
                throw new Exception('Invalid company user ID: ' . $companyUser->id);
            }
        }else{
            $record = new CompanyUserRecord();
            $record->userId = $userId;
        }

        $record->birthday = Db::prepareDateForDb($companyUser->birthday);
        $record->employeeStartDate = Db::prepareDateForDb($companyUser->employeeStartDate);
        $record->employeeEndDate = Db::prepareDateForDb($companyUser->employeeEndDate);
        $record->grossIncome = $companyUser->grossIncome;
        $record->nationalInsuranceNumber = $companyUser->nationalInsuranceNumber;
        $record->companyId = $companyUser->companyId;

        return $record->save(false);
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
        $companyUser = $context['user'] ? CompanyUserRecord::findOne(['userId' => $context['user']->id]) : null;
        $companyId = $companyUser->companyId ?? Craft::$app->getRequest()->get('companyId') ?? null;

        return Craft::$app->getView()->renderTemplate('company-management/_includes/_editUserTab', [
            'user' => $context['user'] ?? null,
            'companyUser' => $companyUser,
            'documents' => [],
            'company' => $companyId,
        ]);
    }

    public function saveCompanyIdInCompanyUser(int $userId, int $companyId)
    {
        $companyUser = CompanyUserRecord::findOne(['userId' => $userId]);
        $companyUser->companyId = $companyId;

        $companyUser = CompanyUserHelper::populateCompanyUserFromRecord($companyUser, $userId, $companyId);

        static::saveCompanyUser($companyUser,$userId);
    }
}