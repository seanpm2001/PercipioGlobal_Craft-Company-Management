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
    public $typeId;
    public $slug;

    // Company Info
    public $name;
    public $info;
    public $address;
    public $town;
    public $postcode;
    public $website;
    public $logo;

    // Company Manager Info
    public $contactFirstName;
    public $contactLastName;
    public $contactEmail;
    public $contactRegistrationNumber;
    public $contactPhone;
    public $contactBirthday;

    public function name($value)
    {
        $this->name = $value;
        return $this;
    }

    public function typeId($value)
    {
        $this->typeId = $value;
        return $this;
    }

    public function info($value)
    {
        $this->info = $value;
        return $this;
    }

    public function slug($value)
    {
        $this->slug = $value;
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

    public function contactFirstName($value)
    {
        $this->contactFirstName = $value;
        return $this;
    }

    public function contactLastName($value)
    {
        $this->contactLastName = $value;
        return $this;
    }

    public function contactEmail($value)
    {
        $this->contactEmail = $value;
        return $this;
    }

    public function contactRegistrationNumber($value)
    {
        $this->contactRegistrationNumber = $value;
        return $this;
    }

    public function contactPhone($value)
    {
        $this->contactPhone = $value;
        return $this;
    }

    public function contactBirthday($value)
    {
        $this->contactBirthday = $value;
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
            'companymanagement_company.info',
            'companymanagement_company.slug',
            'companymanagement_company.typeId',
            'companymanagement_company.address',
            'companymanagement_company.town',
            'companymanagement_company.postcode',
            'companymanagement_company.website',
            'companymanagement_company.logo',
            'companymanagement_company.uid',
            'companymanagement_company.contactFirstName',
            'companymanagement_company.contactLastName',
            'companymanagement_company.contactEmail',
            'companymanagement_company.contactRegistrationNumber',
            'companymanagement_company.contactPhone',
            'companymanagement_company.contactBirthday',
            'companymanagement_company.userId',
        ]);


        return parent::beforePrepare();
    }
}