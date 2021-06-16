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

use craft\base\Element;
use craft\elements\Asset;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\helpers\Company as CompanyHelper;

use Craft;
use craft\web\Controller;
use percipiolondon\companymanagement\elements\Company;

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
        return $this->renderTemplate('company-management/companies/index', []);
    }

    /**
     * @return mixed
     */
    public function actionEdit(int $companyId = null, Company $company = null)
    {
        $variables = compact('companyId', 'company');

        if (empty($variables['company'])) {
            if (!empty($variables['companyId'])) {
                $variables['company'] = CompanyManagement::$plugin->company->getCompanyById($variables['companyId'], 1);

                if (!$variables['company']) {
                    throw new Exception('Missing company data.');
                }
            } else {
                $variables['company'] = new Company();
            }
        }

        $company = $variables['company'];

        if ($company === null) {
            $variables['title'] = Craft::t('company-management', 'Create a new company');
        } else {
            $variables['title'] = $company->name;
        }

        return $this->renderTemplate('company-management/companies/_edit', $variables);
    }

    /**
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $elementsService = Craft::$app->getElements();
        $company = CompanyHelper::companyFromPost($request);
        $company = CompanyHelper::populateCompanyFromPost($company, $request);

        $success = $elementsService->saveElement($company);

        if(!$success) {
            Craft::$app->getSession()->setError(Craft::t('company-management', 'Couldn’t save company.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'company' => $company,
                'errors' => $company->getErrors(),
            ]);

            return null;
        }

        $this->setSuccessFlash(Craft::t('company-management', 'Company saved.'));
        return $this->renderTemplate('company-management/companies');
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
