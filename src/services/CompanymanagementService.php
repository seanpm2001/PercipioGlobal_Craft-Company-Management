<?php
/**
 * company-management plugin for Craft CMS 3.x
 *
 * Manage Companies on entry types
 *
 * @link      https://percipio.london
 * @copyright Copyright (c) 2021 Percipio.London
 */

namespace percipiolondon\companymanagement\services;

use percipiolondon\companymanagement\Companymanagement;

use Craft;
use craft\base\Component;

/**
 * CompanymanagementService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Percipio.London
 * @package   Companymanagement
 * @since     1.0.0
 */
class CompanymanagementService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Companymanagement::$plugin->companymanagementService->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (Companymanagement::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }
}
