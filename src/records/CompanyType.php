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

use percipiolondon\companymanagement\db\Table;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use yii\db\ActiveQueryInterface;


/**
 * Company Type Record
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
 * Company type record.
 *
 * @property FieldLayout $fieldLayout
 * @property int $fieldLayoutId
 * @property string $handle
 * @property int $id
 * @property string $name
 * @property string $titleFormat
 * @property bool $hasDimensions
 * @property bool $hasTitleField
 * @property string $companyTitleFormat
 *
 *
 * @package Company Management
 *
 */
class CompanyType extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * Declares the name of the database table associated with this AR class.
     * By default this method returns the class name as the table name by calling [[Inflector::camel2id()]]
     * with prefix [[Connection::tablePrefix]]. For example if [[Connection::tablePrefix]] is `tbl_`,
     * `Customer` becomes `tbl_customer`, and `OrderItem` becomes `tbl_order_item`. You may override this method
     * if the table is not named after this convention.
     *
     * By convention, tables created by plugins should be prefixed with the plugin
     * name and an underscore.
     *
     * @return string the table name
     */
    public static function tableName()
    {
        return Table::CM_COMPANYTYPES;
    }

    /**
     * Returns the company type's company
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getCompany(): ActiveQueryInterface
    {
        return $this->hasOne(Company::class, ['id' => 'companyId']);
    }

    /**
     * Return the company type's fieldLayout.
     *
     * @return ActiveQueryInterface The relational query object
     */
    public function getFieldLayout(): ActiveQueryInterface
    {
        return $this->hasOne(FieldLayout::class, ['id' => 'fieldLayoutId']);
    }
}
