<?php
/**
 * Company Management plugin for Craft CMS 3.x
 *
 * A plugin to setup companies
 *
 * @link      http://percipio.london/
 * @copyright Copyright (c) 2021 Percipio
 */

namespace percipiolondon\companymanagement;

use craft\fields\PlainText;
use craft\web\twig\variables\CraftVariable;
use percipiolondon\companymanagement\behaviors\CraftVariableBehavior;
use percipiolondon\companymanagement\elements\Company;
use percipiolondon\companymanagement\services\Benefits as BenefitsService;
use percipiolondon\companymanagement\services\Wages as WagesService;
use percipiolondon\companymanagement\services\Company as CompanyService;
use percipiolondon\companymanagement\models\Settings;
use percipiolondon\companymanagement\elements\Company as CompanyElement;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\BaseObject;
use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Percipio
 * @package   CompanyManagement
 * @since     0.1.0
 *
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class CompanyManagement extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * CompanyManagement::$plugin
     *
     * @var CompanyManagement
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '0.1.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * CompanyManagement::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->_registerCpRoutes();
        $this->_registerElementTypes();
        $this->_registerVariables();
        $this->_registerServices();
        $this->_registerAfterInstall();
        $this->_registerAfterUninstall();
        $this->_registerTemplateHooks();

        Craft::info(
            Craft::t(
                'company-management',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): array
    {
        $nav = parent::getCpNavItem();

        $nav['label'] = Craft::t('company-management', 'Company Management');

        $nav['subnav']['dashboard'] = [
            'label' => Craft::t('company-management', 'Dashboard'),
            'url' => 'company-management'
        ];

        if (Craft::$app->getUser()->checkPermission('companies-mangeCompanies')) {
            $nav['subnav']['companies'] = [
                'label' => Craft::t('company-management', 'Companies'),
                'url' => 'company-management/companies'
            ];
        }

        return $nav;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'company-management/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    private function _registerCpRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['company-management/companies'] = 'company-management/company/index';
                $event->rules['company-management/companies/new'] = 'company-management/company/edit';
                $event->rules['company-management/companies/<companyId:\d+>'] = 'company-management/company/edit';
            }
        );
    }

    private function _registerElementTypes()
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = CompanyElement::class;
            }
        );
    }

    private function _registerAfterInstall()
    {
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_UNINSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    CompanyManagement::$plugin->company->uninstallCompanyUserFields();
                }
            }
        );
    }

    private function _registerAfterUninstall()
    {
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    CompanyManagement::$plugin->company->installCompanyUserFields();
                }
            }
        );
    }

    private function _registerVariables()
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->attachBehavior('companies', CraftVariableBehavior::class);
            }
        );
    }

    private function _registerServices()
    {
        $this->setComponents([
            'company' => CompanyService::class
        ]);
    }

    private function _registerTemplateHooks()
    {
        Craft::$app->getView()->hook('cp.users.edit', [CompanyManagement::$plugin->company, 'addEditUserCustomFieldTab']);
        Craft::$app->getView()->hook('cp.users.edit.content', [CompanyManagement::$plugin->company, 'addEditUserCustomFieldContent']);
    }
}
