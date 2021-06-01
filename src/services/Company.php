<?php
/**
 * Company Management plugin for Craft CMS 3.x
 *
 * A plugin to setup companies
 *
 * @link      http://percipio.london/
 * @copyright Copyright (c) 2021 Percipio
 */

namespace percipiolondon\companymanagement\services;

use craft\fields\Assets;
use craft\fields\Date;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\models\FieldGroup;
use percipiolondon\companymanagement\CompanyManagement;

use Craft;
use craft\base\Component;
use yii\base\BaseObject;

/**
 * Company Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Percipio
 * @package   CompanyManagement
 * @since     0.1.0
 */
class Company extends Component
{
    // Public Methods
    // =========================================================================

    public function getCompanyById(int $id)
    {
        return Craft::$app->getElements()->getElementById($id, \percipiolondon\companymanagement\elements\Company::class);
    }

//    public function installCompanyUserFields()
//    {
//        // Create field group
//        $companyFieldGroup = $this->_createFieldGroup();
//
//        // Create fields
//        $this->_createFields($companyFieldGroup);
//
//        // Add fields to the user model
//        $fields = $companyFieldGroup->getFields();
//        $layout = Craft::$app->fields->getLayoutByType('craft/elements/User');
//        $layout->setFields($fields);
//    }

//    public function uninstallCompanyUserFields()
//    {
//        $fieldGroups = Craft::$app->fields->getAllGroups();
//        foreach($fieldGroups as $fieldGroup) {
//            if('Company User Fields' === $fieldGroup->name) {
//                Craft::$app->fields->deleteGroupById($fieldGroup->id);
//            }
//        }
//    }

    public function addEditUserCustomFieldTab(array &$context)
    {
        $context['tabs']['companyManagement'] = [
            'label' => Craft::t('company-management', 'Company Management'),
            'url' => '#companyManagement'
        ];
    }

    public function addEditUserCustomFieldContent(array &$context)
    {
        return Craft::$app->getView()->renderTemplate('company-management/_includes/_editUserTab', [
            'user' => $context['user'] ?? null,
        ]);
    }

//    private function _createFieldGroup()
//    {
//        // Make a field group
//        $fieldGroups = Craft::$app->fields->getAllGroups();
//        $companyFieldGroup = null;
//        foreach($fieldGroups as $fieldGroup) {
//            if('Company User Fields' === $fieldGroup->name) {
//                $companyFieldGroup = $fieldGroup;
//            }
//        }
//
//        if(null === $companyFieldGroup) {
//            $groupModel = new FieldGroup();
//            $groupModel->name = 'Company User Fields';
//            Craft::$app->fields->saveGroup($groupModel);
//            $fieldGroups = Craft::$app->fields->getAllGroups();
//
//            foreach($fieldGroups as $fieldGroup) {
//                if('Company User Fields' === $fieldGroup->name) {
//                    $companyFieldGroup = $fieldGroup;
//                }
//            }
//        }
//
//        return $companyFieldGroup;
//    }

//    private function _createFields($companyFieldGroup)
//    {
//        $fieldsService = Craft::$app->getFields();
//
//        // Create custom fields added to the newly created field group
//        if(!$fieldsService->getFieldByHandle('cmEmployeeStartDate')) {
//
//            //Employee start date
//            $field = $fieldsService->createField([
//                'type' => Date::class,
//                'uid' => null,
//                'name' => "Employee Start Date",
//                'handle' => "cmEmployeeStartDate",
//                'groupId' => $companyFieldGroup->id,
//            ]);
//            $fieldsService->saveField($field);
//        }
//
//        if(!$fieldsService->getFieldByHandle('cmEmployeeEndDate')) {
//
//            //Employee end date
//            $field = $fieldsService->createField([
//                'type' => Date::class,
//                'uid' => null,
//                'name' => "Employee End Date",
//                'handle' => "cmEmployeeEndDate",
//                'groupId' => $companyFieldGroup->id,
//            ]);
//            $fieldsService->saveField($field);
//        }
//
//        if(!$fieldsService->getFieldByHandle('cmBirthday')) {
//
//            //Birthday
//            $field = $fieldsService->createField([
//                'type' => Date::class,
//                'uid' => null,
//                'name' => "Birthday",
//                'handle' => "cmBirthday",
//                'groupId' => $companyFieldGroup->id,
//            ]);
//            $fieldsService->saveField($field);
//        }
//
//        if(!$fieldsService->getFieldByHandle('cmNationalInsuranceNumber')) {
//
//            //National Insurance Number
//            $field = $fieldsService->createField([
//                'type' => PlainText::class,
//                'uid' => null,
//                'name' => "National Insurance Number",
//                'handle' => "cmNationalInsuranceNumber",
//                'groupId' => $companyFieldGroup->id,
//            ]);
//            $fieldsService->saveField($field);
//        }
//
//        if(!$fieldsService->getFieldByHandle('cmGrossIncome')) {
//
//            //Gross income
//            $field = $fieldsService->createField([
//                'type' => Number::class,
//                'uid' => null,
//                'name' => "Gross Income",
//                'handle' => "cmGrossIncome",
//                'groupId' => $companyFieldGroup->id,
//            ]);
//            $fieldsService->saveField($field);
//        }
//
//        if(!$fieldsService->getFieldByHandle('cmDocuments')) {
//
//            //Documents
//            $field = $fieldsService->createField([
//                'type' => Assets::class,
//                'uid' => null,
//                'name' => "Documents",
//                'handle' => "cmDocuments",
//                'groupId' => $companyFieldGroup->id,
//            ]);
//            $fieldsService->saveField($field);
//        }
//
//        if(!$fieldsService->getFieldByHandle('cmPhone')) {
//
//            //National Insurance Number
//            $field = $fieldsService->createField([
//                'type' => PlainText::class,
//                'uid' => null,
//                'name' => "Telephone Number",
//                'handle' => "cmPhone",
//                'groupId' => $companyFieldGroup->id,
//            ]);
//            $fieldsService->saveField($field);
//        }
//    }

}
