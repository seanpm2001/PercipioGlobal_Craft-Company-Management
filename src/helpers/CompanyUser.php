<?php

namespace percipiolondon\companymanagement\helpers;

use craft\helpers\DateTimeHelper;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\models\CompanyUsers as CompanyUsersModel;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use Craft;

class CompanyUser
{
    public static function companyUserFromPost(Request $request = null): CompanyUsersModel
    {
        if (null === $request) {
            $request = Craft::$app->getRequest();
        }

        $companyUser = $request->getBodyParam('userId');

        if($companyUser) {
            $query = CompanyManagement::$plugin->companyUser->getCompanyUserById($companyUser);

            if (count($query) === 0) {
                throw new NotFoundHttpException(Craft::t('company-management', 'No company user with the ID “{id}”', ['id' => $companyUser]));
            }

            $query = $query[0];

            $user = new CompanyUsersModel();
            $user->userId = $query["userId"];
            $user->employeeStartDate = $query["employeeStartDate"];
            $user->employeeEndDate = $query["employeeEndDate"];
            $user->birthday = $query["birthday"];
            $user->nationalInsuranceNumber = $query["nationalInsuranceNumber"];
            $user->grossIncome = $query["grossIncome"];

        }else {
            $user = new CompanyUsersModel();
        }

        return $user;
    }

    public static function populateCompanyUserFromPost(CompanyUsersModel $companyUser = null, Request $request = null): CompanyUsersModel
    {
        if ($request === null) {
            $request = Craft::$app->getRequest();
        }

        if ($companyUser === null) {
            $companyUser = static::companyUserFromPost($request);
        }

        $companyUser->userId = $request->getBodyParam('userId');
        $companyUser->employeeStartDate = DateTimeHelper::toDateTime($request->getBodyParam('employeeStartDate'));
        $companyUser->employeeEndDate = DateTimeHelper::toDateTime($request->getBodyParam('employeeEndDate'));
        $companyUser->birthday = DateTimeHelper::toDateTime($request->getBodyParam('birthday'));
        $companyUser->nationalInsuranceNumber = $request->getBodyParam('nationalInsuranceNumber');
        $companyUser->grossIncome = $request->getBodyParam('grossIncome');

        return $companyUser;
    }
}