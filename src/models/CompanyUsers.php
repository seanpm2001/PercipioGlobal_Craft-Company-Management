<?php

namespace percipiolondon\companymanagement\models;

use craft\base\Model;
use yii\validators\Validator;
use Craft;

class CompanyUsers extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var integer
     */
    public $userId;

    /**
     * @var DateTime
     */
    public $employeeStartDate;

    /**
     * @var DateTime
     */
    public $employeeEndDate;

    /**
     * @var DateTime
     */
    public $birthday;

    /**
     * @var string
     */
    public $nationalInsuranceNumber;

    /**
     * @var string
     */
    public $grossIncome;

    /**
     * @var array
     */
    public $documents;


    // Public Methods
    // =========================================================================
    public function rules()
    {
        $rules = parent::defineRules();

        $rules[] = [['nationalInsuranceNumber'], 'required'];

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