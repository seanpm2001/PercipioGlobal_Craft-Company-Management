<?php


namespace percipiolondon\companymanagement\elements;

use percipiolondon\companymanagement\records\Employee as EmployeeRecord;

use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\UserQuery;
use yii\db\Exception;
use yii\db\Query;
use Craft;
use DateTime;
use ArrayObject;
use yii\validators\Validator;

class Employee extends Element
{

    /**
     * Employee Statusses
     */

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * @var DateTime
     */
    public $postDate;

    /**
     * @var DateTime
     */
    public $expiryDate;

    /**
     * @var Int
     */
    public $userId;

    /**
     * @var Int
     */

    public $companyId;

    /**
     * @var String
     */
    public $slug;

    /**
     * @var DateTime
     */
    public $joinDate;

    /**
     * @var DateTime
     */
    public $endDate;

    /**
     * @var String
     */
    public $probationPeriod;

    /**
     * @var String
     */
    public $noticePeriod;

    /**
     * @var String
     */
    public $firstName;

    /**
     * @var String
     */
    public $middleName;

    /**
     * @var String
     */
    public $lastName;

    /**
     * @var String
     */
    public $nameTitle;

    /**
     * @var String
     */
    public $knownAs;

    /**
     * @var String
     */
    public $reference;

    /**
     * @var DateTime
     */
    public $dateOfBirth;

    /**
     * @var String
     */
    public $gender;

    /**
     * @var String
     */
    public $nationality;

    /**
     * @var String
     */
    public $ethnicity;

    /**
     * @var String
     */
    public $maritalStatus;

    /**
     * @var String
     */
    public $nationalInsuranceNumber;

    /**
     * @var String
     */
    public $drivingLicense;

    /**
     * @var String
     */
    public $personalEmail;

    /**
     * @var String
     */
    public $personalMobile;

    /**
     * @var String
     */
    public $personalPhone;

    /**
     * @var ArrayObject
     */
    public $address;

    /**
     * @var String
     */
    public $jobTitle;

    /**
     * @var String
     */
    public $department;

    /**
     * @var String
     */
    public $contractType;

    /**
     * @var String
     */
    public $directDialingIn;

    /**
     * @var String
     */
    public $workExtension;

    /**
     * @var String
     */
    public $workMobile;

