<?php
/**
 * company-management plugin for Craft CMS 3.x
 *
 * Manage Companies on entry types
 *
 * @link      https://percipio.london
 * @copyright Copyright (c) 2021 Percipio.London
 */

namespace percipiolondon\companymanagement\variables;

use percipiolondon\companymanagement\Companymanagement;

use Craft;

/**
 * company-management Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.companymanagement }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Percipio.London
 * @package   Companymanagement
 * @since     1.0.0
 */
class CompanymanagementVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.companymanagement.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.companymanagement.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }
}
