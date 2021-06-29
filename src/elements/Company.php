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
use craft\validators\DateTimeValidator;
use percipiolondon\companymanagement\CompanyManagement;
use percipiolondon\companymanagement\elements\db\CompanyQuery;
use percipiolondon\companymanagement\helpers\Company as CompanyHelper;
use percipiolondon\companymanagement\helpers\CompanyUser as CompanyUserHelper;
use percipiolondon\companymanagement\models\CompanyType;
use percipiolondon\companymanagement\models\Permissions;
use percipiolondon\companymanagement\records\Company as CompanyRecord;

use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\elements\actions\SetStatus;
use percipiolondon\companymanagement\records\CompanyUser as CompanyUserRecord;
use percipiolondon\companymanagement\records\Permission as PermissionRecord;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\base\InvalidConfigException;
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
    /**
     *
     */
    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

    /**
     * @event DefineCompanyTypesEvent The event that is triggered when defining the available company types for the company
     *
     * @see getAvailableCompanyTypes()
     * @since 3.6.0
     */
    const EVENT_DEFINE_COMPANY_TYPES = 'defineCompanyTypes';

    // Public Properties
    // =========================================================================

    /**
     * @var
     */
    public $postDate;

    /**
     * @var
     */
    public $expiryDate;

    /**
     * @var
     */
    public $siteId;

        /**
     * @var int|null Type ID
     * ---
     * ```php
     * echo $company->typeId;
     * ```
     * ```twig
     * {{ company.typeId }}
     * ```
     */
    public $typeId;

    // Company Info
    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    public $info;
    /**
     * @var
     */
    public $slug;
    /**
     * @var
     */
    public $address;
    /**
     * @var
     */
    public $town;
    /**
     * @var
     */
    public $postcode;
    /**
     * @var
     */
    public $website;
    /**
     * @var
     */
    public $logo;

    // Company Manager Info
    /**
     * @var
     */
    public $contactFirstName;
    /**
     * @var
     */
    public $contactLastName;
    /**
     * @var
     */
    public $contactEmail;
    /**
     * @var
     */
    public $contactRegistrationNumber;
    /**
     * @var
     */
    public $contactPhone;
    /**
     * @var
     */
    public $contactBirthday;
    /**
     * @var
     */
    public $userId;

    public function init()
    {
        parent::init();
        $this->typeId = 1; // TODO: Fetch this dynamically!
    }

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
    public static function lowerDisplayName(): string
    {
        return Craft::t('company-management', 'company');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('company-management', 'Companies');
    }

    /**
     * @inheritdoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('company-management', 'companies');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'company';
    }

    /**
     * Returns whether elements of this type will be storing any data in the `content`
     * table (tiles or custom fields).
     *
     * @return bool Whether elements of this type will be storing any data in the `content` table.
     */
    public static function hasContent(): bool
    {
        return true;
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
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        $status = parent::getStatus();

        return $status;
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
            self::STATUS_ENABLED => Craft::t('company-management', 'Enabled'),
            self::STATUS_DISABLED => Craft::t('company-management', 'Disabled'),
        ];
    }

    /**
     * @return bool
     */
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

    /**
     * @param string|null $srouce
     * @return array
     */
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

        //$actions[] = SetStatus::class;

        return $actions;
    }

    /**
     * @return array
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('company-management', 'Name')],
            'slug' => ['label' => Craft::t('company-management', 'Slug')],
            'address' => ['label' => Craft::t('company-management', 'Address')],
            'town' => ['label' => Craft::t('company-management', 'Town')],
            'postcode' => ['label' => Craft::t('company-management', 'Postcode')],
            'dateCreated' => ['label' => Craft::t('company-management', 'Date Created')],
        ];
    }

    /**
     * @param string $source
     * @return array
     */
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
            'slug' => Craft::t('company-management', 'Slug'),
            'address' => Craft::t('company-management', 'Address'),
            'town' => Craft::t('company-management', 'Town'),
            'postcode' => Craft::t('company-management', 'Postcode'),
            'dateCreated' => Craft::t('company-management', 'Date Created'),

        ];
    }

    /**
     * @param string $attribute
     * @return string
     * @throws InvalidConfigException
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'slug':
                // use this to customise returned values (add links / mailto's etc)
                // https://docs.craftcms.com/commerce/api/v3/craft-commerce-elements-traits-orderelementtrait.html#protected-methods
                return $this->slug;
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @return array
     */
    private static function _getCompanyIds(): array
    {

        $companyIds = [];

        // Fetch all company UIDs
        $companyInfo = (new Query())
            ->from('{{%companymanagement_companies}}')
            ->select('*')
            ->all();

        // Craft:dd( $companyInfo);
        foreach ($companyInfo as $company) {
            $companyIds[] = $company['id'];
        }

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
        $rules[] = [['typeId'], 'number', 'integerOnly' => true];
        $rules[] = [['name'], 'required'];
        $rules[] = [['postDate', 'expiryDate'], DateTimeValidator::class];

        // New created form
        if(null === $this->id){
            $rules[] = [['contactFirstName', 'contactLastName', 'contactEmail', 'contactRegistrationNumber'], 'required'];

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

                // Valid email
                if (!preg_match($preg, $this->$attribute)) {
                    $error = Craft::t('company-management', '"{value}" is not a valid email address.', [
                        'attribute' => $attribute,
                        'value' => $this->$attribute,
                    ]);

                    $validator->addError($this, $attribute, $error);
                }

                // Check if user doesn't already exists
                $user = \craft\elements\User::find()
                    ->email($this->$attribute)
                    ->one();

                if($user) {
                    $error = Craft::t('company-management', 'The user "{value}" already exists.', [
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
        if (($fieldLayout = parent::getFieldLayout()) !== null) {
            return $fieldLayout;
        }
        try {
            $companyType = $this->getType();
        } catch (InvalidConfigException $e) {
            // The company type was probably deleted
            return null;
        }

        return $companyType->getFieldLayout();
    }

    /**
     * @return mixed
     */

//    public function getCompany(): Company
//    {
//        if ($this->companyId === null) {
//            throw new InvalidConfigException('Company is missing its company element ID');
//        }
//
//        return $company;
//    }

    /**
     * Return the available company types
     *
     * @return CompanyType()
     * @throws InvalidConfigException
     * @since 1.0.0
     */
    public function getAvailableCompanyTypes(): array
    {
        $companyTypes = $this->getCompany()->getCompanyTypes();

        // Fire a 'defineCompanyTypes' event
        if ($this->hasEventHandlers(self::EVENT_DEFINE_COMPANY_TYPES)) {
            $event = newDefineCompanyTypesEvents([
                'companyTypes' => $companyTypes,
            ]);
            $this->trigger(self::EVENT_DEFINE_COMPANY_TYPES, $event);
            $companyTypes = $event->companyTypes;
        }

        return $companyTypes;
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

    /**
     * @return string
     */
    public function getCpEditUrl()
    {
        return 'company-management/companies/'.$this->id;
    }

    /**
     * @return mixed|string|null
     */
    public function getUriFormat()
    {
        $companyTypeSiteSettings = $this->getType()->getSiteSettings();

        if (!isset($companyTypeSiteSettings[$this->siteId])) {
            throw new InvalidConfigException('The "' . $this->getType()->name . '" company type is not enabled for the "' . $this->getSite()->name . '" site.');
        }

        return $companyTypeSiteSettings[$this->siteId]->uriFormat;
    }

    /**
     * @return CompanyType
     */
    public function getType(): CompanyType
    {
        $companyType = CompanyManagement::$plugin->companyTypes->getCompanyTypeByHandle('default');
        return $companyType;
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

    /**
     * @param $isNew
     * @throws Exception
     */
    private function _saveRecord($isNew)
    {

        if (!$isNew) {
            $record = CompanyRecord::findOne($this->id);

            $this->contactFirstName = $record->contactFirstName;
            $this->contactLastName = $record->contactLastName;
            $this->contactEmail = $record->contactEmail;
            $this->contactRegistrationNumber = $record->contactRegistrationNumber;
            $this->contactPhone = $record->contactPhone;
            $this->contactBirthday = $record->contactBirthday;

            if (!$record) {
                throw new Exception('Invalid company ID: ' . $this->id);
            }
        } else {
            $record = new CompanyRecord();
            $record->id = $this->id;
            $record->contactFirstName = $this->contactFirstName;
            $record->contactLastName = $this->contactLastName;
            $record->contactEmail = $this->contactEmail;
            $record->contactRegistrationNumber = $this->contactRegistrationNumber;
            $record->contactPhone = $this->contactPhone;
            $record->contactBirthday = $this->contactBirthday;
        }

        $userId = $this->_saveUser($record->id);

        $record->name = $this->name;
        $record->info = $this->info;
        $record->typeId = (int)$this->typeId;
        $record->slug = CompanyHelper::cleanStringForUrl($this->name);
        $record->address = $this->address;
        $record->town = $this->town;
        $record->postcode = $this->postcode;
        $record->website = $this->website;
        $record->userId = $userId;

        $record->save(false);

        $this->id = $record->id;

        //\Craft::dd($this->typeId);

        // save company id into the companyUser
        CompanyManagement::$plugin->companyUser->saveCompanyIdInCompanyUser($record->userId, $record->id);
    }

    /**
     * @param $companyId
     * @return int|null
     * @throws Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\WrongEditionException
     */
    private function _saveUser($companyId)
    {
        // Make sure this is Craft Pro, since that's required for having multiple user accounts
        Craft::$app->requireEdition(Craft::Pro);

//        $companyUser = CompanyUserRecord::findOne(['nationalInsuranceNumber' => $this->contactRegistrationNumber]);
        $user = User::findOne(['email' => $this->contactEmail]);

        if(!$user) {

            // Create a new user
            $user = new User();
            $user->firstName = $this->contactFirstName;
            $user->lastName = $this->contactLastName;
            $user->username = $this->contactEmail;
            $user->email = $this->contactEmail;

            $success = Craft::$app->elements->saveElement($user, true);

            if(!$success){
                throw new Exception("The user couldn't be created");
            }

        }

        //assign user to group
        $this->_saveUserToGroup($user);

        // Check if the user exists in the company user table, if not, create the entry (this is for existing users)
        $this->_updateCompanyUser($user, $companyId);

        // Give user access rights as the company admin
        $permissions = PermissionRecord::find()->asArray()->all();
        CompanyManagement::$plugin->userPermissions->savePermissions($permissions, $user->id);


        return $user->id;
    }

    /**
     * @param $user
     * @throws \Throwable
     * @throws \craft\errors\WrongEditionException
     */
    private function _saveUserToGroup($user)
    {
        //register a new group
        $handle = CompanyHelper::cleanStringForUrl($this->name);
        $group = Craft::$app->getUserGroups()->getGroupByHandle($handle);

        if(null === $group)
        {
            // Create a new user group
            $userGroup = new UserGroup();
            $userGroup->name = $this->name;
            $userGroup->handle = $handle;
            Craft::$app->getUserGroups()->saveGroup($userGroup, false);

            //@TODO: set group permissions

            $group = $userGroup;
        }

        // assign user to group
        Craft::$app->getUsers()->assignUserToGroups($user->id, [$group->id]);
    }

    /**
     * @param $user
     * @param $companyId
     */
    private function _updateCompanyUser($user, $companyId)
    {
        $companyUser = CompanyUserRecord::findOne(['userId' => $user->id]);

        if(!$companyUser) {
            $companyUser = CompanyUserHelper::populateCompanyUserFromPost($user->id, $companyId);

            $validateCompanyUser = $companyUser->validate();

            if($validateCompanyUser) {
                CompanyManagement::$plugin->companyUser->saveCompanyUser($companyUser,$user->id);
            }
        }
    }

    /*
     * GQL instantiation
     */

    /**
     * @inheritdoc
     * @since 1.0.0
     */
    public function getGqlTypeName(): string
    {
        return static::gqlTypeNameByContext($this->getType());
    }

    public static function gqlTypeNameByContext($context): string
    {
        /* @var CompanyType $context */
        return $context->handle . '_Company';
    }
    
    public static function gqlScopesByContext($context): array 
    {
        /** @var ProductType $context */
        return ['companyTypes.' . $context->uid];
    }

}
