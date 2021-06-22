<?php

namespace percipiolondon\companymanagement\models;

use Craft;
use craft\base\Model;
use craft\models\Site;
use percipiolondon\companymanagement\CompanyManagement;
use yii\base\InvalidConfigException;

/**
 * Company type locale model class.
 *
 * @property CompanyType $companyType the Company Type
 * @property Site $site the Site
 */
class CompanyTypeSite extends Model
{
    /**
     * @var int ID
     */
    public $id;

    /**
     * @var int Company type ID
     */
    public $companyTypeId;

    /**
     * @var int Site ID
     */
    public $siteId;

    /**
     * @var bool Has Urls
     */
    public $hasUrls;

    /**
     * @var string URL Format
     */
    public $uriFormat;

    /**
     * @var string Template Path
     */
    public $template;

    /**
     * @var CompanyType
     */
    private $_companyType;

    /**
     * @var Site
     */
    private $_site;

    /**
     * @var bool
     */
    public $uriFormatIsRequired = true;


    /**
     * Returns the Company Type.
     *
     * @return CompanyType
     * @throws InvalidConfigException if [[companyTypeId]] is missing or invalid
     */
    public function getCompanyType(): CompanyType
    {
        if ($this->_companyType !== null) {
            return $this->_companyType;
        }

        if (!$this->companyTypeId) {
            throw new InvalidConfigException('Company type site is missing its company type ID');
        }

        if (($this->_companyType = CompanyManagement::$plugin->companyTypes()->getCompanyTypeById($this->companyTypeId)) === null) {
            throw new InvalidConfigException('Invalid company type ID: ' . $this->companyTypeId);
        }

        return $this->_companyType;
    }

    /**
     * Sets the Company Type.
     *
     * @param CompanyType $companyType
     */
    public function setCompanyType(CompanyType $companyType)
    {
        $this->_companyType = $companyType;
    }

    /**
     * @return Site
     * @throws InvalidConfigException if [[siteId]] is missing or invalid
     */
    public function getSite(): Site
    {
        if ($this->_site !== null) {
            return $this->_site;
        }

        if (!$this->siteId) {
            throw new InvalidConfigException('Company type site is missing its site ID');
        }

        if (($this->_site = Craft::$app->getSites()->getSiteById($this->siteId)) === null) {
            throw new InvalidConfigException('Invalid site ID: ' . $this->siteId);
        }

        return $this->_site;
    }

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        if ($this->uriFormatIsRequired) {
            $rules[] = ['uriFormat', 'required'];
        }

        return $rules;
    }
}
