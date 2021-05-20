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

use Craft;
use DateTime;
use craft\db\ActiveRecord;

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
 * @since     0.1.0
 */

/**
 * Product record.
 *
 * @property DateTime postDate
 * @property DateTime expiryDate
 * @property string name
 * @property string info
 * @property string shortName
 * @property string address
 * @property string town
 * @property string postcode
 * @property string registerNumber
 * @property string payeReference
 * @property string accountsOfficeReference
 * @property string taxReference
 * @property string website
 * @property string logo
 * @property string contactName
 * @property string contactEmail
 * @property string contactRegistrationNumber
 * @property string contactPhone
 * @property string contactBirthday
 * @property int id
 * @property int siteId
 *
 */
class Company extends ActiveRecord
{

    public $postDate;
    public $expiryDate;
    public $name;
    public $info;
    public $shortName;
    public $address;
    public $town;
    public $postcode;
    public $registerNumber;
    public $payeReference;
    public $accountsOfficeReference;
    public $taxReference;
    public $website;
    public $logo;
    public $contactName;
    public $contactEmail;
    public $contactRegistrationNumber;
    public $contactPhone;
    public $contactBirthday;
    public $id;
    public $siteId;


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
        return '{{%companymanagement_company}}';
    }
}
