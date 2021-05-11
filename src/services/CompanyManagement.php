<?php
/**
 * Company Management plugin for Craft CMS 3.x
 *
 * A plugin to setup companies and add users to it
 *
 * @link      http://percipio.london
 * @copyright Copyright (c) 2021 Percipio
 */

namespace percipiolondon\companymanagement\services;

use percipiolondon\companymanagement\CompanyManagement;

use Craft;
use craft\base\Component;

/**
 * CompanyManagement Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Percipio
 * @package   CompanyManagement
 * @since     1.0.0
 */
class CompanyManagement extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     CompanyManagement::$plugin->companyManagement->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (CompanyManagement::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }
}
