<?php

namespace percipiolondon\companymanagement\helpers;

use craft\helpers\DateTimeHelper;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\models\Employee as EmployeeModel;
use percipiolondon\companymanagement\records\Employee as EmployeeRecord;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use Craft;

class Employee
{
    public static function populateEmployeeFromPost(int $userId = null, int $companyId = null): EmployeeModel
    {
        $request = Craft::$app->getRequest();

        $employee = new EmployeeModel();

        $employee->userId = $request->getBodyParam('userId') ?? $userId;
        $employee->employeeStartDate = $request->getBodyParam('employeeStartDate');
        $employee->employeeEndDate = $request->getBodyParam('employeeEndDate');
        $employee->birthday = $request->getBodyParam('birthday') ?? $request->getBodyParam('contactBirthday') ?? "";
        $employee->nationalInsuranceNumber = strtoupper(str_replace(' ', '', $request->getBodyParam('nationalInsuranceNumber') ?? $request->getBodyParam('contactRegistrationNumber') ?? ""));
        $employee->grossIncome = $request->getBodyParam('grossIncome');
        $employee->jobRole = $request->getBodyParam('jobRole');
        $employee->companyId = $request->getBodyParam('companyId')[0] ?? $companyId;

        return $employee;
    }

    public static function populateEmployeeFromRecord(EmployeeRecord $employeeRecord, int $userId = null, int $companyId = null): EmployeeModel
    {
        $employee = new EmployeeModel();

        $employee->userId =  $userId;
        $employee->employeeStartDate = $employeeRecord->employeeStartDate;
        $employee->employeeEndDate = $employeeRecord->employeeEndDate;
        $employee->birthday = $employeeRecord->birthday;
        $employee->nationalInsuranceNumber = strtoupper(str_replace(' ', '', $employeeRecord->nationalInsuranceNumber));
        $employee->grossIncome = $employeeRecord->grossIncome;
        $employee->jobRole = $employeeRecord->jobRole;
        $employee->companyId = $companyId;

        return $employee;
    }
}
