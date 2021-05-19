<?php
/**
 * Company Management plugin for Craft CMS 3.x
 *
 * A plugin to setup companies
 *
 * @link      http://percipio.london/
 * @copyright Copyright (c) 2021 Percipio
 */

namespace percipiolondon\companymanagement\services;

use percipiolondon\companymanagement\CompanyManagement;

use Craft;
use craft\base\Component;

/**
 * Company Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Percipio
 * @package   CompanyManagement
 * @since     0.1.0
 */
class Company extends Component
{
    // Public Methods
    // =========================================================================

    public function getCompanyById(int $id)
    {
        return Craft::$app->getElements()->getElementById($id, \percipiolondon\companymanagement\elements\Company::class);
    }

}
