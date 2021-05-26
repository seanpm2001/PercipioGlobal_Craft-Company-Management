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

    public function installCompanyUserFields()
    {
        // Create field group
        $companyFieldGroup = $this->_createFieldGroup();

        // Create fields
        $this->_createFields($companyFieldGroup);

        // Add fields to the user model
        $fields = $companyFieldGroup->getFields();
        $layout = Craft::$app->fields->getLayoutByType('craft/elements/User');
        $layout->setFields($fields);
    }

    private function _createFieldGroup()
    {
        // Make a field group
        $fieldGroups = Craft::$app->fields->getAllGroups();
        $companyFieldGroup = null;
        foreach($fieldGroups as $fieldGroup) {
            if('Company User Fields' === $fieldGroup->name) {
                $companyFieldGroup = $fieldGroup;
            }
        }

        if(null === $companyFieldGroup) {
            $groupModel = new FieldGroup();
            $groupModel->name = 'Company User Fields';
            Craft::$app->fields->saveGroup($groupModel);
            $fieldGroups = Craft::$app->fields->getAllGroups();

            foreach($fieldGroups as $fieldGroup) {
                if('Company User Fields' === $fieldGroup->name) {
                    $companyFieldGroup = $fieldGroup;
                }
            }
        }

        return $companyFieldGroup;
    }

    private function _createFields($companyFieldGroup)
    {
        $fieldsService = Craft::$app->getFields();

        // Create custom fields added to the newly created field group
        if(!$fieldsService->getFieldByHandle('cm_companies')) {

            // Companies field for arrays
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => null,
                'name' => "Companies",
                'handle' => "cm_companies",
                'groupId' => $companyFieldGroup->id,
            ]);
            $fieldsService->saveField($field);
        }

        if(!$fieldsService->getFieldByHandle('cm_employeeStartDate')) {

            //Employee start date
            $field = $fieldsService->createField([
                'type' => Date::class,
                'uid' => null,
                'name' => "Employee Start Date",
                'handle' => "cm_employeeStartDate",
                'groupId' => $companyFieldGroup->id,
            ]);
            $fieldsService->saveField($field);
        }

        if(!$fieldsService->getFieldByHandle('cm_employeeEndDate')) {

            //Employee end date
            $field = $fieldsService->createField([
                'type' => Date::class,
                'uid' => null,
                'name' => "Employee End Date",
                'handle' => "cm_employeeEndDate",
                'groupId' => $companyFieldGroup->id,
            ]);
            $fieldsService->saveField($field);
        }

        if(!$fieldsService->getFieldByHandle('cm_birthday')) {

            //Birthday
            $field = $fieldsService->createField([
                'type' => Date::class,
                'uid' => null,
                'name' => "Birthday",
                'handle' => "cm_birthday",
                'groupId' => $companyFieldGroup->id,
            ]);
            $fieldsService->saveField($field);
        }

        if(!$fieldsService->getFieldByHandle('cm_nationalInsuranceNumber')) {

            //National Insurance Number
            $field = $fieldsService->createField([
                'type' => PlainText::class,
                'uid' => null,
                'name' => "National Insurance Number",
                'handle' => "cm_nationalInsuranceNumber",
                'groupId' => $companyFieldGroup->id,
            ]);
            $fieldsService->saveField($field);
        }

        if(!$fieldsService->getFieldByHandle('cm_nationalInsuranceNumber')) {

            //Gross income
            $field = $fieldsService->createField([
                'type' => Number::class,
                'uid' => null,
                'name' => "Gross Income",
                'handle' => "cm_nationalInsuranceNumber",
                'groupId' => $companyFieldGroup->id,
            ]);
            $fieldsService->saveField($field);
        }

        if(!$fieldsService->getFieldByHandle('cm_documents')) {

            //Documents
            $field = $fieldsService->createField([
                'type' => Assets::class,
                'uid' => null,
                'name' => "Documents",
                'handle' => "cm_documents",
                'groupId' => $companyFieldGroup->id,
            ]);
            $fieldsService->saveField($field);
        }
    }

}
