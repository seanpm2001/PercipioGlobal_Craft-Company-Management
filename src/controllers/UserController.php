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

use yii\web\Controller;

class UserController extends Controller
{
    protected $allowAnonymous = [
        'get-user'
    ];

    public function actionGetUser()
    {

    }
}
