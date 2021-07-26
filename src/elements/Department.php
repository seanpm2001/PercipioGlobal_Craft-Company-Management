<?php

namespace percipiolondon\companymanagement\elements;

use craft\base\Element;

use Craft;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use percipiolondon\companymanagement\elements\db\DepartmentQuery;
use percipiolondon\companymanagement\records\Department as DepartmentRecord;
use yii\db\Exception;
use yii\db\Query;

class Department extends Element
{
    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

    public $slug;
    public $companyId;


    // Static Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('company-management', 'Department');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t('company-management', 'department');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('company-management', 'Departments');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('company-management', 'departments');
    }

    public static function refHandle()
    {
        return 'department';
    }

    public static function hasContent(): bool
    {
        return true;
    }

    public static function hasTitles(): bool
    {
        return true;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }

    public function getStatus()
    {
        return parent::getStatus();
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ENABLED => Craft::t('company-management', 'Enabled'),
            self::STATUS_DISABLED => Craft::t('company-management', 'Disabled'),
        ];
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    public static function find(): ElementQueryInterface
    {
        return new DepartmentQuery(static::class);
    }


    // Protected Static Methods
    // =========================================================================
    protected static function defineSources(string $context = null): array
    {
        $ids = self::_getDepartmentIds();

        return [
            [
                'key' => '*',
                'label' => 'All Departments',
                'criteria' => ['id' => $ids],
            ]
        ];
    }

    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $elementsService = Craft::$app->getElements();

        // Delete
        $actions[] = $elementsService->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('company-management', 'Are you sure you want to delete the selected departments?'),
            'successMessage' => Craft::t('company-management', 'Departments deleted.'),
        ]);

        //$actions[] = SetStatus::class;

        return $actions;
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('company-management', 'Title')],
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = [];
        $attributes[] = 'dateCreated';
        $attributes[] = 'dateUpdated';

        return $attributes;
    }

    protected static function defineSortOptions(): array
    {
        return [
            'dateCreated' => Craft::t('company-management', 'Date Created'),
        ];
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'slug':
                // use this to customise returned values (add links / mailto's etc)
                // https://docs.craftcms.com/commerce/api/v3/craft-commerce-elements-traits-orderelementtrait.html#protected-methods
                return $this->slug;
        }

        return parent::tableAttributeHtml($attribute);
    }


    // Private Methods
    // =========================================================================
    private static function _getDepartmentIds(): array
    {

        $departmentIds = [];

        // Fetch all company UIDs
        $departments = (new Query())
            ->from('{{%companymanagement_departments}}')
            ->select('*')
            ->all();

        foreach ($departments as $department) {
            $departmentIds[] = $department['id'];
        }

        return $departmentIds;
    }

    private function _saveRecord($isNew)
    {
        if(!$isNew) {
            $record = DepartmentRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid department ID: ' . $this->id);
            }
        } else {
            $record = new DepartmentRecord();
            $record->id = (int)$this->id;
        }

        $record->title = $this->title;
        $record->slug = $this->slug;
        $record->companyId = $this->companyId;

        $record->save(false);
    }


    // Public Methods
    // =========================================================================
    public function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['title', 'companyId'], 'required'];

        return $rules;
    }

    public function getIsEditable(): bool
    {
        return true;
    }

    public function getCpEditUrl()
    {
        return 'company-management/departments/'.$this->id;
    }

    public function afterSave(bool $isNew)
    {
        if (!$this->propagating) {

            $this->_saveRecord($isNew);
        }

        return parent::afterSave($isNew);
    }
}
