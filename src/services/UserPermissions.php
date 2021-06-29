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

use percipiolondon\companymanagement\CompanyManagement;

use Craft;
use craft\base\Component;
use percipiolondon\companymanagement\records\Permission as PermissionRecord;
use percipiolondon\companymanagement\records\UserPermission as UserPermissionRecord;
use percipiolondon\companymanagement\models\UserPermissions as UserPermissionModel;

/**
 * UserPermissions Service
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
class UserPermissions extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @param $permissions
     * @param $userId
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     *
     * Save a permission set of a user to the database
     */
    public function savePermissions($permissions, $userId)
    {
        foreach ($permissions as $permission) {

            $record = UserPermissionRecord::findOne(['permissionId' => $permission['id'], 'userId' => $userId]);

            if(!$record) {

                $userPermission = new UserPermissionModel();
                $userPermission->userId = $userId;
                $userPermission->permissionId = $permission['id'];

                $userPermission->validate();

                $record = new UserPermissionRecord();
                $record->permissionId = $userPermission->permissionId;
                $record->userId = $userPermission->userId;

                $record->save(false);

            }
        }
    }

    public function updatePermissions($updatedPermissions, $userId)
    {
        $permissions = PermissionRecord::find()->asArray()->all();
        $permissionsIds = [];

        $updatedPermissions = '' === $updatedPermissions ? [] : $updatedPermissions;

        foreach( $permissions as $permission ) {
            $permissionsIds[] = $permission['id'];
        }

        $userPermissions = UserPermissionRecord::findAll(['userId' => $userId]);
        $userPermissionsIds = [];

        foreach ($userPermissions as $permission ) {
            $userPermissionsIds[] = $permission->permissionId;
        }

        $permissionsToSave = [];

        foreach( $permissionsIds as $permission) {

            if(in_array($permission, $userPermissionsIds) && !in_array($permission, $updatedPermissions)) {
                // Delete
                $record = UserPermissionRecord::findOne(['permissionId' => $permission, 'userId' => $userId]);
                $record->delete();

            } else if (!in_array($permission, $userPermissionsIds) && in_array($permission, $updatedPermissions)) {
                // Add
                $permissionsToSave[] = ['id' => $permission];
            }
        }

        $this->savePermissions($permissionsToSave, $userId);
    }

    public function addEditUserPermissionCustomFieldTab(array &$context)
    {
        $context['tabs']['companyManagementPermissions'] = [
            'label' => Craft::t('company-management', 'Company Management Permissions'),
            'url' => '#companyManagementPermissions'
        ];
    }

    public function addEditUserPermissionsCustomFieldContent(array &$context)
    {
        $user = $context['user'] ?? null;
        $permissions = PermissionRecord::find()->asArray()->all();
        $userPermissions = $user ? UserPermissionRecord::findAll(['userId' => $user->id]) : [];

        $permissionOptions = [];
        foreach($permissions as $permission) {
            $permissionOptions[$permission['id']] = $permission['name'];
        }

        return Craft::$app->getView()->renderTemplate('company-management/_includes/_editUserPermissionsTab', [
            'user' => $user,
            'permissions' => $permissionOptions,
            'userPermissions' => $userPermissions,
        ]);
    }
}
