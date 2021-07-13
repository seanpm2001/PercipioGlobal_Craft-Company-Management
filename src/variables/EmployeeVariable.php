<?php

namespace percipiolondon\companymanagement\variables;

use percipiolondon\companymanagement\records\Employee as EmployeeRecord;

class EmployeeVariable
{
    public function getEmployees($companyId)
    {
        return EmployeeRecord::findAll(['companyId' => $companyId]);
    }
}
