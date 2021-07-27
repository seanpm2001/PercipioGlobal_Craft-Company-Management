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

use craft\base\Element;
use DateTime;
use craft\db\ActiveRecord;
use percipiolondon\companymanagement\db\Table;
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
 * Employee record.
 *
 * @property int $id
 * @property int $userId
 * @property int $companyId
 * @property \DateTime $joinDate
 * @property \DateTime $endDate
 * @property \DateTime $probationPeriod
 * @property \DateTime $dateOfBirth
 * @property string $slug
 * @property string $title
 * @property string $noticePeriod
 * @property string $firstName
 * @property string $middleName
 * @property string $lastName
 * @property string $knownAs
 * @property string $reference
 * @property string $gender
 * @property string $nationality
 * @property string $nameTitle
 * @property string $ethnicity
 * @property string $maritalStatus
 * @property string $nationalInsuranceNumber
 * @property string $drivingLicense
 * @property string $personalEmail
 * @property string $personalMobile
 * @property string $personalPhone
 * @property string $address
 * @property string $department
 * @property string $contractType
 * @property string $directDialingIn
 * @property string $workExtension
 * @property string $workMobile
 * @property string $jobTitle
 * @property string $companyEmail
 *
 *
 * @package Company Management
 *
 */
class Employee extends ActiveRecord
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
        return Table::CM_EMPLOYEES;
    }

    /**
     * Returns the employee’s element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}
