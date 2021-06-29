<?php

namespace percipiolondon\companymanagement\models;

use craft\base\Model;

class UserPermissions extends Model
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
}
