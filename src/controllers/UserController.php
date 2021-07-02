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

use Craft;
use http\Header;
use jamesedmonston\graphqlauthentication\GraphqlAuthentication;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\filters\Cors;
use yii\filters\ContentNegotiator;
use yii\web\Controller;
use yii\web\Response;

class UserController extends Controller
{
//    protected $allowAnonymous = [
//        'get-user'
//    ];

    public function actionGetUser($companyId = null)
    {
        $restrictionService = GraphqlAuthentication::$restrictionService;
        $request = Craft::$app->getRequest();

        $this->response->getHeaders()
            ->setDefault('Access-Control-Allow-Origin', '*')
            ->setDefault('Access-Control-Allow-Credentials', 'true')
            ->setDefault('Access-Control-Allow-Headers', 'X-Craft-Token');

        if ($restrictionService->shouldRestrictRequests()) {

            $user = GraphqlAuthentication::$tokenService->getUserFromToken();

            if(!CompanyManagement::$plugin->userPermissions->applyCanParam("access:company", $user->id, $request->getBodyParam('companyId')) ) {
                throw new \yii\web\HttpException(401, 'Unauthorized');
            }
        }

        $user = [
            'name' => 'name'
        ];

        return $this->asJson($user);
    }
}
