<?php
/**
 * Company Management plugin for Craft CMS 3.x
 *
 * A plugin to setup companies
 *
 * @link      http://percipio.london/
 * @copyright Copyright (c) 2021 Percipio
 */

namespace percipiolondon\companymanagement\migrations;

use craft\fields\PlainText;
use craft\models\FieldGroup;
use percipiolondon\companymanagement\CompanyManagement;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use yii\base\BaseObject;

/**
 * Company Management Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Percipio
 * @package   CompanyManagement
 * @since     0.1.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

    // companymanagement_company table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%companymanagement_company}}');

        $currentDate = new \DateTime('NOW');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%companymanagement_company}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                // Custom columns in the table
                    'siteId' => $this->integer()->notNull()->defaultValue(1),
                    'info' => $this->string()->notNull()->defaultValue(''),
                    'name' => $this->string()->notNull()->defaultValue(''),
                    'shortName' => $this->string()->notNull()->defaultValue(''),
                    'address' => $this->string()->notNull()->defaultValue(''),
                    'town' => $this->string()->notNull()->defaultValue(''),
                    'postcode' => $this->string()->notNull()->defaultValue(''),
                    'registerNumber' => $this->string()->notNull()->defaultValue(''),
                    'payeReference' => $this->string()->notNull()->defaultValue(''),
                    'accountsOfficeReference' => $this->string()->notNull()->defaultValue(''),
                    'taxReference' => $this->string()->notNull()->defaultValue(''),
                    'website' => $this->string()->notNull()->defaultValue(''),
                    'logo' => $this->integer(),
                    'contactName' => $this->string()->notNull()->defaultValue(''),
                    'contactEmail' => $this->string()->notNull()->defaultValue(''),
                    'contactRegistrationNumber' => $this->string()->notNull()->defaultValue(''),
                    'contactPhone' => $this->string(),
                    'contactBirthday' => $this->dateTime(),
                    'userId' => $this->integer()->notNull(),
                ]
            );
        }

//        $this->execute('alter table {{%companymanagement_company}} modify column id int AUTO_INCREMENT');

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
    // companymanagement_company table
        $this->createIndex(null,'{{%companymanagement_company}}',['id'], true);
//        $this->createIndex(null,'{{%companymanagement_company}}',['registerNumber'], true);

        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
    // companymanagement_company table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%companymanagement_company}}', 'siteId'),
            '{{%companymanagement_company}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%companymanagement_company}}', 'id'),
            '{{%companymanagement_company}}',
            'id',
            '{{%elements}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%companymanagement_company}}', 'logo'),
            '{{%companymanagement_company}}',
            'logo',
            '{{%assets}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%companymanagement_company}}', 'userId'),
            '{{%companymanagement_company}}',
            'userId',
            '{{%users}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
    // companymanagement_company table
        $this->dropTableIfExists('{{%companymanagement_company}}');
    }
}
