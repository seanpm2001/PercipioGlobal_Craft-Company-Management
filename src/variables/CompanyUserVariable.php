<?php

namespace percipiolondon\companymanagement\variables;

use percipiolondon\companymanagement\records\CompanyUser as CompanyUserRecord;

class CompanyUserVariable
{
    public function getUsers($companyId)
    {
        return CompanyUserRecord::findAll(['companyId' => $companyId]);
    }
}