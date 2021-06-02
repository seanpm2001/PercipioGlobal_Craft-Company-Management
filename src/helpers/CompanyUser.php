<?php

namespace percipiolondon\companymanagement\helpers;

use craft\helpers\DateTimeHelper;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\models\CompanyUsers as CompanyUsersModel;
use percipiolondon\companymanagement\records\CompanyUser as CompanyUserRecord;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use Craft;

class CompanyUser
{
    public static function populateCompanyUserFromPost(int $userId = null, int $companyId = null): CompanyUsersModel
    {
        $request = Craft::$app->getRequest();

        $companyUser = new CompanyUsersModel();

        $companyUser->userId = $request->getBodyParam('userId') ?? $userId;
        $companyUser->employeeStartDate = $request->getBodyParam('employeeStartDate');
        $companyUser->employeeEndDate = $request->getBodyParam('employeeEndDate');
        $companyUser->birthday = $request->getBodyParam('birthday') ?? $request->getBodyParam('contactBirthday') ?? "";
        $companyUser->nationalInsuranceNumber = strtoupper(str_replace(' ', '', $request->getBodyParam('nationalInsuranceNumber') ?? $request->getBodyParam('contactRegistrationNumber') ?? ""));
        $companyUser->grossIncome = $request->getBodyParam('grossIncome');
        $companyUser->companyId = $request->getBodyParam('companyId')[0] ?? $companyId;

        return $companyUser;
    }

    public static function populateCompanyUserFromRecord(CompanyUserRecord $companyUserRecord, int $userId = null, int $companyId = null): CompanyUsersModel
    {
        $companyUser = new CompanyUsersModel();

        $companyUser->userId =  $userId;
        $companyUser->employeeStartDate = $companyUserRecord->employeeStartDate;
        $companyUser->employeeEndDate = $companyUserRecord->employeeEndDate;
        $companyUser->birthday = $companyUserRecord->birthday;
        $companyUser->nationalInsuranceNumber = strtoupper(str_replace(' ', '', $companyUserRecord->nationalInsuranceNumber));
        $companyUser->grossIncome = $companyUserRecord->grossIncome;
        $companyUser->companyId = $companyId;

        return $companyUser;
    }
}