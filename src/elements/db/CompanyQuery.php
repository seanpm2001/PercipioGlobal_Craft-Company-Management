<?php

namespace percipiolondon\companymanagement\elements\db;

use Craft;
use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use percipiolondon\companymanagement\elements\Company;

class CompanyQuery extends ElementQuery
{
    // Company Info
    public $name;
    public $info;
    public $shortName;
    public $address;
    public $town;
    public $postcode;
    public $registerNumber;
    public $payeReference;
    public $accountsOfficeReference;
    public $taxReference;
    public $website;
    public $logo;

    // Company Manager Info
    public $contactName;
    public $contactEmail;
    public $contactRegistrationNumber;
    public $contactPhone;
    public $contactBirthday;

    public function name($value)
    {
        $this->name = $value;
        return $this;
    }

    public function info($value)
    {
        $this->info = $value;
        return $this;
    }

    public function shortName($value)
    {
        $this->shortName = $value;
        return $this;
    }

    public function address($value)
    {
        $this->address = $value;
        return $this;
    }

    public function town($value)
    {
        $this->town = $value;
        return $this;
    }

    public function postcode($value)
    {
        $this->postcode = $value;
        return $this;
    }

    public function registerNumber($value)
    {
        $this->registerNumber = $value;
        return $this;
    }

    public function payeReference($value)
    {
        $this->payeReference = $value;
        return $this;
    }

    public function accountsOfficeReference($value)
    {
        $this->accountsOfficeReference = $value;
        return $this;
    }

    public function taxReference($value)
    {
        $this->taxReference = $value;
        return $this;
    }

    public function website($value)
    {
        $this->website = $value;
        return $this;
    }

    public function logo($value)
    {
        $this->logo = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {

        // join in the products table
        $this->joinElementTable('companymanagement_company');

        // select the price column
        $this->query->select([
            'companymanagement_company.id',
            'companymanagement_company.name',
            'companymanagement_company.shortName',
            'companymanagement_company.address',
            'companymanagement_company.town',
            'companymanagement_company.postcode',
            'companymanagement_company.registerNumber',
            'companymanagement_company.payeReference',
            'companymanagement_company.accountsOfficeReference',
            'companymanagement_company.taxReference',
            'companymanagement_company.website',
            'companymanagement_company.uid',
        ]);


        return parent::beforePrepare();
    }
}