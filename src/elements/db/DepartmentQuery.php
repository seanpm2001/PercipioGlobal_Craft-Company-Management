<?php

namespace percipiolondon\companymanagement\elements\db;

use craft\elements\db\ElementQuery;
use percipiolondon\companymanagement\elements\Department;

class DepartmentQuery extends ElementQuery
{
    public $slug;
    public $companyId;

    public function __construct($elementType, array $config = [])
    {
        // Default status
        if (!isset($config['status'])) {
            $config['status'] = [
                Department::STATUS_ENABLED,
            ];
        }

        parent::__construct($elementType, $config);
    }

    public function slug($value)
    {
        $this->slug = $value;
        return $this;
    }

    public function companyId($value)
    {
        $this->companyId = $value;
        return $this;
    }

    public function status($value)
    {
        return parent::status($value);
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('companymanagement_departments');

        $this->query->select([
            'companymanagement_departments.companyId',
            'companymanagement_departments.slug',
            'companymanagement_departments.title',
        ]);

        return parent::beforePrepare();
    }
}
