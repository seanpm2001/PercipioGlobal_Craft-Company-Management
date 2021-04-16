<?php
/**
 * company-management plugin for Craft CMS 3.x
 *
 * Manage Companies on entry types
 *
 * @link      https://percipio.london
 * @copyright Copyright (c) 2021 Percipio.London
 */

namespace percipiolondon\companymanagementtests\unit;

use Codeception\Test\Unit;
use UnitTester;
use Craft;
use percipiolondon\companymanagement\Companymanagement;

/**
 * ExampleUnitTest
 *
 *
 * @author    Percipio.London
 * @package   Companymanagement
 * @since     1.0.0
 */
class ExampleUnitTest extends Unit
{
    // Properties
    // =========================================================================

    /**
     * @var UnitTester
     */
    protected $tester;

    // Public methods
    // =========================================================================

    // Tests
    // =========================================================================

    /**
     *
     */
    public function testPluginInstance()
    {
        $this->assertInstanceOf(
            Companymanagement::class,
            Companymanagement::$plugin
        );
    }

    /**
     *
     */
    public function testCraftEdition()
    {
        Craft::$app->setEdition(Craft::Pro);

        $this->assertSame(
            Craft::Pro,
            Craft::$app->getEdition()
        );
    }
}
