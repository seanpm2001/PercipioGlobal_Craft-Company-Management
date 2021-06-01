<?php
/**
 * Company Management plugin for Craft CMS 3.x
 *
 * A plugin to setup companies
 *
 * @link      http://percipio.london/
 * @copyright Copyright (c) 2021 Percipio
 */

namespace percipiolondon\companymanagement\elements;

use craft\elements\actions\Delete;
use craft\elements\db\ElementQuery;
use craft\elements\User;
use craft\models\UserGroup;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\elements\db\CompanyQuery;
use percipiolondon\companymanagement\helpers\Company as CompanyHelper;
use percipiolondon\companymanagement\records\Company as CompanyRecord;

use Craft;
use DateTime;
use craft\base\Element;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use yii\base\BaseObject;
use yii\validators\Validator;

/**
 * Company Element
 *
 * Element is the base class for classes representing elements in terms of objects.
 *
 * @property FieldLayout|null      $fieldLayout           The field layout used by this element
 * @property array                 $htmlAttributes        Any attributes that should be included in the element’s DOM representation in the Control Panel
 * @property int[]                 $supportedSiteIds      The site IDs this element is available in
 * @property string|null           $uriFormat             The URI format used to generate this element’s URL
 * @property string|null           $url                   The element’s full URL
 * @property \Twig_Markup|null     $link                  An anchor pre-filled with this element’s URL and title
 * @property string|null           $ref                   The reference string to this element
 * @property string                $indexHtml             The element index HTML
 * @property bool                  $isEditable            Whether the current user can edit the element
 * @property string|null           $cpEditUrl             The element’s CP edit URL
 * @property string|null           $thumbUrl              The URL to the element’s thumbnail, if there is one
 * @property string|null           $iconUrl               The URL to the element’s icon image, if there is one
 * @property string|null           $status                The element’s status
 * @property Element               $next                  The next element relative to this one, from a given set of criteria
 * @property Element               $prev                  The previous element relative to this one, from a given set of criteria
 * @property Element               $parent                The element’s parent
 * @property mixed                 $route                 The route that should be used when the element’s URI is requested
 * @property int|null              $structureId           The ID of the structure that the element is associated with, if any
 * @property ElementQueryInterface $ancestors             The element’s ancestors
 * @property ElementQueryInterface $descendants           The element’s descendants
 * @property ElementQueryInterface $children              The element’s children
 * @property ElementQueryInterface $siblings              All of the element’s siblings
 * @property Element               $prevSibling           The element’s previous sibling
 * @property Element               $nextSibling           The element’s next sibling
 * @property bool                  $hasDescendants        Whether the element has descendants
 * @property int                   $totalDescendants      The total number of descendants that the element has
 * @property string                $title                 The element’s title
 * @property string|null           $serializedFieldValues Array of the element’s serialized custom field values, indexed by their handles
 * @property array                 $fieldParamNamespace   The namespace used by custom field params on the request
 * @property string                $contentTable          The name of the table this element’s content is stored in
 * @property string                $fieldColumnPrefix     The field column prefix this element’s content uses
 * @property string                $fieldContext          The field context this element’s content uses
 *
 * http://pixelandtonic.com/blog/craft-element-types
 *
 * @author    Percipio
 * @package   CompanyManagement
 * @since     0.1.0
 */
class Company extends Element
{
    const STATUS_LIVE = 'live';
    const STATUS_EXPIRED = 'expired';

    // Public Properties
    // =========================================================================

    public $postDate;
    public $expiryDate;
    public $siteId;

    // Company Info
    public $name;
    public $info;
    public $shortName;
    public $address;
    public $town;
    public $postcode;
    public $registerNumber;
    public $payeReference;
    public $accountsOfficeReference;
    public $taxReference;
    public $website;
    public $logo;

