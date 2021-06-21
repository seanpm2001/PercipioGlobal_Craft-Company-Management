<?php

namespace percipiolondon\companymanagement\elements\db;

use percipiolondon\companymanagement\db\Table;
use percipiolondon\companymanagement\elements\Company;
use percipiolondon\companymanagement\models\CompanyType;
use percipiolondon\companymanagement\CompanyManagement;


use Craft;
use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use DateTime;
use yii\db\Connection;

class CompanyQuery extends ElementQuery
{
    public $slug;

    // Company Info
    public $name;
    public $info;
    public $address;
    public $town;
    public $postcode;
    public $website;
    public $logo;

    // Company Manager Info
    public $contactFirstName;
    public $contactLastName;
    public $contactEmail;
    public $contactRegistrationNumber;
    public $contactPhone;
    public $contactBirthday;

    /**
     * @var int|int[]|null The company type ID(s) that the resulting products must have.
     */
    public $typeId;

    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default status
        if (!isset($config['status'])) {
            $config['status'] = 'live';
        }

        parent::__construct($elementType, $config);
    }

    public function name($value)
    {
        $this->name = $value;
        return $this;
    }

    public function info($value)
    {
        $this->info = $value;
        return $this;
    }

    public function slug($value)
    {
        $this->slug = $value;
        return $this;
    }

    public function address($value)
    {
        $this->address = $value;
        return $this;
    }

    public function town($value)
    {
        $this->town = $value;
        return $this;
    }

    public function postcode($value)
    {
        $this->postcode = $value;
        return $this;
    }

   public function website($value)
    {
        $this->website = $value;
        return $this;
    }

    public function logo($value)
    {
        $this->logo = $value;
        return $this;
    }

    public function contactFirstName($value)
    {
        $this->contactFirstName = $value;
        return $this;
    }

    public function contactLastName($value)
    {
        $this->contactLastName = $value;
        return $this;
    }

    public function contactEmail($value)
    {
        $this->contactEmail = $value;
        return $this;
    }

    public function contactRegistrationNumber($value)
    {
        $this->contactRegistrationNumber = $value;
        return $this;
    }

    public function contactPhone($value)
    {
        $this->contactPhone = $value;
        return $this;
    }

    public function contactBirthday($value)
    {
        $this->contactBirthday = $value;
        return $this;
    }

    /**
     * Narrows the query results based on the companies’ types.
     *
     * Possible values include:
     *
     * | Value | Fetches {elements}…
     * | - | -
     * | `'foo'` | of a type with a handle of `foo`.
     * | `'not foo'` | not of a type with a handle of `foo`.
     * | `['foo', 'bar']` | of a type with a handle of `foo` or `bar`.
     * | `['not', 'foo', 'bar']` | not of a type with a handle of `foo` or `bar`.
     * | an [[CompanyType|CompanyType]] object | of a type represented by the object.
     *
     * ---
     *
     * ```twig
     * {# Fetch {elements} with a Foo company type #}
     * {% set {elements-var} = {twig-method}
     *     .type('foo')
     *     .all() %}
     * ```
     *
     * ```php
     * // Fetch {elements} with a Foo company type
     * ${elements-var} = {php-method}
     *     ->type('foo')
     *     ->all();
     * ```
     *
     * @param string|string[]|CompanyType|null $value The property value
     * @return static self reference
     */
    public function type($value)
    {
        if ($value instanceof CompanyType) {
            $this->typeId = [$value->id];
        } else if ($value !== null) {
            $this->typeId = (new Query())
                ->select(['id'])
                ->from([Table::CM_COMPANYTYPES])
                ->where(Db::parseParam('handle', $value))
                ->column();
        } else {
            $this->typeId = null;
        }

        return $this;
    }

    /**
     * Narrows the query results based on the companies’ types, per the types’ IDs.
     *
     * Possible values include:
     *
     * | Value | Fetches {elements}…
     * | - | -
     * | `1` | of a type with an ID of 1.
     * | `'not 1'` | not of a type with an ID of 1.
     * | `[1, 2]` | of a type with an ID of 1 or 2.
     * | `['not', 1, 2]` | not of a type with an ID of 1 or 2.
     *
     * ---
     *
     * ```twig
     * {# Fetch {elements} of the company type with an ID of 1 #}
     * {% set {elements-var} = {twig-method}
     *     .typeId(1)
     *     .all() %}
     * ```
     *
     * ```php
     * // Fetch {elements} of the company type with an ID of 1
     * ${elements-var} = {php-method}
     *     ->typeId(1)
     *     ->all();
     * ```
     *
     * @param mixed $value The company value
     * @return static self reference
     */
    public function typeId($value)
    {
        $this->typeId = $value;
        return $this;
    }

    /**
     * Narrows the query results based on the {elements}’ statuses.
     *
     * Possible values include:
     *
     * | Value | Fetches {elements}…
     * | - | -
     * | `'live'` _(default)_ | that are live.
     * | `'pending'` | that are pending (enabled with a Post Date in the future).
     * | `'expired'` | that are expired (enabled with an Expiry Date in the past).
     * | `'disabled'` | that are disabled.
     * | `['live', 'pending']` | that are live or pending.
     *
     * ---
     *
     * ```twig
     * {# Fetch disabled {elements} #}
     * {% set {elements-var} = {twig-function}
     *     .status('disabled')
     *     .all() %}
     * ```
     *
     * ```php
     * // Fetch disabled {elements}
     * ${elements-var} = {element-class}::find()
     *     ->status('disabled')
     *     ->all();
     * ```
     */
    public function status($value)
    {
        return parent::status($value);
    }

    protected function beforePrepare(): bool
    {
        $this->_normalizeTypeId();

        // See if 'type' were set to invalid handles
        if ($this->typeId === []) {
            return false;
        }

        // join in the products table
        $this->joinElementTable('companymanagement_companies');

        // select the price column
        $this->query->select([
            'companymanagement_companies.id',
            'companymanagement_companies.typeId',
            'companymanagement_companies.name',
            'companymanagement_companies.info',
            'companymanagement_companies.slug',
            'companymanagement_companies.address',
            'companymanagement_companies.town',
            'companymanagement_companies.postcode',
            'companymanagement_companies.website',
            'companymanagement_companies.logo',
            'companymanagement_companies.contactFirstName',
            'companymanagement_companies.contactLastName',
            'companymanagement_companies.contactEmail',
            'companymanagement_companies.contactRegistrationNumber',
            'companymanagement_companies.contactPhone',
            'companymanagement_companies.contactBirthday',
            'companymanagement_companies.userId',
        ]);

        if ($this->typeId) {
            $this->subQuery->andWhere(['companymanagement_companies.typeId' => $this->typeId]);
        }

        $this->_applyRefParam();

        return parent::beforePrepare();
    }

    /**
     * Normalizes the typeId param to an array of IDs or null
     */
    private function _normalizeTypeId()
    {
        if (empty($this->typeId)) {
            $this->typeId = null;
        } else if (is_numeric($this->typeId)) {
            $this->typeId = [$this->typeId];
        } else if (!is_array($this->typeId) || !ArrayHelper::isNumeric($this->typeId)) {
            $this->typeId = (new Query())
                ->select(['id'])
                ->from([Table::CM_COMPANYTYPES])
                ->where(Db::parseParam('id', $this->typeId))
                ->column();
        }
    }

    /**
     * Applies the 'ref' param to the query being prepared.
     */
    private function _applyRefParam()
    {
        if (!$this->ref) {
            return;
        }

        $refs = ArrayHelper::toArray($this->ref);
        $joinSections = false;
        $condition = ['or'];

        foreach ($refs as $ref) {
            $parts = array_filter(explode('/', $ref));

            if (!empty($parts)) {
                if (count($parts) == 1) {
                    $condition[] = Db::parseParam('elements_sites.slug', $parts[0]);
                } else {
                    $condition[] = [
                        'and',
                        Db::parseParam('companymanagement_companytypes.handle', $parts[0]),
                        Db::parseParam('elements_sites.slug', $parts[1])
                    ];
                    $joinSections = true;
                }
            }
        }

        $this->subQuery->andWhere($condition);

        if ($joinSections) {
            $this->subQuery->innerJoin(Table::CM_COMPANYTYPES . ' companymanagement_companytypes', '[[companytypes.id]] = [[company.typeId]]');
        }
    }
}