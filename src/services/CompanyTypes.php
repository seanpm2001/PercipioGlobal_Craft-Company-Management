<?php

namespace percipiolondon\companymanagement\services;

use Craft;
use craft\fieldlayoutelements\CustomField;
use craft\db\Query;
use craft\events\ConfigEvent;
use craft\fields\Dropdown;
use craft\fields\Lightswitch;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\helpers\App;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\models\FieldGroup;
use craft\models\FieldLayout;
use craft\queue\jobs\ResaveElements;
use craft\fields\Date;
use percipiolondon\companymanagement\events\CompanyTypeEvent;
use percipiolondon\timeloop\fields\TimeloopField;
use yii\base\Component;
use craft\db\Table as CraftTable;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use percipiolondon\companymanagement\db\Table;
use percipiolondon\companymanagement\models\CompanyTypeSite;
use percipiolondon\companymanagement\models\CompanyType;
use percipiolondon\companymanagement\records\CompanyType as CompanyTypeRecord;
use percipiolondon\companymanagement\records\CompanyTypeSite as CompanyTypeSiteRecord;
use percipiolondon\companymanagement\elements\Company;
use yii\base\Exception;

class CompanyTypes extends Component
{
    const CONFIG_COMPANYTYPES_KEY = 'companymanagement.companyTypes';
    const EVENT_BEFORE_SAVE_COMPANYTYPE = 'beforeSaveCompanyType';
    const EVENT_AFTER_SAVE_COMPANYTYPE = 'afterSaveCompanyType';

    /**
     * @var bool
     */
    private $_fetchedAllCompanyTypes = false;

    /**
     * @var int[]
     */
    private $_allCompanyTypeIds;

    /**
     * @var CompanyType[]
     */
    private $_companyTypesById;

    /**
     * @var CompanyType[]
     */
    private $_companyTypesByHandle;

    /**
     * @var int[]
     */
    private $_editableCompanyTypeIds;

    /**
     * @var CompanyTypeSite[]
     */
    private $_siteSettingsByCompanyId = [];

        /**
     * @var array interim storage for company types being saved via CP
     */
    private $_savingCompanyTypes = [];

    /**
     * Returns all of the Company type IDs.
     *
     * @return array An array of all the company types’ IDs.
     */

    public function getAllCompanyTypeIds(): array
    {
        if(null === $this->_allCompanyTypeIds) {
            $this->_allCompanyTypeIds = [];
            $companyTypes = $this->getAllCompanyTypes();
        }

        foreach ($companyTypes as $companyType) {
            $this->_allCompanyTypeIds[] = $companyType->id;
        }

        return $this->_allCompanyTypeIds;
    }

    /**
     * Returns all company types
     *
     * @return CompanyType[] An array of all company types
     */
    public function getAllCompanyTypes(): array
    {
        if(!$this->_fetchedAllCompanyTypes) {
            $results = $this->_createCompanyTypeQuery()->all();

            foreach ($results as $result) {
                $this->_memoizeCompanyType(new CompanyType($result));
            }

            $this->_fetchedAllCompanyTypes = true;
        }

        return $this->_companyTypesById ?: [];
    }

    /**
     * Returns a company type by its ID.
     *
     * @param int $companyTypeId the company type's ID
     * @return CompanyType|null either the company type or `null`
     */
    public function getCompanyTypeById(int $companyTypeId)
    {
        if(isset($this->_companyTypeId[$companyTypeId])) {
            return $this->_companyTypeId[$companyTypeId];
        }

        if($this->_fetchedAllCompanyTypes) {
            return null;
        }

        $result = $this->_createCompanyTypeQuery()
            ->where(['id' => $companyTypeId])
            ->one();

        if(!$result) {
            return null;
        }

        $this->_memoizeCompanyType(new CompanyType($result));

        return $this->_companyTypesById[$companyTypeId];

    }

    /**
     * Returns a company type by its handle.
     *
     * @param string $handle The company type's handle.
     * @return CompanyType|null The company type or `null`.
     */
    public function getCompanyTypeByHandle(string $handle)
    {
        if (isset($this->_companyTypesByHandle[$handle])) {
            return $this->_companyTypesByHandle[$handle];
        }

        if ($this->_fetchedAllCompanyTypes) {
            return null;
        }

        $result = $this->_createCompanyTypeQuery()
            ->where(['handle' => $handle])
            ->one();

        if (!$result) {
            return null;
        }

        $this->_memoizeCompanyType(new CompanyType($result));

        return $this->_companyTypesByHandle[$handle];
    }

