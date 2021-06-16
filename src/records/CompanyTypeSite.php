<?php

namespace percipiolondon\companymanagement\records;

use percipiolondon\companymanagement\db\Table;
use craft\db\ActiveRecord;
use craft\records\Site;
use yii\db\ActiveQueryInterface;

/**
 * Company type site record.
 *
 * @property bool $hasUrls
 * @property int $id
 * @property CompanyType $companyType
 * @property int $companyTypeId
 * @property Site $site
 * @property int $siteId
 * @property string $template
 * @property string $uriFormat
 * @since 0.1.0
 */
class CompanyTypeSite extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return Table::CM_COMAPNYTYPES_SITES;
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getCompanyType(): ActiveQueryInterface
    {
        return $this->hasOne(CompanyType::class, ['id', 'companyTypeId']);
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getSite(): ActiveQueryInterface
    {
        return $this->hasOne(Site::class, ['id', 'siteId']);
    }
}