    public function init()
    {
        parent::init();
    }

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('company-management', 'Employee');
    }

    /**
     * @inheritdoc
     */
    public static function lowerDisplayName(): string
    {
        return Craft::t('company-management', 'employee');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('company-management', 'Employee');
    }

    /**
     * @inheritdoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('company-management', 'employees');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'employee';
    }

    /**
     * Returns whether elements of this type will be storing any data in the `content`
     * table (tiles or custom fields).
     *
     * @return bool Whether elements of this type will be storing any data in the `content` table.
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * Returns whether elements of this type have traditional titles.
     *
     * @return bool Whether elements of this type have traditional titles.
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => Craft::t('company-management', 'Active'),
            self::STATUS_INACTIVE => Craft::t('company-management', 'Inactive'),
        ];
    }

    /**
     * @return bool
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * Creates an [[ElementQueryInterface]] instance for query purpose.
     *
     * The returned [[ElementQueryInterface]] instance can be further customized by calling
     * methods defined in [[ElementQueryInterface]] before `one()` or `all()` is called to return
     * populated [[ElementInterface]] instances. For example,
     *
     * ```php
     * // Find the entry whose ID is 5
     * $entry = Entry::find()->id(5)->one();
     *
     * // Find all assets and order them by their filename:
     * $assets = Asset::find()
     *     ->orderBy('filename')
     *     ->all();
     * ```
     *
     * If you want to define custom criteria parameters for your elements, you can do so by overriding
     * this method and returning a custom query class. For example,
     *
     * ```php
     * class Product extends Element
     * {
     *     public static function find()
     *     {
     *         // use ProductQuery instead of the default ElementQuery
     *         return new ProductQuery(get_called_class());
     *     }
     * }
     * ```
     *
     * You can also set default criteria parameters on the ElementQuery if you don’t have a need for
     * a custom query class. For example,
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         return parent::find()->limit(50);
     *     }
     * }
     * ```
     *
     * @return ElementQueryInterface The newly created [[ElementQueryInterface]] instance.
     */

    public static function find(): ElementQueryInterface
    {
        return new UserQuery(static::class);
    }

    /**
     * Defines the sources that elements of this type may belong to.
     *
     * @param string|null $context The context ('index' or 'modal').
     *
     * @return array The sources.
     * @see sources()
     */
    protected static function defineSources(string $context = null): array
    {
        $ids = self::_getEmployeeIds();
        return [
            [
                'key' => '*',
                'label' => 'All Employees',
                'defaultSort' => ['firstName', 'desc'],
                'criteria' => ['id' => $ids],
            ]
        ];
    }

    /**
     * @param string|null $source
     * @return array
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $elementsService = Craft::$app->getElements();

        // Delete
        $actions[] = $elementsService->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('company-management', 'Are you sure you want to delete the selected employees?'),
            'successMessage' => Craft::t('company-management', 'employees deleted.'),
        ]);

        //$actions[] = SetStatus::class;

        return $actions;
    }

    /**
     * @return array
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('company-management', 'Name')],
            'dateCreated' => ['label' => Craft::t('company-management', 'Date Created')],
        ];
    }

    /**
     * @param string $source
     * @return array
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = [];
        $attributes[] = 'name';
        $attributes[] = 'dateCreated';
        $attributes[] = 'dateUpdated';

        return $attributes;
    }

    /**
     * @return array
     */
    private static function _getEmployeeIds(): array
    {
        $employeeIds = [];

        // Fetch all employee UIDs
        // @TODO: only select based on company ID
        $employees = (new Query())
            ->from('{{%companymanagement_employees}}')
            ->select('*')
            ->all();

        foreach ($employees as $employee) {
            $employeeIds[] = $employee['id'];
        }

        return $employeeIds;
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        // @TODO: Create additional rules
        $rules = parent::defineRules();
        $rules[] = [['nameTitle', 'firstName', 'lastName', 'dateOfBirth', 'nationalInsuranceNumber'], 'required'];

        $rules[] = ['personalEmail', function($attribute, $params, Validator $validator){
            $preg = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

            // Valid email
            if (!preg_match($preg, $this->$attribute)) {
                $error = Craft::t('company-management', '"{value}" is not a valid email address.', [
                    'attribute' => $attribute,
                    'value' => $this->$attribute,
                ]);

                $validator->addError($this, $attribute, $error);
            }
        }];

        return $rules;
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * Performs actions before an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return void
     */
    public function afterSave(bool $isNew)
    {
        if (!$this->propagating) {

            $this->_saveRecord($isNew);
        }

        return parent::afterSave($isNew);
    }

    /**
     * Performs actions before an element is deleted.
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * @param $isNew
     * @throws Exception
     */
    private function _saveRecord($isNew)
    {
        if (!$isNew) {
            $record = EmployeeRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid employee ID: ' . $this->id);
            }
        } else {
            $record = new EmployeeRecord();
            $record->id = (int)$this->id;
        }

        // Personal
//        $record->title = $this->firstName . ' ' . $this->lastName;
        $record->firstName = $this->firstName;
        $record->lastName = $this->lastName;
        $record->middleName = $this->middleName;
        $record->knownAs = $this->knownAs;
        $record->nameTitle = $this->nameTitle;
        $record->ethnicity = $this->ethnicity;
        $record->maritalStatus = $this->maritalStatus;
        $record->drivingLicense = $this->drivingLicense;
        $record->address = $this->address;
        $record->gender = $this->gender;
        $record->nationality = $this->nationality;
        $record->nationalInsuranceNumber = $this->nationalInsuranceNumber;
        $record->dateOfBirth = $this->dateOfBirth;

        // Employee related info inside of the company
        $record->joinDate = $this->joinDate;
        $record->endDate = $this->endDate;
        $record->probationPeriod = $this->probationPeriod;
        $record->noticePeriod = $this->noticePeriod;
        $record->reference = $this->reference;
        $record->department = $this->department;
        $record->jobTitle = $this->jobTitle;

        // Contacts
        $record->contractType = $this->contractType;
        $record->personalEmail = $this->personalEmail;
        $record->personalMobile = $this->personalMobile;
        $record->personalPhone = $this->personalPhone;
        $record->directDialingIn = $this->directDialingIn;
        $record->workMobile = $this->workMobile;
        $record->workExtension = $this->workExtension;

        $record->save(false);
    }

}