    /**
     * Returns an array of company type site settings for a company type by its ID.
     *
     * @param int $companyTypeId the company type ID
     * @return array The company type settings.
     */
    public function getCompanyTypeSites($companyTypeId): array
    {
        if (!isset($this->_siteSettingsByCompanyId[$companyTypeId])) {
            $rows = (new Query())
                ->select([
                    'id',
                    'companyTypeId',
                    'siteId',
                    'uriFormat',
                    'hasUrls',
                    'template'
                ])
                ->from(Table::CM_COMPANYTYPES_SITES)
                ->where(['companytypeId' => $companyTypeId])
                ->all();

            $this->_siteSettingsByCompanyId[$companyTypeId] = [];

            foreach ($rows as $row) {
                $this->_siteSettingsByCompanyId[$companyTypeId][] = new CompanyTypeSite($row);
            }
        }

        return $this->_siteSettingsByCompanyId[$companyTypeId];
    }

    /**
     * Returns a company type by its UID.
     *
     * @param string $uid the company type's UID
     * @return CompanyType|null either the company type or `null`
     */
    public function getCompanyTypeByUid(string $uid)
    {
        return ArrayHelper::firstWhere($this->getAllComapnyTypes(), 'uid', $uid, true);
    }


    /**
     * Handle a company type change.
     *
     * @param ConfigEvent $event
     * @return void
     * @throws Throwable if reasons
     */
    public function handleChangedCompanyType(ConfigEvent $event)
    {
        $companyTypeUid = $event->tokenMatches[0];
        $data = $event->newValue;
        $shouldResaveCompanies = false;

        // Make sure fields and sites are processed
        ProjectConfigHelper::ensureAllSitesProcessed();
        ProjectConfigHelper::ensureAllFieldsProcessed();

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            $siteData = $data['siteSettings'];

            // Basic data
            $companyTypeRecord = $this->_getCompanyTypeRecord($companyTypeUid);
            $isNewCompanyType = $companyTypeRecord->getIsNewRecord();
            $fieldsService = Craft::$app->getFields();

            $companyTypeRecord->uid = $companyTypeUid;
            $companyTypeRecord->name = $data['name'];
            $companyTypeRecord->handle = $data['handle'];

            $companyTypeRecord->titleFormat = $data['titleFormat'] ?? '{company.title}';
            $companyTypeRecord->hasTitleField = $data['hasTitleField'];

            if (!empty($data['companyFieldLayouts']) && !empty($config = reset($data['companyFieldLayouts']))) {
                // Save the main field layout
                $layout = FieldLayout::createFromConfig($config);
                $layout->id = $companyTypeRecord->fieldLayoutId;
                $layout->type = \percipiolondon\companymanagement\elements\Company::class;
                $layout->uid = key($data['companyFieldLayouts']);
                $fieldsService->saveLayout($layout);
                $companyTypeRecord->fieldLayoutId = $layout->id;
            } else if ($companyTypeRecord->fieldLayoutId) {
                // Delete the main field layout
                $fieldsService->deleteLayoutById($companyTypeRecord->fieldLayoutId);
                $companyTypeRecord->fieldLayoutId = null;
            }

//            $companyTypeRecord->fieldLayoutId = $data['fieldLayoutId'];

            $companyTypeRecord->save(false);

            // Install default fields for the layout
            // -----------------------------------------------------------------
//            if($isNewCompanyType) {
//                $this->_installCompanyFields($companyTypeRecord->fieldLayoutId);
//            }

            // Update the site settings
            // -----------------------------------------------------------------

            $sitesNowWithoutUrls = [];
            $sitesWithNewUriFormats = [];
            $allOldSiteSettingsRecords = [];

            if (!$isNewCompanyType) {
                // Get the old product type site settings
                $allOldSiteSettingsRecords = CompanyTypeSiteRecord::find()
                    ->where(['companyTypeId' => $companyTypeRecord->id])
                    ->indexBy('siteId')
                    ->all();
            }

            $siteIdMap = Db::idsByUids('{{%sites}}', array_keys($siteData));

            /** @var CompanyTypeSite $siteSettings */
            foreach ($siteData as $siteUid => $siteSettings) {
                $siteId = $siteIdMap[$siteUid];

                // Was this already selected?
                if (!$isNewCompanyType && isset($allOldSiteSettingsRecords[$siteId])) {
                    $siteSettingsRecord = $allOldSiteSettingsRecords[$siteId];
                } else {
                    $siteSettingsRecord = new CompanyTypeSiteRecord();
                    $siteSettingsRecord->companyTypeId = $companyTypeRecord->id;
                    $siteSettingsRecord->siteId = $siteId;
                }

                if ($siteSettingsRecord->hasUrls = $siteSettings['hasUrls']) {
                    $siteSettingsRecord->uriFormat = $siteSettings['uriFormat'];
                    $siteSettingsRecord->template = $siteSettings['template'];
                } else {
                    $siteSettingsRecord->uriFormat = null;
                    $siteSettingsRecord->template = null;
                }

                if (!$siteSettingsRecord->getIsNewRecord()) {
                    // Did it used to have URLs, but not anymore?
                    if ($siteSettingsRecord->isAttributeChanged('hasUrls', false) && !$siteSettings['hasUrls']) {
                        $sitesNowWithoutUrls[] = $siteId;
                    }

                    // Does it have URLs, and has its URI format changed?
                    if ($siteSettings['hasUrls'] && $siteSettingsRecord->isAttributeChanged('uriFormat', false)) {
                        $sitesWithNewUriFormats[] = $siteId;
                    }
                }

                $siteSettingsRecord->save(false);
            }

            if (!$isNewCompanyType) {
                // Drop any site settings that are no longer being used, as well as the associated product/element
                // site rows
                $affectedSiteUids = array_keys($siteData);

                /** @noinspection PhpUndefinedVariableInspection */
                foreach ($allOldSiteSettingsRecords as $siteId => $siteSettingsRecord) {
                    $siteUid = array_search($siteId, $siteIdMap, false);
                    if (!in_array($siteUid, $affectedSiteUids, false)) {
                        $siteSettingsRecord->delete();
                    }
                }
            }

            // Finally, deal with the existing companies...
            // -----------------------------------------------------------------

            if (!$isNewCompanyType) {
                $companyIds = Company::find()
                    ->typeId($companyTypeRecord->id)
                    ->anyStatus()
                    ->limit(null)
                    ->ids();

                // Are there any sites left?
                if (!empty($siteData)) {
                    // Drop the old company URIs for any site settings that don't have URLs
                    if (!empty($sitesNowWithoutUrls)) {
                        $db->createCommand()
                            ->update(
                                '{{%elements_sites}}',
                                ['uri' => null],
                                [
                                    'elementId' => $companyIds,
                                    'siteId' => $sitesNowWithoutUrls,
                                ])
                            ->execute();
                    } else if (!empty($sitesWithNewUriFormats)) {
                        foreach ($companyIds as $companyId) {
                            App::maxPowerCaptain();

                            // Loop through each of the changed sites and update all of the companies’ slugs and
                            // URIs
                            foreach ($sitesWithNewUriFormats as $siteId) {
                                $company = Company::find()
                                    ->id($companyId)
                                    ->siteId($siteId)
                                    ->anyStatus()
                                    ->one();

                                if ($company) {
                                    Craft::$app->getElements()->updateElementSlugAndUri($company, false, false);
                                }
                            }
                        }
                    }
                }
            }

            $transaction->commit();

            if ($shouldResaveCompanies) {
                Craft::$app->getQueue()->push(new ResaveElements([
                    'elementType' => Company::class,
                    'criteria' => [
                        'siteId' => '*',
                        'status' => null,
                        'typeId' => $companyTypeRecord->id,
                        'enabledForSite' => false
                    ]
                ]));
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Clear caches
        $this->_allCompanyTypeIds = null;
        $this->_editableCompanyTypeIds = null;
        $this->_fetchedAllCompanyTypes = false;
        unset(
            $this->_companyTypesById[$companyTypeRecord->id],
            $this->_companyTypesByHandle[$companyTypeRecord->handle],
            $this->_siteSettingsByCompanyId[$companyTypeRecord->id]
        );

        // Fire an 'afterSaveCompanyType' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_COMPANYTYPE)) {
            $this->trigger(self::EVENT_AFTER_SAVE_COMPANYTYPE, new CompanyTypeEvent([
                'companyType' => $this->getCompanyTypeById($companyTypeRecord->id),
                'isNew' => empty($this->_savingProductTypes[$companyTypeUid]),
            ]));
        }
    }

