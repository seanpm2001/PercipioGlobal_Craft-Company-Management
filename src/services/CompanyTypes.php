<?php

namespace percipiolondon\companymanagement\services;

use Craft;
use craft\fieldlayoutelements\CustomField;
use craft\db\Query;
use craft\events\ConfigEvent;
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
    const CONFIG_COMPANYTYPES_KEY = 'companymanagement_companytypes.companyTypes';
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
     * @var int[]
     */
    private $_editableCompanyTypeIds;

    /**
     * @var CompanyType[]
     */
    private $_companyTypesById;

    /**
     * @var CompanyType[]
     */
    private $_companyTypesByHandle;

    /**
     * @var CompanyTypeSite[]
     */
    private $_siteSettingsByCompanyId = [];

    /**
     * @var array interim storage for company types being saved via CP
     */
    private $_savingCompanyTypes = [];

    public function getCompanyTypeById(int $companyTypeId)
    {
        if(isset($this_companyTypeId[$companyTypeId])) {
            return $this_companyTypeId[$companyTypeId];
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

        $this->_memorizeCompanyType(new CompanyType($result));

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

        $this->_memorizeCompanytype(new CompanyType($result));

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
                ->from(Table::CM_COMAPNYTYPES_SITES)
                ->where(['companytypeId' => $companyTypeId])
                ->all();

            $this->_siteSettingsByCompanyId[$companyTypeId] = [];

            foreach ($rows as $row) {
                $this->_siteSettingsByCompanyId[$companyTypeId][] = new CompanyTypeSite($row);
            }
        }

        return $this->_siteSettingsByCompanyId[$companyTypeId];
    }

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
            $companyTypeRecord->hasDimensions = $data['hasDimensions'];

            $companyTypeRecord->titleFormat = $data['titleFormat'] ?? '{company.title}';
            $companyTypeRecord->hasTitleField = $data['hasTitleField'];

//            if (!empty($data['companyFieldLayouts']) && !empty($config = reset($data['companyFieldLayouts']))) {
//                // Save the main field layout
//                $layout = FieldLayout::createFromConfig($config);
//                $layout->id = $companyTypeRecord->fieldLayoutId;
//                $layout->type = \percipiolondon\companymanagement\elements\Company::class;
//                $layout->uid = key($data['companyFieldLayouts']);
//                $fieldsService->saveLayout($layout);
//                $companyTypeRecord->fieldLayoutId = $layout->id;
//            } else if ($companyTypeRecord->fieldLayoutId) {
//                // Delete the main field layout
//                $fieldsService->deleteLayoutById($companyTypeRecord->fieldLayoutId);
//                $companyTypeRecord->fieldLayoutId = null;
//            }

            $companyTypeRecord->fieldLayoutId = $data['fieldLayoutId'];

            // Install default fields for the layout
            // -----------------------------------------------------------------
            $this->_installCompanyFields($companyTypeRecord->fieldLayoutId);

            $companyTypeRecord->save(false);

            // Update the site settings
            // -----------------------------------------------------------------

            $sitesNowWithoutUrls = [];
            $sitesWithNewUriFormats = [];
            $allOldSiteSettingsRecords = [];

            if (!$isNewCompanyType) {
                // Get the old product type site settings
                $allOldSiteSettingsRecords = CompanyTypeSiteRecord::find()
                    ->where(['productTypeId' => $companyTypeRecord->id])
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
                    // Drop the old product URIs for any site settings that don't have URLs
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

        // Fire an 'afterCompayType' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_COMPANYTYPE)) {
            $this->trigger(self::EVENT_AFTER_SAVE_COMPANYTYPE, new CompanyTypeEvent([
                'productType' => $this->getCompanyTypeById($companyTypeRecord->id),
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

        $projectConfig = Craft::$app->getProjectConfig();
        $configData = [
            'name' => $companyType->name,
            'handle' => $companyType->handle,
            'hasTitleField' => $companyType->hasTitleField,
            'titleFormat' => $companyType->titleFormat,
            'hasDimensions' => $companyType->hasDimensions,
            'fieldLayoutId' => $companyType->getFieldLayout()->id,
            'uid' => $companyType->uid,
            'siteSettings' => []
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

    private function _installCompanyFields($layoutId)
    {
        $fieldGroup = $this->_createFieldGroup();

        $fieldsService = Craft::$app->getFields();

        $cmRegnNr = $fieldsService->getFieldByHandle('cmRegNr');
        if(!$cmRegnNr) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "Company Registration Number",
                'handle' => "cmRegNr",
                'required' => true,
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
            $cmRegnNr = $field;
        }

        $cmPaye = $fieldsService->getFieldByHandle('cmPaye');
        if(!$cmPaye) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "PAYE Reference",
                'handle' => "cmPaye",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
            $cmPaye = $field;
        }

        $cmAccOffRef = $fieldsService->getFieldByHandle('cmAccOffRef');
        if(!$cmAccOffRef) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "Accounts office reference",
                'handle' => "cmAccOffRef",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
            $cmAccOffRef = $field;
        }

        $cmTaxRef = $fieldsService->getFieldByHandle('cmTaxRef');
        if(!$cmTaxRef) {
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => StringHelper::UUID(),
                'name' => "Corporation Tax Reference",
                'handle' => "cmTaxRef",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
            $cmTaxRef = $field;
        }

        $cmPayroll = $fieldsService->getFieldByHandle('cmPayroll');
        if(!$cmPayroll) {
            $field = $fieldsService->createField([
                'type' => TimeloopField::class,
                'uid' => StringHelper::UUID(),
                'name' => "Payroll Date",
                'handle' => "cmPayroll",
                'groupId' => $fieldGroup->id
            ]);
            $fieldsService->saveField($field);
            $cmPayroll = $field;
        }

        $layout = Craft::$app->getFields()->getLayoutById($layoutId);
        $layout->tabs = [
            [
                'name' => 'Company Reference',
                'sortOrder' => 1,
                'elements' => [
                    new CustomField($cmRegnNr),
                    new CustomField($cmPaye),
                    new CustomField($cmAccOffRef),
                    new CustomField($cmTaxRef),
                    new CustomField($cmPayroll),
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

    private function _createCompanyTypeQuery(): Query
    {
         return (new Query())
             ->select([
                 'companymanagement_companytypes.id',
                 'companymanagement_companytypes.fieldLayoutId',
                 'companymanagement_companytypes.name',
                 'companymanagement_companytypes.handle',
                 'companymanagement_companytypes.titleFormat',
             ])
             ->from([Table::CM_COMPANYTYPES]);
    }

    private function _memorizeCompanytype(CompanyType  $companyType)
    {
        $this->_companyTypesById[$companyType->id] = $companyType;
        $this->_companyTypesByHandle[$companyType->handle] = $companyType;
    }

    private function _getCompanyTypeRecord(string $uid): CompanyTypeRecord
    {
        if ($companyType = CompanyTypeRecord::findOne(['uid' => $uid])) {
            return $companyType;
        }

        return new CompanyTypeRecord();
    }
}