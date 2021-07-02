<?php

namespace percipiolondon\companymanagement\elements\db;

use craft\elements\db\ElementQuery;

class CompanyUserQuery extends ElementQuery
{
    public $companyId;
    public $userId;
    public $employeeStartDate;
    public $employeeEndDate;
    public $birthday;
    public $nationalInsuranceNumber;
    public $grossIncome;

    public function companyId($value)
    {
        $this->companyId($value);
        return $this;
    }

    public function userId($value)
    {
        $this->userId($value);
        return $this;
    }

    public function employeeStartDate($value)
    {
        $this->employeeStartDate($value);
        return $this;
    }

    public function employeeEndDate($value)
    {
        $this->employeeEndDate($value);
        return $this;
    }

    public function birthday($value)
    {
        $this->birthday($value);
        return $this;
    }

    public function nationalInsuranceNumber($value)
    {
        $this->nationalInsuranceNumber($value);
        return $this;
    }

    public function grossIncome($value)
    {
        $this->grossIncome($value);
        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->_normalizeTypeId();

        $this->joinElementTable('companymanagement_users');

        $this->query->select([
            'companymanagement_companies.companyId',
            'companymanagement_companies.userId',
            'companymanagement_companies.employeeStartDate',
            'companymanagement_companies.employeeEndDate',
            'companymanagement_companies.birthday',
            'companymanagement_companies.nationalInsuranceNumber',
            'companymanagement_companies.grossIncome',
        ]);

        $this->_applyRefParam();

        return parent::beforePrepare();
    }
}
