<?php

namespace percipiolondon\companymanagement\events;

use percipiolondon\companymanagement\models\CompanyType;
use yii\base\Event;

/**
 * Company type event class.
 *
 * @since 0.1.0
 */
class CompanyTypeEvent extends Event
{
    /**
     * @var CompanyType|null The company type model associated with the event.
     */
    public $companyType;

    /**
     * @var bool Whether the product type is brand new
     */
    public $isNew = false;
}
