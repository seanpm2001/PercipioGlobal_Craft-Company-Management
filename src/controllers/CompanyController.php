<?php
/**
 * Company Management plugin for Craft CMS 3.x
 *
 * A plugin to setup companies
 *
 * @link      http://percipio.london/
 * @copyright Copyright (c) 2021 Percipio
 */

namespace percipiolondon\companymanagement\controllers;

use percipiolondon\companymanagement\CompanyManagement;

use Craft;
use craft\web\Controller;

/**
 * Company Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Percipio
 * @package   CompanyManagement
 * @since     0.1.0
 */
class CompanyController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['edit','do-something'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/company-management/company
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'Welcome to the CompanyController actionIndex() method';

        return $result;
    }

    /**
     * @return mixed
     */
    public function actionEdit()
    {
        $variables = [];
        return $this->renderTemplate('companies-management/companies/_edit', $variables);
    }

    /**
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        return "company-management/companies";
    }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/company-management/company/do-something
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'Welcome to the CompanyController actionDoSomething() method';

        return $result;
    }
}
