<?php

namespace percipiolondon\companymanagement\models;

use craft\base\Model;
use yii\validators\Validator;
use Craft;
use DateTime;

class Employee extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $userId;

    /**
     * @var integer
     */
    public $companyId;

    /**
     * @var DateTime
     */
    public $joinDate;

    /**
     * @var DateTime
     */
    public $endDate;

    /**
     * @var DateTime
     */
    public $dateOfBirth;

    /**
     * @var string
     */
    public $nationalInsuranceNumber;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $middleName;

    /**
     * @var string
     */
    public $knownAs;

    /**
     * @var string
     */
    public $nameTitle;

    /**
     * @var string
     */
    public $ethnicity;

    /**
     * @var string
     */
    public $maritalStatus;

    /**
     * @var string
     */
    public $drivingLicense;

    /**
     * @var string
     */
    public $address;

    /**
     * @var string
     */
    public $gender;

    /**
     * @var string
     */
    public $nationality;

    /**
     * @var string
     */
    public $probationPeriod;

    /**
     * @var string
     */
    public $noticePeriod;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var string
     */
    public $department;

    /**
     * @var string
     */
    public $jobTitle;

    /**
     * @var string
     */
    public $contractType;

    /**
     * @var string
     */
    public $personalEmail;

    /**
     * @var string
     */
    public $personalMobile;

    /**
     * @var string
     */
    public $personalPhone;

    /**
     * @var string
     */
    public $directDialingIn;

    /**
     * @var string
     */
    public $workMobile;

    /**
     * @var array
     */
    public $documents;


    // Public Methods
    // =========================================================================
    public function rules()
    {
        $rules = parent::defineRules();

        $rules[] = [['nameTitle', 'firstName', 'lastName', 'dateOfBirth', 'nationalInsuranceNumber'], 'required'];

        $rules[] = ['nationalInsuranceNumber', function($attribute, $params, Validator $validator){

            $ssn  = strtoupper(str_replace(' ', '', $this->$attribute));
            $preg = "/^[A-CEGHJ-NOPR-TW-Z][A-CEGHJ-NPR-TW-Z][0-9]{6}[ABCD]?$/";

            if (!preg_match($preg, $ssn)) {
                $error = Craft::t('company-management', '"{value}" is not a valid National Insurance Number.', [
                    'attribute' => $attribute,
                    'value' => $ssn,
                ]);

                $validator->addError($this, $attribute, $error);
            }
        }];

        return $rules;
    }
}
