<?php

namespace percipiolondon\companymanagement\services;

use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\models\Employee as EmployeeModel;
use yii\base\Component;
use percipiolondon\companymanagement\records\Employee as EmployeeRecord;
use percipiolondon\companymanagement\helpers\Employee as EmployeeHelper;
use Craft;
use yii\base\Exception;

class Employee extends Component
{
    public function saveEmployee(EmployeeModel $employee, int $userId)
    {
        $employeeRecord = EmployeeRecord::findOne(['userId' => $employee->userId]);

        if($employeeRecord) {
            $record = $employeeRecord;

            if (!$record) {
                throw new Exception('Invalid company user ID: ' . $employee->id);
            }
        }else{
            $record = new EmployeeRecord();
            $record->userId = $userId;
        }

        $record->birthday = Db::prepareDateForDb($employee->birthday);
        $record->employeeStartDate = Db::prepareDateForDb($employee->employeeStartDate);
        $record->employeeEndDate = Db::prepareDateForDb($employee->employeeEndDate);
        $record->grossIncome = $employee->grossIncome;
        $record->nationalInsuranceNumber = $employee->nationalInsuranceNumber;
        $record->jobRole = $employee->jobRole;
        $record->companyId = $employee->companyId;

        return $record->save(false);
    }

    public function createEmployee()
    {
        $employee =
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
        $employee = $context['user'] ? EmployeeRecord::findOne(['userId' => $context['user']->id]) : null;
        $companyId = $employee->companyId ?? Craft::$app->getRequest()->get('companyId') ?? null;

        return Craft::$app->getView()->renderTemplate('company-management/_includes/_editUserTab', [
            'user' => $context['user'] ?? null,
            'employee' => $employee,
            'documents' => [],
            'company' => $companyId,
        ]);
    }

    public function saveCompanyIdInEmployee(int $userId, int $companyId)
    {

        $employee = EmployeeRecord::findOne(['userId' => $userId]);

        if($employee) {
            $employee->companyId = $companyId;

            $employee = EmployeeHelper::populateEmployeeFromRecord($employee, $userId, $companyId);

            static::saveEmployee($employee, $userId);
        }
    }
}
