<?php

namespace percipiolondon\companymanagement\elements\db;

use craft\elements\db\ElementQuery;
use percipiolondon\companymanagement\elements\Employee;

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
//        if(!isset($config['status']))
//        {
//            $config['status'] = [
//                Employee::STATUS_ACTIVE,
//            ];
//        }

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
            'companymanagement_employees.companyId',
            'companymanagement_employees.userId',
            'companymanagement_employees.firstName',
            'companymanagement_employees.middleName',
            'companymanagement_employees.lastName',
            'companymanagement_employees.knownAs',
            'companymanagement_employees.reference',
            'companymanagement_employees.gender',
            'companymanagement_employees.nationality',
            'companymanagement_employees.nationalInsuranceNumber',
            'companymanagement_employees.drivingLicense',
            'companymanagement_employees.address',
            'companymanagement_employees.dateOfBirth',
            'companymanagement_employees.ethnicity',
            'companymanagement_employees.maritalStatus',
            'companymanagement_employees.personalEmail',
            'companymanagement_employees.personalMobile',
            'companymanagement_employees.personalPhone',
            'companymanagement_employees.department',
            'companymanagement_employees.jobTitle',
            'companymanagement_employees.directDialingIn',
            'companymanagement_employees.workExtension',
            'companymanagement_employees.workMobile',
            'companymanagement_employees.contractType',
            'companymanagement_employees.joinDate',
            'companymanagement_employees.endDate',
            'companymanagement_employees.probationPeriod',
            'companymanagement_employees.noticePeriod',
        ]);

        return parent::beforePrepare();
    }
}
