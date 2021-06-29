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
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\MigrationHelper;
use craft\records\Site;
use craft\records\FieldLayout;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\db\Table;
use percipiolondon\companymanagement\elements\Company;
use craft\records\Site;
use craft\records\FieldLayout;
use craft\helpers\Db;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\db\Table;
use percipiolondon\companymanagement\elements\Company;
use percipiolondon\companymanagement\models\Permissions;
use percipiolondon\companymanagement\models\CompanyType as CompanyTypeModel;
use percipiolondon\companymanagement\models\CompanyTypeSite as CompanyTypeSiteModel;
use percipiolondon\companymanagement\records\Company as CompanyRecord;
use percipiolondon\companymanagement\records\CompanyType as CompanyTypeRecord;
use yii\base\NotSupportedException;

/**
 * Installation Migration
 *
 * @author Percipio Global Ltd.
 * @since 1.0.0
 */
class Install extends Migration {

    /**
     * @var
     */
    public $_companyFieldLayoutId;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
        $this->insertDefaultData();

        // Refresh the db schema caches
        Craft::$app->db->schema->refresh();

        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKeys();
        $this->dropTables();
        $this->dropProjectConfig();

        $this->delete(\craft\db\Table::ELEMENTINDEXSETTINGS, ['type' => [ Company::class ]]);
        $this->delete(\craft\db\Table::FIELDLAYOUTS, ['type' => [ Company::class ]]);
    }

    // Protected Functions
    // =========================================================================
    /**
     * Creates the tables for Company Management
     */
    public function createTables()
    {
        $tableSchemaCompany = Craft::$app->db->schema->getTableSchema(Table::CM_COMPANIES);
        $tableSchemaUsers = Craft::$app->db->schema->getTableSchema(Table::CM_USERS);
        $tableSchemaTypes = Craft::$app->db->schema->getTableSchema(Table::CM_COMPANYTYPES);
        $tableSchemaTypesSites = Craft::$app->db->schema->getTableSchema(Table::CM_COMPANYTYPES_SITES);
        $tableSchemaDocuments = Craft::$app->db->schema->getTableSchema(Table::CM_DOCUMENTS);
        $tableSchemaPermissions = Craft::$app->db->schema->getTableSchema(Table::CM_PERMISSIONS);
        $tableSchemaPermissionsUsers = Craft::$app->db->schema->getTableSchema(Table::CM_PERMISSIONS_USERS);

        if ($tableSchemaCompany === null) {
            $this->createTable(Table::CM_COMPANIES, [
                'id' => $this->integer()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'siteId' => $this->integer()->notNull()->defaultValue(1),
                'typeId' => $this->integer()->notNull(),
                // Custom columns in the table
                'info' => $this->string()->notNull()->defaultValue(''),
                'name' => $this->string()->notNull()->defaultValue(''),
                'slug' => $this->string()->notNull()->defaultValue(''),
                'address' => $this->string()->notNull()->defaultValue(''),
                'town' => $this->string()->notNull()->defaultValue(''),
                'postcode' => $this->string()->notNull()->defaultValue(''),
                'website' => $this->string()->notNull()->defaultValue(''),
                'logo' => $this->integer(),
                'contactFirstName' => $this->string()->notNull()->defaultValue(''),
                'contactLastName' => $this->string()->notNull()->defaultValue(''),
                'contactEmail' => $this->string()->notNull()->defaultValue(''),
                'contactRegistrationNumber' => $this->string()->notNull()->defaultValue(''),
                'contactPhone' => $this->string(),
                'contactBirthday' => $this->dateTime(),
                'userId' => $this->integer(),
                'PRIMARY KEY(id)',
            ]);
        }

        if ($tableSchemaTypes === null) {
            $this->createTable(Table::CM_COMPANYTYPES, [
                'id' => $this->primaryKey(),
                'fieldLayoutId' => $this->integer(),
                'name' => $this->string()->notNull(),
                'handle' => $this->string()->notNull(),
                'hasTitleField' => $this->boolean(),
                'titleFormat' => $this->string()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        if($tableSchemaTypesSites === null) {
            $this->createTable(Table::CM_COMPANYTYPES_SITES, [
                'id' => $this->primaryKey(),
                'companyTypeId' => $this->integer()->notNull(),
                'siteId' => $this->integer()->notNull(),
                'uriFormat' => $this->text(),
                'template' => $this->string(500),
                'hasUrls' => $this->boolean(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        if ($tableSchemaUsers === null) {
            $this->createTable(Table::CM_USERS, [
                'id' => $this->primaryKey(),
                'companyId' => $this->integer(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                // Custom columns in the table
                'userId' => $this->integer(),
                'employeeStartDate' => $this->string(),
                'employeeEndDate' => $this->string(),
                'birthday' => $this->string(),
                'nationalInsuranceNumber' => $this->string()->notNull()->defaultValue(''),
                'grossIncome' => $this->string()->defaultValue(''),
            ]);
        }

        if ($tableSchemaDocuments === null) {
            $this->createTable(Table::CM_DOCUMENTS, [
                'id' => $this->integer()->notNull(),
                'userId' => $this->integer()->notNull(),
                'assetId' => $this->integer()->notNull(),
                'PRIMARY KEY(id)',
            ]);
        }

        if ($tableSchemaPermissions === null) {
            $this->createTable(Table::CM_PERMISSIONS, [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        if ($tableSchemaPermissionsUsers === null) {
            $this->createTable(Table::CM_PERMISSIONS_USERS, [
                'id' => $this->primaryKey(),
                'permissionId' => $this->integer()->notNull(),
                'userId' => $this->integer()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }
    }

    /**
     * Drop the tables
     */
    public function dropTables()
    {
        $this->dropTableIfExists(Table::CM_COMPANIES);
        $this->dropTableIfExists(Table::CM_USERS);
        $this->dropTableIfExists(Table::CM_DOCUMENTS);
        $this->dropTableIfExists(Table::CM_COMPANYTYPES);
        $this->dropTableIfExists(Table::CM_COMPANYTYPES_SITES);
        $this->dropTableIfExists(Table::CM_PERMISSIONS);
        $this->dropTableIfExists(Table::CM_PERMISSIONS_USERS);
        return null;
    }

    /**
     * Drop the foreign keys
     */
    public function dropForeignKeys()
    {
        $tables = [
            Table::CM_COMPANIES,
            Table::CM_USERS,
            Table::CM_DOCUMENTS,
            Table::CM_COMPANYTYPES,
            Table::CM_COMPANYTYPES_SITES,
            Table::CM_PERMISSIONS,
            Table::CM_PERMISSIONS_USERS,
        ];
        foreach ($tables as $table) {
            $this->_dropForeignKeyToAndFromTable($table);
        }
    }

    /**
     * Deletes the project config entry.
     */
    public function dropProjectConfig()
    {
        Craft::$app->projectConfig->remove('companymanagement_companytypes');
    }

    /**
     * Creates the indexes.
     */
    public function createIndexes()
    {
        $this->createIndex(null, Table::CM_COMPANIES, 'typeId', false);
        $this->createIndex(null, Table::CM_COMPANYTYPES, 'handle', true);
        $this->createIndex(null, Table::CM_COMPANYTYPES, 'fieldLayoutId', false);
        $this->createIndex(null, Table::CM_COMPANYTYPES_SITES, ['companyTypeId', 'siteId'], true);
        $this->createIndex(null, Table::CM_COMPANYTYPES_SITES, 'siteId', false);
    }

    /**
     * Adds the foreign keys.
     */
    public function addForeignKeys()
    {
        $this->addForeignKey(null, Table::CM_COMPANIES, ['id'], '{{%elements}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, Table::CM_COMPANIES, ['userId'], \craft\db\Table::USERS, ['id'], null, 'CASCADE');
        $this->addForeignKey(null, Table::CM_COMPANIES, ['typeId'], Table::CM_COMPANYTYPES, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::CM_USERS, ['userId'], \craft\db\Table::USERS, ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::CM_USERS, ['companyId'], Table::CM_COMPANIES, ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::CM_DOCUMENTS, ['assetId'], \craft\db\Table::ASSETS, ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::CM_DOCUMENTS, ['userId'], \craft\db\Table::USERS, ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::CM_COMPANYTYPES, ['fieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL');
        $this->addForeignKey(null, Table::CM_COMPANYTYPES_SITES, ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::CM_COMPANYTYPES_SITES, ['companyTypeId'], Table::CM_COMPANYTYPES, ['id'], 'CASCADE');
        $this->addForeignKey(null, Table::CM_PERMISSIONS_USERS, ['userId'], \craft\db\Table::USERS, ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, Table::CM_PERMISSIONS_USERS, ['permissionId'], Table::CM_PERMISSIONS, ['id'], 'CASCADE', 'CASCADE');
    }

    /**
     * Insert the default data
     */
    public function insertDefaultData()
    {
        $this->_createCompanyType();
        $this->_createPermissions();
    }

    /**
     * Create a Default Company Type for the Company Element
     */
    private function _createCompanyType()
    {
        $this->insert(FieldLayout::tableName(), ['type' => Company::class]);
        $this->_companyFieldLayoutId = $this->db->getLastInsertID(FieldLayout::tableName());

        $data = [
            'name' => 'Default',
            'handle' => 'default',
            'hasTitleField' => true,
            'fieldLayoutId' => $this->_companyFieldLayoutId,
            'titleFormat' => null,
        ];

        $companyType = new CompanyTypeModel($data);

        $siteIds = (new Query())
            ->select(['id'])
            ->from(Site::tableName())
            ->column();

        $allSiteSettings = [];

        foreach ($siteIds as $siteId) {
            $siteSettings = new CompanyTypeSiteModel();

            $siteSettings->siteId = $siteId;
            $siteSettings->hasUrls = true;
            $siteSettings->uriFormat = 'company-management/companies/{slug}';
            $siteSettings->template = 'company-management/companies/_company';

            $allSiteSettings[$siteId] = $siteSettings;
        }

        $companyType->setSiteSettings($allSiteSettings);

        CompanyManagement::$plugin->companyTypes->saveCompanyType($companyType);
    }
    /**
     * Create the permissions for the Company Users
     */
    private function _createPermissions()
    {
        $rows = [];

        $rows[] = ['access:company'];
        $rows[] = ['manage:notifications'];
        $rows[] = ['manage:employees'];
        $rows[] = ['manage:companydata'];
        $rows[] = ['manage:benefits'];
        $rows[] = ['purchase:groupbenefits'];
        $rows[] = ['purchase:voluntarybenefits'];

        $this->batchInsert(Table::CM_PERMISSIONS, ['name'], $rows);
    }

    /**
     * Returns if the table exists.
     *
     * @param string $tableName
     * @param \yii\db\Migration|null $migration
     * @return bool If the table exists.
     * @throws NotSupportedException
     */
    private function _tableExists(string $tableName): bool
    {
        $schema = $this->db->getSchema();
        $schema->refresh();
        $rawTableName = $schema->getRawTableName($tableName);
        $table = $schema->getTableSchema($rawTableName);

        return (bool)$table;
    }

    /**
     * @param string $tableName
     * @throws NotSupportedException
     */
    private function _dropForeignKeyToAndFromTable(string $tableName)
    {
        if ($this->_tableExists($tableName)) {
            MigrationHelper::dropAllForeignKeysToTable($tableName, $this);
            MigrationHelper::dropAllForeignKeysOnTable($tableName, $this);
        }
    }
}





