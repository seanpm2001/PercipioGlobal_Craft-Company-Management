<?php

namespace percipiolondon\companymanagement\helpers;

use percipiolondon\companymanagement\elements\Department as DepartmentModel;
use percipiolondon\companymanagement\records\Department as DepartmentRecord;

use yii\web\NotFoundHttpException;
use yii\web\Request;
use Craft;

class Department
{
    public static function departmentFromPost(Request $request = null): DepartmentModel
    {
        if ($request === null) {
            $request = Craft::$app->getRequest();
        }

        $departmentId = $request->getBodyParam('employeeId');

        if($departmentId) {
            $department = DepartmentModel::findOne($departmentId);

            if (!$department) {
                throw new NotFoundHttpException(Craft::t('company-management', 'No department with the ID “{id}”', ['id' => $departmentId]));
            }
        }else {
            $department = new DepartmentModel();
        }

        return $department;
    }

    public static function populateDepartmentFromPost(DepartmentModel $department = null, Request $request = null): DepartmentModel
    {
        if ($request === null) {
            $request = Craft::$app->getRequest();
        }

        if ($department === null) {
            $department = static::departmentFromPost($request);
        }

        $department->title = $request->getBodyParam('title');

        $companyId = count($request->getBodyParam('companyId')) > 0 ? $request->getBodyParam('companyId')[0] : null;

        $department->slug = str_replace(" ", "_", strtolower($request->getBodyParam('title')).'-'.$companyId);
        $department->companyId = $companyId;

        return $department;
    }

    public static function populateDepartmentFromRecord(DepartmentRecord $departmentRecord, int $companyId = null): DepartmentModel
    {
        $department = new DepartmentModel();

        $department->companyId = $companyId;
        $department->title = $departmentRecord->title;
        $department->slug = $departmentRecord->slug;

        return $department;
    }
}
