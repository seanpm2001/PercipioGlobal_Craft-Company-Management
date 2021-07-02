<?php

namespace percipiolondon\companymanagement\models;

use craft\base\Model;

class Permissions extends Model
{
    /**
     * @var string|null Name
     */
    public $name;

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['name'], 'required'];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->name;
    }
}
