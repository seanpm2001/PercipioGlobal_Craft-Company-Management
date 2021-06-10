<?php
/**
 * Company Management plugin for Craft CMS 3.x
 *
 * A plugin to setup companies
 *
 * @link      http://percipio.london/
 * @copyright Copyright (c) 2021 Percipio
 */

namespace percipiolondon\companymanagement\records;

use percipiolondon\companymanagement\CompanyManagement;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\db\Table;
use yii\db\ActiveQueryInterface;

/**
 * Company Record
 *
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * Active Record implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
 * The premise behind Active Record is that an individual [[ActiveRecord]] object is associated with a specific
 * row in a database table. The object's attributes are mapped to the columns of the corresponding table.
 * Referencing an Active Record attribute is equivalent to accessing the corresponding table column for that record.
 *
 * http://www.yiiframework.com/doc-2.0/guide-db-active-record.html
 *
 * @author    Percipio
 * @package   CompanyManagement
 * @since     1.0.0
 */


/**
 * Class CompanyType record.
 *
 * @property int $id ID
 * @property int $companyId Company ID
 * @property int $fieldLayoutId Field layout ID
 * @property string $name Name
 * @property string $handle Handle
 * @property bool $hasTitleField Has title field
 * @property string $titleTranslationMethod Title translation method
 * @property string|null $titleTranslationKeyFormat Title translation key format
 * @property string|null $titleFormat Title format
 * @property int $sortOrder Sort order
 * @property Company $compny Company
 * @property FieldLayout $fieldLayout Field layout
 * @author Percipio Global Ltd. <support@percipio.london>
 * @since 3.0.0
 */

class CompanyType extends ActiveRecord
{
    use SoftDeleteTrait;

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return Table::COMPANYTYPES;
    }

    /**
     * Returns the entry type’s fieldLayout.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFieldLayout(): ActiveQueryInterface
    {
        return $this->hasOne(FieldLayout::class, ['id' => 'fieldLayoutId']);
    }
}
