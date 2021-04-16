<?php
/**
 * company-management plugin for Craft CMS 3.x
 *
 * Manage Companies on entry types
 *
 * @link      https://percipio.london
 * @copyright Copyright (c) 2021 Percipio.London
 */

namespace percipiolondon\companymanagement\utilities;

use percipiolondon\companymanagement\Companymanagement;
use percipiolondon\companymanagement\assetbundles\companymanagementutilityutility\CompanymanagementUtilityUtilityAsset;

use Craft;
use craft\base\Utility;

/**
 * company-management Utility
 *
 * Utility is the base class for classes representing Control Panel utilities.
 *
 * https://craftcms.com/docs/plugins/utilities
 *
 * @author    Percipio.London
 * @package   Companymanagement
 * @since     1.0.0
 */
class CompanymanagementUtility extends Utility
{
    // Static
    // =========================================================================

    /**
     * Returns the display name of this utility.
     *
     * @return string The display name of this utility.
     */
    public static function displayName(): string
    {
        return Craft::t('company-management', 'CompanymanagementUtility');
    }

    /**
     * Returns the utility’s unique identifier.
     *
     * The ID should be in `kebab-case`, as it will be visible in the URL (`admin/utilities/the-handle`).
     *
     * @return string
     */
    public static function id(): string
    {
        return 'companymanagement-companymanagement-utility';
    }

    /**
     * Returns the path to the utility's SVG icon.
     *
     * @return string|null The path to the utility SVG icon
     */
    public static function iconPath()
    {
        return Craft::getAlias("@percipiolondon/companymanagement/assetbundles/companymanagementutilityutility/dist/img/CompanymanagementUtility-icon.svg");
    }

    /**
     * Returns the number that should be shown in the utility’s nav item badge.
     *
     * If `0` is returned, no badge will be shown
     *
     * @return int
     */
    public static function badgeCount(): int
    {
        return 0;
    }

    /**
     * Returns the utility's content HTML.
     *
     * @return string
     */
    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(CompanymanagementUtilityUtilityAsset::class);

        $someVar = 'Have a nice day!';
        return Craft::$app->getView()->renderTemplate(
            'company-management/_components/utilities/CompanymanagementUtility_content',
            [
                'someVar' => $someVar
            ]
        );
    }
}
