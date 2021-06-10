<?php
/**
 * @link https://percipio.london/
 * @copyright Copyright (c) Percipio Global Ltd.
 * @license https://github.com/percipioglobal/craft-company-management/LICENSE.md
 */

namespace percipiolondon\companymanagement\models;

use Craft;
use craft\base\Field;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

use percipiolondon\companymanagement\elements\Company;

/**
 * Company model class.
 *
 * @mixin FieldLayoutBehavior
 * @author Percipio Global Ltd. <support@pixelandtonic.com>
 * @since 1.0.0
 */

class Company extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    // Company ID instead of section ID: https://github.com/craftcms/cms/blob/43dc0a6f1d406abfd6f33fa2d9ffb1a49e6d5257/src/models/EntryType.php

    /**
     * @var int|null Field layout ID
     */
    public $fieldLayoutId;

    /**
     * @var string|null Name
     */
    public $name;

    /**
     * @var string UID
     */
    public $uid;

    /**
     * @var bool Has title field
     */
    public $hasTitleField = true;

    /**
     * @var string|null Title format
     */
    public $titleFormat;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['fieldLayout'] = [
            'class' => FieldLayoutBehavior::class,
            'elementType' => Company::class,
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['id', 'fieldLayoutId'], 'number', 'integerOnly' => true];
        $rules[] = [['name', 'handle'], 'required'];
        $rules[] = [['name', 'handle'], 'string', 'max' => 255];
        $rules[] = [
            ['handle'],
            HandleValidator::class,
            'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title'],
        ];
        $rules[] = [
            ['name'],
            UniqueValidator::class,
            'targetClass' => EntryTypeRecord::class,
            'targetAttribute' => ['name', 'sectionId'],
            'comboNotUnique' => Craft::t('yii', '{attribute} "{value}" has already been taken.'),
        ];
        $rules[] = [
            ['handle'],
            UniqueValidator::class,
            'targetClass' => EntryTypeRecord::class,
            'targetAttribute' => ['handle', 'sectionId'],
            'comboNotUnique' => Craft::t('yii', '{attribute} "{value}" has already been taken.'),
        ];

        if (!$this->hasTitleField) {
            $rules[] = [['titleFormat'], 'required'];
        }

        return $rules;
    }
}