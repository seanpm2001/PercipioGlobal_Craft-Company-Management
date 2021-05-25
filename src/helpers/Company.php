<?php

namespace percipiolondon\companymanagement\helpers;

use craft\helpers\DateTimeHelper;
use percipiolondon\companymanagement\CompanyManagement;
use yii\web\Request;
use percipiolondon\companymanagement\elements\Company as CompanyModel;

class Company
{
    public static function companyFromPost(Request $request = null): CompanyModel
    {
        if ($request === null) {
            $request = Craft::$app->getRequest();
        }

        $companyId = $request->getBodyParam('companyId');

        if($companyId) {
            $company = CompanyManagement::$plugin->company->getCompanyById($companyId);

            if (!$company) {
                throw new NotFoundHttpException(Craft::t('company-management', 'No company with the ID “{id}”', ['id' => $companyId]));
            }
        }else {
            $company = new CompanyModel();
        }

        return $company;
    }

    public static function populateCompanyFromPost(CompanyModel $company = null, Request $request = null): CompanyModel
    {
        if ($request === null) {
            $request = Craft::$app->getRequest();
        }

        if ($company === null) {
            $company = static::companyFromPost($request);
        }

        $company->title =  $request->getBodyParam('name');
        $company->name = $request->getBodyParam('name');
        $company->info = $request->getBodyParam('info');
        $company->shortName = $request->getBodyParam('shortName');
        $company->address = $request->getBodyParam('address');
        $company->town = $request->getBodyParam('town');
        $company->postcode = $request->getBodyParam('postcode');
        $company->registerNumber = $request->getBodyParam('registerNumber');
        $company->payeReference = $request->getBodyParam('payeReference');
        $company->accountsOfficeReference = $request->getBodyParam('accountsOfficeReference');
        $company->taxReference = $request->getBodyParam('taxReference');
        $company->website = $request->getBodyParam('website');
        $company->logo = $request->getBodyParam('logo');
        $company->contactName = $request->getBodyParam('contactName');
        $company->contactEmail = $request->getBodyParam('contactEmail');
        $company->contactRegistrationNumber = strtoupper(str_replace(' ', '', $request->getBodyParam('contactRegistrationNumber')));
        $company->contactPhone = $request->getBodyParam('contactPhone');

        $contactBirthday = DateTimeHelper::toDateTime($request->getBodyParam('contactBirthday'));
        $company->contactBirthday = $contactBirthday && $contactBirthday instanceOf \DateTime ? $contactBirthday->format(\DateTime::ATOM) : null;

        return $company;
    }
}