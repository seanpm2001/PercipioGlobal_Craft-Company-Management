<?php

namespace percipiolondon\companymanagement\elements\db;

use craft\elements\db\ElementQuery;

class EmployeeQuery extends ElementQuery
{
    public $slug;

    // Employee Name
    public $nameTitle;
    public $firstName;
    public $middleName;
    public $lastName;
    public $knownAs;
    public $reference;

    // Employee Information
    public $gender;
    public $nationality;
    public $nationalInsuranceNumber;
    public $drivingLicense;
    public $address;
    public $dateOfBirth;

    // Personal Information
    public $ethnicity;
    public $maritalStatus;
    public $personalEmail;
    public $personalMobile;
    public $personalPhone;

    // Company Information
    public $department;
    public $jobTitle;
    public $directDialingIn;
    public $workExtension;
    public $workMobile;

    // Contract Information
    public $contractType;
    public $joinDate;
    public $endDate;
    public $probationPeriod;
    public $noticePeriod;

    public $companyId;
    public $userId;

    // TODO
    public $grossIncome;

    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default status
        if(!isset($config['status']))
        {
            $config['status'] = [
                Employee::STATUS_ACTIVE,
            ];
        }

        parent::__construct($elementType, $config);
    }

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

    /**
     * @param $value
     * @return static self reference
     */
    public function firstName($value)
    {
        $this->firstName = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function middleName($value)
    {
        $this->middleName = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function lastName($value)
    {
        $this->lastName = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function knownAs($value)
    {
        $this->knownAs = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function reference($value)
    {
        $this->reference = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function gender($value) {
        $this->gender = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function nationality($value) {
        $this->nationality = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function nationalInsuranceNumber($value) {
        $this->nationalInsuranceNumber = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function drivingLicense($value) {
        $this->drivingLicense = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function address($value) {
        $this->address = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function dateOfBirth($value) {
        $this->dateOfBirth = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function ethnicity($value) {
        $this->ethnicity = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function maritalStatus($value) {
        $this->maritalStatus = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function personalEmail($value) {
        $this->personalEmail = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function personalMobile($value) {
        $this->personalMobile = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function personalPhone($value) {
        $this->personalPhone = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function department($value) {
        $this->department = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function jobTitle($value) {
        $this->jobTitle = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function directDialingIn($value) {
        $this->directDialingIn = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function workExtension($value) {
        $this->workExtension = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function workMobile($value) {
        $this->workMobile = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function contractType($value) {
        $this->contractType = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function joinDate($value)
    {
        $this->joinDate = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function endDate($value)
    {
        $this->endDate = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function probationPeriod($value)
    {
        $this->probationPeriod = $value;
        return $this;
    }

    /**
     * @param $value
     * @return static self reference
     */
    public function noticePeriod($value)
    {
        $this->noticePeriod = $value;
        return $this;
    }

    public function grossIncome($value)
    {
        $this->grossIncome($value);
        return $this;
    }

    protected function beforePrepare(): bool
    {

        $this->joinElementTable('companymanagement_employees');

        /**
         *     // Employee Name
        public $nameTitle;
        public $firstName;
        public $middleName;
        public $lastName;
        public $knownAs;
        public $reference;

        // Employee Information
        public $gender;
        public $nationality;
        public $nationalInsuranceNumber;
        public $drivingLicense;
        public $address;
        public $dateOfBirth;

        // Personal Information
        public $ethnicity;
        public $maritalStatus;
        public $personalEmail;
        public $personalMobile;
        public $personalPhone;

        // Company Information
        public $department;
        public $jobTitle;
        public $directDialingIn;
        public $workExtension;
        public $workMobile;

        // Contract Information
        public $contractType;
        public $joinDate;
        public $endDate;
        public $probationPeriod;
        public $noticePeriod;

        public $companyId;
        public $userId;
         */

        $this->query->select([
            'companymanagement_companies.companyId',
            'companymanagement_companies.userId',
            'companymanagement_companies.nameTitle',
            'companymanagement_companies.firstName',
            'companymanagement_companies.middleName',
            'companymanagement_companies.lastName',
            'companymanagement_companies.knownAs',
            'companymanagement_companies.reference',
            'companymanagement_companies.gender',
            'companymanagement_companies.nationality',
            'companymanagement_companies.nationalInsuranceNumber',
            'companymanagement_companies.drivingLicense',
            'companymanagement_companies.address',
            'companymanagement_companies.dateOfBirth',
            'companymanagement_companies.ethnicity',
            'companymanagement_companies.maritalStatus',
            'companymanagement_companies.personalEmail',
            'companymanagement_companies.personalMobile',
            'companymanagement_companies.personalPhone',
            'companymanagement_companies.department',
            'companymanagement_companies.jobTitle',
            'companymanagement_companies.directDialingIn',
            'companymanagement_companies.workExtension',
            'companymanagement_companies.workMobile',
            'companymanagement_companies.contractType',
            'companymanagement_companies.joinDate',
            'companymanagement_companies.endDate',
            'companymanagement_companies.probationPeriod',
            'companymanagement_companies.noticePeriod',
        ]);

        $this->_applyRefParam();

        return parent::beforePrepare();
    }
}
