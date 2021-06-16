<?php

namespace percipiolondon\companymanagement\services;

use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use yii\base\Component;
use craft\db\Table as CraftTable;
use percipiolondon\companymanagement\db\Table;
use percipiolondon\companymanagement\models\CompanyTypeSite;
use percipiolondon\companymanagement\models\CompanyType;
use percipiolondon\companymanagement\records\CompanyType as CompanyTypeRecord;
use yii\base\Exception;

class CompanyTypes extends Component
{
    const CONFIG_COMPANYTYPES_KEY = 'companymanagement_companytypes.companyTypes';

    /**
     * @var bool
     */
    private $_fetchedAllCompanyTypes = false;

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

//        $this->_savingCompanyTypes[$companyType->uid] = $companyType;

        $projectConfig = Craft::$app->getProjectConfig();
        $configData = [
            'name' => $companyType->name,
            'handle' => $companyType->handle,
            'hasTitleField' => $companyType->hasTitleField,
            'titleFormat' => $companyType->titleFormat,
            'uid' => $companyType->uid,
            'siteSettings' => []
        ];

        function(FieldLayout $fieldLayout): array {
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

        //Craft::dd($companyType);

        return true;
    }

    private function _createCompanyTypeQuery(): Query
    {
         return (new Query())
             ->select([
                 'companyTypes.id',
                 'companyTypes.fieldLayoutId',
                 'companyTypes.name',
                 'companyTypes.handle',
                 'companyTypes.titleFormat',
             ])
             ->from([Table::CM_COMPANYTYPES]);
    }

    private function _memorizeCompanytype(CompanyType  $companyType)
    {
        $this->_companyTypesById[$companyType->id] = $companyType;
        $this->_companyTypesByHandle[$companyType->handle] = $companyType;
    }
}