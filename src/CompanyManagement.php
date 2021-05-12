<?php
/**
 * Company Management plugin for Craft CMS 3.x
 *
 * A plugin to setup companies and add users to it
 *
 * @link      http://percipio.london
 * @copyright Copyright (c) 2021 Percipio
 */

namespace percipiolondon\companymanagement;

use craft\base\Element;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\UserPermissions;
use percipiolondon\companymanagement\services\CompanyManagement as CompanyManagementService;
use percipiolondon\companymanagement\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\models\Section;
use craft\models\Section_SiteSettings;

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
 * @since     1.0.0
 *
 * @property  CompanyManagementService $companyManagement
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

        $this->_registerRoutes();
        $this->_registerSaves();
        $this->_registerPermissions();

        Craft::info(
            Craft::t(
                'company-management',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function getCpNavItem(): array
    {
        $nav = parent::getCpNavItem();
        $nav['subnav'] = [
            'dashboard' => ['label' => 'Dashboard', 'url' => 'company-management'],
            'companies' => ['label' => 'Companies', 'url' => 'company-management/companies'],
        ];

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

    protected function _registerRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['company-management'] = ['template' => 'company-management/views'];
                $event->rules['company-management/companies'] = ['template' => 'company-management/views/companies'];
                $event->rules['company-management/company/<companyId:\d+>'] = ['template' => 'company-management/views/company'];
            }
        );
    }

    protected function _registerSaves()
    {
        /* Add section after install */
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {

                    $section = new Section([
                        'name' => 'Company Management',
                        'handle' => 'companyManagement',
                        'type' => Section::TYPE_CHANNEL,
                        'siteSettings' => [
                            new Section_SiteSettings([
                                'siteId' => Craft::$app->sites->getPrimarySite()->id,
                                'enabledByDefault' => true,
                                'hasUrls' => false,
                            ]),
                        ]
                    ]);

                    Craft::$app->sections->saveSection($section);
                }
            }
        );

        /* Redirect to companies overview after save */
        Event::on(
            Element::class,
            Element::EVENT_AFTER_SAVE,
            function(Event $event){
                $section = Craft::$app->sections->getSectionByHandle('companyManagement');

                if($event->sender->sectionId === $section->id && false === $event->isNew) {
                    Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('company-management/companies'))->send();
                }
            }
        );
    }

    protected function _registerPermissions()
    {
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[Craft::t(
                    'company-management', $this->getSettings()->pluginName )] = [
                    'test' => ['label' => Craft::t('company-management', 'Test')],
                ];
            }
        );
    }
}
