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

use craft\web\Controller;
use percipiolondon\companymanagement\elements\Department;
use percipiolondon\companymanagement\helpers\Department as DeparmentHelper;
use yii\db\Exception;
use Craft;

class DepartmentController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['edit', 'index'];

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
        return $this->renderTemplate('company-management/departments/index', []);
    }

    /**
     * @return mixed
     */
    public function actionEdit(int $departmentId = null, Department $department = null)
    {
        $variables = compact('departmentId', 'department');

        if (empty($variables['department'])) {
            if (!empty($variables['departmentId'])) {
                $department = Department::findOne($variables['departmentId']);
                $variables['department'] = $department;
                if (!$department) {
                    throw new Exception('Missing department data.');
                }
            } else {
                $variables['department'] = new Department();
            }
        }

        if ($department === null) {
            $variables['title'] = Craft::t('company-management', 'Create a new department');
        } else {
            $variables['title'] = $variables['department']->title;
        }

        $variables['errors'] = $variables['department']->getErrors();

        return $this->renderTemplate('company-management/departments/_edit', $variables);
    }

    /**
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $elementsService = Craft::$app->getElements();
        $department = DeparmentHelper::populateDepartmentFromPost();

        $success = $elementsService->saveElement($department);

        if(!$success) {
            Craft::$app->getSession()->setError(Craft::t('company-management', 'Couldn’t save employee.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'department' => $department,
                'errors' => $department->getErrors(),
            ]);

            return null;
        }

        $this->setSuccessFlash(Craft::t('company-management', 'Department saved.'));
        return $this->renderTemplate('company-management/departments');
    }
}