    // Company Manager Info
    public $contactName;
    public $contactEmail;
    public $contactRegistrationNumber;
    public $contactPhone;
    public $contactBirthday;
    public $userId;

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('company-management', 'Company');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('company-management', 'Companies');
    }

    /**
     * Returns whether elements of this type will be storing any data in the `content`
     * table (tiles or custom fields).
     *
     * @return bool Whether elements of this type will be storing any data in the `content` table.
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * Returns whether elements of this type have traditional titles.
     *
     * @return bool Whether elements of this type have traditional titles.
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * Returns whether elements of this type have statuses.
     *
     * If this returns `true`, the element index template will show a Status menu
     * by default, and your elements will get status indicator icons next to them.
     *
     * Use [[statuses()]] to customize which statuses the elements might have.
     *
     * @return bool Whether elements of this type have statuses.
     * @see statuses()
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_LIVE => Craft::t('company-management', 'Live'),
            self::STATUS_EXPIRED => Craft::t('company-management', 'Expired'),
        ];
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * Creates an [[ElementQueryInterface]] instance for query purpose.
     *
     * The returned [[ElementQueryInterface]] instance can be further customized by calling
     * methods defined in [[ElementQueryInterface]] before `one()` or `all()` is called to return
     * populated [[ElementInterface]] instances. For example,
     *
     * ```php
     * // Find the entry whose ID is 5
     * $entry = Entry::find()->id(5)->one();
     *
     * // Find all assets and order them by their filename:
     * $assets = Asset::find()
     *     ->orderBy('filename')
     *     ->all();
     * ```
     *
     * If you want to define custom criteria parameters for your elements, you can do so by overriding
     * this method and returning a custom query class. For example,
     *
     * ```php
     * class Product extends Element
     * {
     *     public static function find()
     *     {
     *         // use ProductQuery instead of the default ElementQuery
     *         return new ProductQuery(get_called_class());
     *     }
     * }
     * ```
     *
     * You can also set default criteria parameters on the ElementQuery if you don’t have a need for
     * a custom query class. For example,
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         return parent::find()->limit(50);
     *     }
     * }
     * ```
     *
     * @return ElementQueryInterface The newly created [[ElementQueryInterface]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new CompanyQuery(static::class);
    }

    /**
     * Defines the sources that elements of this type may belong to.
     *
     * @param string|null $context The context ('index' or 'modal').
     *
     * @return array The sources.
     * @see sources()
     */
   protected static function defineSources(string $context = null): array
    {
        $ids = self::_getCompanyIds();
        return [
            [
                'key' => '*',
                'label' => 'All Companies',
                'defaultSort' => ['title', 'desc'],
                'criteria' => ['id' => $ids],
            ]
        ];
    }

    protected static function defineActions(string $srouce = null): array
    {
        $actions = [];

        $elementsService = Craft::$app->getElements();

        // Delete
        $actions[] = $elementsService->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('company-management', 'Are you sure you want to delete the selected companies?'),
            'successMessage' => Craft::t('company-management', 'Companies deleted.'),
        ]);

        return $actions;
    }

    /**
     * @return array
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'id' => ['label' => Craft::t('company-management', 'ID')],
            'name' => ['label' => Craft::t('company-management', 'Name')],
            'shortName' => ['label' => Craft::t('company-management', 'Short')],
            'address' => ['label' => Craft::t('company-management', 'Address')],
            'town' => ['label' => Craft::t('company-management', 'Town')],
            'postcode' => ['label' => Craft::t('company-management', 'Postcode')],
            'registerNumber' => ['label' => Craft::t('company-management', 'Company No.')],
            'payeReference' => ['label' => Craft::t('company-management', 'PAYE No.')],
            'accountsOfficeReference' => ['label' => Craft::t('company-management', 'Accounts No.')],
            'taxReference' => ['label' => Craft::t('company-management', 'VAT No.')],
            'website' => ['label' => Craft::t('company-management', 'Url')],
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = [];
        $attributes[] = 'name';
        $attributes[] = 'dateCreated';
        $attributes[] = 'dateUpdated';

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'name' => Craft::t('company-management', 'Name'),
            'shortName' => Craft::t('company-management', 'Short'),
            'address' => Craft::t('company-management', 'Address'),
            'town' => Craft::t('company-management', 'Town'),
            'postcode' => Craft::t('company-management', 'Postcode'),
            'registerNumber' => Craft::t('company-management', 'Company No.'),
            'payeReference' => Craft::t('company-management', 'PAYE No.'),
            'accountsOfficeReference' => Craft::t('company-management', 'Accounts No.'),
            'taxReference' => Craft::t('company-management', 'VAT No.'),
            'website' => Craft::t('company-management', 'Url'),

        ];
    }

    private static function _getCompanyIds()
    {

        $companyIds = [];

        // Fetch all company UIDs
        $companyInfo = (new Query())
            ->from('{{%companymanagement_company}}')
            ->select('*')
            ->all();

        // Craft:dd( $companyInfo);
        foreach ($companyInfo as $company) {
            $companyIds[] = $company['id'];
        }
        // Craft:dd( $companyIds);

        return $companyIds;
    }

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::defineRules();

        $rules[] = [['name', 'registerNumber'], 'required'];

        // New created form
        if(null === $this->id){
            $rules[] = [['contactName', 'contactEmail', 'contactRegistrationNumber'], 'required'];

            $rules[] = ['name', function($attribute, $params, Validator $validator){
                if(count(CompanyManagement::$plugin->company->getCompanyByName($this->$attribute)) > 0 && null === $this->id) {
                    $error = Craft::t('company-management', 'The company "{value}" already exists.', [
                        'attribute' => $attribute,
                        'value' => $this->$attribute,
                    ]);

                    $validator->addError($this, $attribute, $error);
                }
            }];
            $rules[] = ['contactRegistrationNumber', function($attribute, $params, Validator $validator){

                $ssn  = strtoupper(str_replace(' ', '', $this->$attribute));
                $preg = "/^[A-CEGHJ-NOPR-TW-Z][A-CEGHJ-NPR-TW-Z][0-9]{6}[ABCD]?$/";

                if (!preg_match($preg, $ssn)) {
                    $error = Craft::t('company-management', '"{value}" is not a valid National Insurance Number.', [
                        'attribute' => $attribute,
                        'value' => $ssn,
                    ]);

                    $validator->addError($this, $attribute, $error);
                }
            }];
            $rules[] = ['contactEmail', function($attribute, $params, Validator $validator){
                $preg = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

                if (!preg_match($preg, $this->$attribute)) {
                    $error = Craft::t('company-management', '"{value}" is not a valid email address.', [
                        'attribute' => $attribute,
                        'value' => $this->$attribute,
                    ]);

                    $validator->addError($this, $attribute, $error);
                }
            }];
        }else{
            $rules[] = [['userId'], 'required'];
        }


        return $rules;
    }

    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function getIsEditable(): bool
    {
       // return \Craft::$app->user->checkPermission('edit-companies:'.$this->getType()->id);
        return true;
    }

    /**
     * Returns the field layout used by this element.
     *
     * @return FieldLayout|null
     */
    public function getFieldLayout()
    {
        return parent::getFieldLayout();
//        $tagGroup = $this->getGroup();
//
//        if ($tagGroup) {
//            return $tagGroup->getFieldLayout();
//        }
//
//        return null;
    }

    public function getGroup()
    {
        if ($this->groupId === null) {
            throw new InvalidConfigException('Tag is missing its group ID');
        }

        if (($group = Craft::$app->getTags()->getTagGroupById($this->groupId)) === null) {
            throw new InvalidConfigException('Invalid tag group ID: '.$this->groupId);
        }

        return $group;
    }

    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * Returns the HTML for the element’s editor HUD.
     *
     * @return string The HTML for the editor HUD
     */
    public function getEditorHtml(): string
    {
        $html = Craft::$app->getView()->renderTemplateMacro('commerce/products/_fields', 'general', [$this]);

        $html .= parent::getEditorHtml();

        return $html;
    }

    public function getCpEditUrl()
    {
        return 'company-management/companies/'.$this->id;
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * Performs actions before an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return void
     */
    public function afterSave(bool $isNew)
    {
        if (!$this->propagating) {
            $this->_saveRecord($isNew);
        }

        return parent::afterSave($isNew);
    }

    /**
     * Performs actions before an element is deleted.
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is deleted.
     *
     * @return void
     */
    public function afterDelete()
    {
    }

    /**
     * Performs actions before an element is moved within a structure.
     *
     * @param int $structureId The structure ID
     *
     * @return bool Whether the element should be moved within the structure
     */
    public function beforeMoveInStructure(int $structureId): bool
    {
        return true;
    }

    /**
     * Performs actions after an element is moved within a structure.
     *
     * @param int $structureId The structure ID
     *
     * @return void
     */
    public function afterMoveInStructure(int $structureId)
    {
    }

    private function _saveRecord($isNew)
    {
        if (!$isNew) {
            $record = CompanyRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid company ID: ' . $this->id);
            }
        } else {
            $record = new CompanyRecord();
            $record->id = $this->id;
            $record->contactName = $this->contactName;
            $record->contactEmail = $this->contactEmail;
            $record->contactRegistrationNumber = $this->contactRegistrationNumber;
            $record->contactPhone = $this->contactPhone;
            $record->contactBirthday = $this->contactBirthday;
        }

        $record->name = $this->name;
        $record->info = $this->info;
        $record->shortName = CompanyHelper::cleanStringForUrl($this->name);
        $record->address = $this->address;
        $record->town = $this->town;
        $record->postcode = $this->postcode;
        $record->registerNumber = $this->registerNumber;
        $record->payeReference = $this->payeReference;
        $record->accountsOfficeReference = $this->accountsOfficeReference;
        $record->taxReference = $this->taxReference;
        $record->website = $this->website;
        $record->logo = $this->logo;
        $record->userId = $this->_saveUser();

        $record->save(false);

        $this->id = $record->id;
    }

    private function _saveUser()
    {
        $companyUser = CompanyManagement::$plugin->companyUser->getCompanyUserByNin($this->contactRegistrationNumber);
        $user = null;

        if($companyUser) {
            $userId = $companyUser[0];

            // check if user exists
            $user = User::find()
                ->id($userId)
                ->anyStatus()
                ->one();
        }

        if(!$user) {

            // Make sure this is Craft Pro, since that's required for having multiple user accounts
            Craft::$app->requireEdition(Craft::Pro);

            $handle = CompanyHelper::cleanStringForUrl($this->name);

            $group = Craft::$app->getUserGroups()->getGroupByHandle($handle);

            if(null === $group)
            {
                // Create a new user group
                $userGroup = new UserGroup();
                $userGroup->name = $this->name;
                $userGroup->handle = $handle;
                Craft::$app->getUserGroups()->saveGroup($userGroup, false);

                $group = $userGroup;
            }else{
                throw new Exception('A user group with: ' . $handle . ' already exists.');
            }

            // Create a new user
            $user = new User();
            $user->username = $this->contactEmail;
            $user->email = $this->contactEmail;

            $success = Craft::$app->elements->saveElement($user, true);

            if($success) {
                $success = CompanyManagement::$plugin->companyUser->saveCompanyUser($this,$user);
            }

            if($success){
                Craft::$app->getUsers()->assignUserToGroups($user->id, [$group->id]);
                return $user->id;
            }

            return null;
        }

        return $user->id;

    }
}
