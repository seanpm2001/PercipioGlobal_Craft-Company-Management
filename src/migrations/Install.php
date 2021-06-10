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

use Craft;
use craft\db\ActiveRecord;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;

use craft\helpers\DateTimeHelper;
use craft\helpers\ElementHelper;
use craft\helpers\MigrationHelper;
use craft\helpers\StringHelper;

use craft\queue\jobs\ResaveElements;

use craft\records\Element;
use craft\records\Element_SiteSettings;
use craft\records\FieldLayout;
use craft\records\Site;


use percipiolondon\companymanagement\db\Table;
use percipiolondon\companymanagement\elements\Company;
//use percipiolondon\companymanagement\elements\Employee;
use percipiolondon\companymanagement\models\CompanyType;


/**
 * Installation Migration
 *
 * @author Percipio Global Ltd.
 * @since 1.0.0
 */
class Install extends Migration {

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKeys();
        $this->dropTables();
        $this->dropProjectConfig():

        $this->delete(Table::ELEMENTINDEXSETTINGS, ['type' => [ Company::class ]]);
        $this->delete(Table::FIELDLAYOUTS, ['type' => [ Company::class ]]);
    }

    // Protected Functions
    // =========================================================================

    /**
     * Creates the tables for Company Management
     */

    public function createTables()
    {
        $this->createTable(Table::CM_COMPANIES, [
            'id' => $this->primaryKey(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CM_COMPANYTYPES, [
            'id' => $this->primaryKey(),
            'fieldLayoutId' => $this->integer(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
        ]);

        $this->createTable(Table::CM_USERS, [
            'id' => $this->integer()->notNull(),
            'companyId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);
    }

    /**
     * Drop the tables
     */
    public function dropTables()
    {
        $this->dropTableIfExists(Table::CM_COMPANIES);

        return null;
    }

    /**
     * Deletes the project config entry.
     */
    public function dropProjectConfig()
    {
        Craft::$app->projectConfig->remove('companies');
    }

    /**
     * Creates the indexes.
     */
    public function createIndexes()
    {
        $this->createIndex(null, Table::CM_COMPANIES, 'typeId', false);
        $this->createIndex(null, Table::CM_COMPANYTYPES, 'handle', true);
        $this->createIndex(null, Table::CM_COMPANYTYPES, 'fieldLayoutId', true);
        $this->createIndex(null, Table::CM_USERS, 'companyId', true);
    }

    /**
     * Adds the foreign keys.
     */
    public function addForeignKeys()
    {

    }
}