    /**
     * Saves a company type.
     *
     * @param CompanyType $companyType The company type model.
     * @param bool $runValidation If validation should be ran.
     * @return bool Whether the company type was saved successfully.
     * @throws \Throwable if reasons
     */
    public function saveCompanyType(CompanyType $companyType, bool $runValidation = true): bool
    {
        $isNewCompanyType = !$companyType->id;

        // Fire a 'beforeSaveCompanyType' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_COMPANYTYPE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_COMPANYTYPE, new CompanyTypeEvent([
                'companyType' => $companyType,
                'isNew' => $isNewCompanyType,
            ]));
        }

        if ($runValidation && !$companyType->validate()) {
            Craft::info('Company type not saved due to validation error.', __METHOD__);
            return false;
        }

        if ($isNewCompanyType) {
            $companyType->uid = StringHelper::UUID();
        } else {
            /** @var CompanyTypeRecord|null $existingCompanyTypeRecord */
            $existingCompanyTypeRecord = CompanyTypeRecord::find()
                ->where(['id' => $companyType->id])
                ->one();

            if (!$existingCompanyTypeRecord) {
                throw new Exception("No company type exists with the ID '{$companyType->id}'");
            }

            $companyType->uid = $existingCompanyTypeRecord->uid;
        }

        $this->_savingCompanyTypes[$companyType->uid] = $companyType;

        // Install default fields for the layout
        // -----------------------------------------------------------------
        if($isNewCompanyType) {
            $this->_installCompanyFields($companyType->getFieldLayout()->id);
        }

        $projectConfig = Craft::$app->getProjectConfig();
        $configData = [
            'name' => $companyType->name,
            'handle' => $companyType->handle,
            'hasTitleField' => $companyType->hasTitleField,
            'titleFormat' => $companyType->titleFormat,
            'companyFieldLayouts' => [],
//            'fieldLayoutId' => $companyType->getFieldLayout()->id,
            'uid' => $companyType->uid,
            'siteSettings' => [],
        ];

        $generateLayoutConfig = function(FieldLayout $fieldLayout): array {
            $fieldLayoutConfig = $fieldLayout->getConfig();

            if ($fieldLayoutConfig) {
                if (empty($fieldLayout->id)) {
                    $layoutUid = StringHelper::UUID();
                    $fieldLayout->uid = $layoutUid;
                } else {
                    $layoutUid = Db::uidById('{{%fieldlayouts}}', $fieldLayout->id);
                }

                return [$layoutUid => $fieldLayoutConfig];
            }

            return [];
        };

        $configData['companyFieldLayouts'] = $generateLayoutConfig($companyType->getCompanyFieldLayout());


        // Get the site settings
        $allSiteSettings = $companyType->getSiteSettings();

        // Make sure they're all there
        foreach (Craft::$app->getSites()->getAllSiteIds() as $siteId) {
            if (!isset($allSiteSettings[$siteId])) {
                throw new Exception('Tried to save a company type that is missing site settings');
            }
        }

        foreach ($allSiteSettings as $siteId => $settings) {
            $siteUid = Db::uidById(CraftTable::SITES, $siteId);
            $configData['siteSettings'][$siteUid] = [
                'hasUrls' => $settings['hasUrls'],
                'uriFormat' => $settings['uriFormat'],
                'template' => $settings['template'],
            ];
        }

        $configPath = self::CONFIG_COMPANYTYPES_KEY . '.' . $companyType->uid;
        $projectConfig->set($configPath, $configData);

        if ($isNewCompanyType) {
            $companyType->id = Db::idByUid(Table::CM_COMPANYTYPES, $companyType->uid);
        }

        return true;
    }

    public function uninstallFields()
    {
        $fieldGroups = Craft::$app->fields->getAllGroups();
        foreach($fieldGroups as $fieldGroup) {
            if('Company Fields' === $fieldGroup->name) {
                Craft::$app->fields->deleteGroupById($fieldGroup->id);
            }
        }
    }

    private function _installCompanyFields($layoutId)
    {
        $fieldGroup = $this->_createFieldGroup();
        $fields = $this->_createFields($fieldGroup);

        $layout = Craft::$app->getFields()->getLayoutById($layoutId);
        if(!$layout){
            return false;
        }

        $layout->tabs = [
            [
                'name' => 'Company Reference',
                'sortOrder' => 1,
                'elements' => [
                    new CustomField($fields['cmRegNr'], ['required' => true]),
                    new CustomField($fields['cmPaye']),
                    new CustomField($fields['cmAccOffRef']),
                    new CustomField($fields['cmTaxRef']),
                ]
            ],
            [
                'name' => 'Company Dates',
                'sortOrder' => 2,
                'elements' => [
                    new CustomField($fields['cmAccYearEndDate']),
                    new CustomField($fields['cmBusinessCat']),
                    new CustomField($fields['cmPayrollDate']),
                    new CustomField($fields['cmStagingDate']),
                    new CustomField($fields['cmPensionDate']),
                    new CustomField($fields['cmPayCutOffDate']),
                ]
            ],
            [
                'name' => 'Pension Scheme',
                'sortOrder' => 3,
                'elements' => [
                    new CustomField($fields['cmEmployeeContribution']),
                    new CustomField($fields['cmEmployerContribution']),
                    new CustomField($fields['cmTaxRelief']),
                    new CustomField($fields['cmCalculateQualifyingEarning']),
                    new CustomField($fields['cmPayPeriodEnd']),
                ]
            ],
            [
                'name' => 'Connections',
                'sortOrder' => 4,
                'elements' => [
                    new CustomField($fields['cmXeroApi']),
                    new CustomField($fields['cmBlackhawkApi']),
                ]
            ],
        ];
        Craft::$app->fields->saveLayout($layout);
    }

    private function _createFieldGroup(): FieldGroup
    {
        // Make a field group
        $fieldGroups = Craft::$app->fields->getAllGroups();
        $companyFieldGroup = null;
        foreach($fieldGroups as $fieldGroup) {
            if('Company Fields' === $fieldGroup->name) {
                $companyFieldGroup = $fieldGroup;
            }
        }

        if(null === $companyFieldGroup) {
            $groupModel = new FieldGroup();
            $groupModel->name = 'Company Fields';
            Craft::$app->fields->saveGroup($groupModel);
            $fieldGroups = Craft::$app->fields->getAllGroups();

            foreach($fieldGroups as $fieldGroup) {
                if('Company Fields' === $fieldGroup->name) {
                    $companyFieldGroup = $fieldGroup;
                }
            }
        }

        return $companyFieldGroup;
    }

    private function _createFields($fieldGroup)
    {
        $fields = [];

        $fieldsService = Craft::$app->getFields();

        $field = $fieldsService->getFieldByHandle('cmRegNr');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "Company Registration Number",
                'handle' => "cmRegNr",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmRegNr'] = $field;


        $field = $fieldsService->getFieldByHandle('cmPaye');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "PAYE Reference",
                'handle' => "cmPaye",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmPaye'] = $field;

        $field = $fieldsService->getFieldByHandle('cmAccOffRef');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "Accounts office reference",
                'handle' => "cmAccOffRef",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmAccOffRef'] = $field;

        $field = $fieldsService->getFieldByHandle('cmTaxRef');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "Corporation Tax Reference",
                'handle' => "cmTaxRef",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmTaxRef'] = $field;

        $field = $fieldsService->getFieldByHandle('cmAccYearEndDate');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => Date::class,
                'uid' => StringHelper::UUID(),
                'name' => "Accounting Year End Date",
                'handle' => "cmAccYearEndDate",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmAccYearEndDate'] = $field;

        $field = $fieldsService->getFieldByHandle('cmPayrollDate');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => TimeloopField::class,
                'uid' => StringHelper::UUID(),
                'name' => "Payroll Date",
                'handle' => "cmPayrollDate",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmPayrollDate'] = $field;

        $field = $fieldsService->getFieldByHandle('cmBusinessCat');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => Dropdown::class,
                'uid' => StringHelper::UUID(),
                'name' => "Business Category",
                'handle' => "cmBusinessCat",
                'options' => [
                    [
                        "value" => 2,
                        "label" => "Administration"
                    ],
                    [
                        "value" => 3,
                        "label" => "Agriculture"
                    ],
                    [
                        "value" => 4,
                        "label" => "Apparel & Fashion"
                    ],
                    [
                        "value" => 5,
                        "label" => "Architecture & Planning"
                    ],
                    [
                        "value" => 6,
                        "label" => "Arts & Crafts"
                    ],
                    [
                        "value" => 7,
                        "label" => "Automotive"
                    ],
                    [
                        "value" => 8,
                        "label" => "Aviation"
                    ],
                    [
                        "value" => 9,
                        "label" => "Biotechnology"
                    ],
                    [
                        "value" => 10,
                        "label" => "Builder"
                    ],
                    [
                        "value" => 38,
                        "label" => "Business / Management Consulting"
                    ],
                    [
                        "value" => 11,
                        "label" => "Childcare"
                    ],
                    [
                        "value" => 12,
                        "label" => "Cleaning"
                    ],
                    [
                        "value" => 13,
                        "label" => "Commercial Property"
                    ],
                    [
                        "value" => 14,
                        "label" => "Communications"
                    ],
                    [
                        "value" => 15,
                        "label" => "Courier"
                    ],
                    [
                        "value" => 16,
                        "label" => "Design"
                    ],
                    [
                        "value" => 17,
                        "label" => "Driver (Taxi / Private)"
                    ],
                    [
                        "value" => 18,
                        "label" => "Education"
                    ],
                    [
                        "value" => 19,
                        "label" => "Electrician"
                    ],
                    [
                        "value" => 20,
                        "label" => "Energy"
                    ],
                    [
                        "value" => 21,
                        "label" => "Engineering"
                    ],
                    [
                        "value" => 22,
                        "label" => "Entertainment"
                    ],
                    [
                        "value" => 23,
                        "label" => "Events"
                    ],
                    [
                        "value" => 24,
                        "label" => "Film & TV"
                    ],
                    [
                        "value" => 25,
                        "label" => "Financial Services"
                    ],
                    [
                        "value" => 26,
                        "label" => "Floristry"
                    ],
                    [
                        "value" => 27,
                        "label" => "Food & Beverages"
                    ],
                    [
                        "value" => 28,
                        "label" => "Gambling & Casinos"
                    ],
                    [
                        "value" => 54,
                        "label" => "Hair & Beauty"
                    ],
                    [
                        "value" => 29,
                        "label" => "Health & Social Care"
                    ],
                    [
                        "value" => 30,
                        "label" => "Health, Wellness and Fitness"
                    ],
                    [
                        "value" => 31,
                        "label" => "Hospitality"
                    ],
                    [
                        "value" => 32,
                        "label" => "IT Contractor / Consulting"
                    ],
                    [
                        "value" => 33,
                        "label" => "Joiner"
                    ],
                    [
                        "value" => 34,
                        "label" => "Landscape Gardener"
                    ],
                    [
                        "value" => 35,
                        "label" => "Legal Services"
                    ],
                    [
                        "value" => 36,
                        "label" => "Leisure & Tourism"
                    ],
                    [
                        "value" => 37,
                        "label" => "Logistics"
                    ],
                    [
                        "value" => 39,
                        "label" => "Marketing & Advertising"
                    ],
                    [
                        "value" => 40,
                        "label" => "Music"
                    ],
                    [
                        "value" => 41,
                        "label" => "Painter and Decorator"
                    ],
                    [
                        "value" => 42,
                        "label" => "Performing Arts"
                    ],
                    [
                        "value" => 43,
                        "label" => "Photography"
                    ],
                    [
                        "value" => 44,
                        "label" => "Plasterer"
                    ],
                    [
                        "value" => 45,
                        "label" => "Plumber"
                    ],
                    [
                        "value" => 46,
                        "label" => "Property / Landlord"
                    ],
                    [
                        "value" => 47,
                        "label" => "Retail"
                    ],
                    [
                        "value" => 48,
                        "label" => "Social Clubs"
                    ],
                    [
                        "value" => 49,
                        "label" => "Software Development"
                    ],
                    [
                        "value" => 50,
                        "label" => "Utilities"
                    ],
                    [
                        "value" => 51,
                        "label" => "Vet & Pet Care"
                    ],
                    [
                        "value" => 52,
                        "label" => "Web Design"
                    ]
                ],
                'groupId' => $fieldGroup->id,

            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmBusinessCat'] = $field;

        $field = $fieldsService->getFieldByHandle('cmStagingDate');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => Date::class,
                'uid' => StringHelper::UUID(),
                'name' => "Staging Date",
                'handle' => "cmStagingDate",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmStagingDate'] = $field;

        $field = $fieldsService->getFieldByHandle('cmPensionDate');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => TimeloopField::class,
                'uid' => StringHelper::UUID(),
                'name' => "Pension Date",
                'handle' => "cmPensionDate",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmPensionDate'] = $field;

        $field = $fieldsService->getFieldByHandle('cmPayCutOffDate');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => TimeloopField::class,
                'uid' => StringHelper::UUID(),
                'name' => "Pay Cut Off Date",
                'handle' => "cmPayCutOffDate",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmPayCutOffDate'] = $field;

        $field = $fieldsService->getFieldByHandle('cmEmployeeContribution');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => Number::class,
                'uid' => StringHelper::UUID(),
                'name' => "Employee Contribution ( % )",
                'handle' => "cmEmployeeContribution",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmEmployeeContribution'] = $field;

        $field = $fieldsService->getFieldByHandle('cmEmployerContribution');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => Number::class,
                'uid' => StringHelper::UUID(),
                'name' => "Employer Contribution ( % )",
                'handle' => "cmEmployerContribution",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmEmployerContribution'] = $field;

        $field = $fieldsService->getFieldByHandle('cmTaxRelief');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => Dropdown::class,
                'uid' => StringHelper::UUID(),
                'name' => "Tax Relief Method",
                'handle' => "cmTaxRelief",
                'options' => [
                    [
                        "value" => "salary",
                        "label" => "Salaray Sacrifice"
                    ],
                    [
                        "value" => "relief",
                        "label" => "Relief at Source"
                    ],
                    [
                        "value" => "net",
                        "label" => "Net Pay"
                    ],
                ],
                'groupId' => $fieldGroup->id,

            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmTaxRelief'] = $field;

        $field = $fieldsService->getFieldByHandle('cmCalculateQualifyingEarning');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => Lightswitch::class,
                'uid' => StringHelper::UUID(),
                'name' => "Calculate on Qualifying Earning",
                'handle' => "cmCalculateQualifyingEarning",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmCalculateQualifyingEarning'] = $field;

        $field = $fieldsService->getFieldByHandle('cmPayPeriodEnd');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => Number::class,
                'uid' => StringHelper::UUID(),
                'name' => "Day in Month Following Pay Period End ( 1 - 31 )",
                'handle' => "cmPayPeriodEnd",
                'groupId' => $fieldGroup->id,
                'settings' => [
                    'min' => 1,
                    'max' => 31
                ]

            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmPayPeriodEnd'] = $field;

        $field = $fieldsService->getFieldByHandle('cmXeroApi');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "XERO payroll API connect",
                'handle' => "cmXeroApi",
                'groupId' => $fieldGroup->id,
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmXeroApi'] = $field;

        $field = $fieldsService->getFieldByHandle('cmBlackhawkApi');
        if(!$field) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "Blackhawk network API connect",
                'handle' => "cmBlackhawkApi",
                'groupId' => $fieldGroup->id,
            ]);
            $fieldsService->saveField($field);
        }
        $fields['cmBlackhawkApi'] = $field;

        return $fields;
    }

    private function _createCompanyTypeQuery(): Query
    {
        return (new Query())
            ->select([
                'companymanagement_companytypes.id',
                'companymanagement_companytypes.fieldLayoutId',
                'companymanagement_companytypes.name',
                'companymanagement_companytypes.handle',
                'companymanagement_companytypes.titleFormat',
                'companymanagement_companytypes.uid',
            ])
            ->from([Table::CM_COMPANYTYPES]);
    }

    private function _memoizeCompanyType(CompanyType  $companyType)
    {
        $this->_companyTypesById[$companyType->id] = $companyType;
        $this->_companyTypesByHandle[$companyType->handle] = $companyType;
    }

    private function _getCompanyTypeRecord(string $uid): CompanyTypeRecord
    {
        // @TODO: change to ['uid' => $uid]
        if ($companyType = CompanyTypeRecord::findOne(['handle' => "default"])) {
            return $companyType;
        }

        return new CompanyTypeRecord();
    }
}
