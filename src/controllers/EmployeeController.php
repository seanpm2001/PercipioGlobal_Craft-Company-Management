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
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\elements\Employee;
use yii\db\Exception;
use Craft;

class EmployeeController extends Controller
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
        return $this->renderTemplate('company-management/employees/index', []);
    }

    /**
     * @return mixed
     */
    public function actionEdit(int $employeeId = null, Employee $employee = null)
    {
        $variables = compact('employeeId', 'employee');

        if (empty($variables['employee'])) {
            if (!empty($variables['employeeId'])) {
                $variables['employee'] = CompanyManagement::$plugin->employee->getEmployeeId($variables['employeeId'], 1);

                if (!$variables['employee']) {
                    throw new Exception('Missing company data.');
                }
            } else {
                $variables['employee'] = new Employee();
            }
        }

        if ($employee === null) {
            $variables['title'] = Craft::t('company-management', 'Create a new employee');
        } else {
            $variables['title'] = $employee->title;
        }

        return $this->renderTemplate('company-management/employees/_edit', $variables);
    }
}
