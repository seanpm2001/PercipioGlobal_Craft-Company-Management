<?php

namespace percipiolondon\companymanagement\models;

use craft\base\Model;
use percipiolondon\companymanagement\CompanyManagement;

class UserPermission extends Model
{
    /**
     * @var string|null Name
     */
    public $permissionId;
    public $userId;

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['permissionId', 'userId'], 'required'];

        return $rules;
    }

    public function can(int $companyId): bool
    {
        if($this->permissionId && $this->userId && $companyId)
        {
            return CompanyManagement::$plugin->userPermissions->applyCanParam($this->permissionId, $this->userId, $companyId);
        }

        return false;
    }
}
