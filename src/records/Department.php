<?php

namespace percipiolondon\companymanagement\records;

use craft\base\Element;
use percipiolondon\companymanagement\db\Table;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;

/**
 * Employee record.
 *
 * @property int $id
 * @property int $companyId
 * @property string $slug
 * @property string $title
 *
 */

class Department extends ActiveRecord
{
    public static function tableName(): string
    {
        return Table::CM_DEPARTMENTS;
    }

    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}
