<?php

namespace percipiolondon\companymanagement\migrations;

use Craft;
use craft\db\Migration;
use percipiolondon\companymanagement\db\Table;

/**
 * m210704_153542_jobroles migration.
 */
class m210704_153542_jobroles extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        echo "m210704_153542_jobroles executed.\n";
        // Place migration code here...
        $this->addColumn(Table::CM_USERS, 'jobRole', $this->string(500)->defaultValue(''));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210704_153542_jobroles cannot be reverted.\n";
        return false;
    }
}
