<?php

class UserDetails extends _CActiveRecord
{
    /**
     * The followings are the available columns in table 'UserDetails':
     * @var integer $userId
     * @var string $passwordHint
     * @var string $isEmailConfirmed
     * @var string $emailConfirmationKey
     * @var string $isEmailVisible
     * @var string $isScreenNameEditable
     * @var string $firstName
     * @var string $middleName
     * @var string $lastName
     * @var string $gender
     * @var string $birthDate
     * @var string $textStatus
     * @var string $lastLoginDate
     * @var string $lastLoginGmtDate
     * @var string $lastVisitDate
     * @var string $lastVisitGmtDate
     * @var integer $totalTimeLoggedIn
     * @var string $secretQuestion
     * @var string $secretAnswer
     * @var string $adminComment
     * @var string $updateDate
     * @var string $updateGmtDate
     */
    // userId is not in safeAttributes
    //public $userId;
    public $emailConfirmationKey;
    public $isEmailVisible;

    const EMAIL_IS_CONFIRMED='1';
    const EMAIL_IS_NOT_CONFIRMED='0';
    const EMAIL_IS_VISIBLE='1';
    const EMAIL_IS_NOT_VISIBLE='0';

    /**
     * Returns the static model of the specified AR class.
     * @return CActiveRecord the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    protected function _tableName()
    {
        return 'UserDetails';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('emailConfirmationKey','length','max'=>32),
            array('firstName','length','max'=>128),
            array('middleName','length','max'=>128),
            array('lastName','length','max'=>128),
            array('secretAnswer','length','max'=>255),
            array('totalTimeLoggedIn', 'numerical', 'integerOnly'=>true),
        );
    }

    /**
     * @return array attributes that can be massively assigned
     * using something like $model->attributes=$_POST['Model'];
     */
    public function safeAttributes()
    {
        return array(
            'emailConfirmationKey',
            'isEmailVisible',
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'isEmailConfirmed'=>Yii::t('t','Email is confirmed'),
            'isEmailVisible'=>Yii::t('t','Email is visible'),
            'userId'=>'User',
            'passwordHint'=>'Password Hint',
            'emailConfirmationKey'=>'Email Confirmation Key',
            'isScreenNameEditable'=>'Is Screen Name Editable',
            'firstName'=>'First Name',
            'middleName'=>'Middle Name',
            'lastName'=>'Last Name',
            'gender'=>'Gender',
            'birthDate'=>'Birth Date',
            'textStatus'=>'Text Status',
            'lastLoginDate'=>'Last login date',
            'lastLoginGmtDate'=>'Last login date (GMT)',
            'lastVisitDate'=>'Last visit date',
            'lastVisitGmtDate'=>'Last visit date (GMT)',
            'totalTimeLoggedIn'=>'Total Time Logged In',
            'secretQuestion'=>'Secret Question',
            'secretAnswer'=>'Secret Answer',
            'adminComment'=>'Admin Comment',
            'updateDate'=>'Update date',
            'updateGmtDate'=>'Update date (GMT)',
        );
    }

    /**
     * Prepares attributes before performing validation.
     */
    protected function beforeValidate($on)
    {
        //if($on==='update')
        //{
            if(isset($this->isEmailVisible) && $this->isEmailVisible!==self::EMAIL_IS_VISIBLE && $this->isEmailVisible!==self::EMAIL_IS_NOT_VISIBLE)
                // convert empty value "" to null
                $this->isEmailVisible=null;
        //}
        return true;
    }

    /**
     * Last model processing before save in db.
     */
    protected function beforeSave()
    {
        //if($this->isNewRecord)
        //{
            $this->updateDate=date('Y-m-d H:i:s');
            $this->updateGmtDate=gmdate('Y-m-d H:i:s');
        //}
        return true;
    }

    /**
     * Returns i18n (translated) representation of the attribute value for view.
     * @param string the attribute name
     * @return string the attribute value's translation
     */
    public function getAttributeView($attribute)
    {
        switch($attribute)
        {
            case 'isEmailConfirmed':
                switch($this->isEmailConfirmed)
                {
                    case self::EMAIL_IS_CONFIRMED:
                        return Yii::t('attr','Yes (Member has confirmed indicated email)');
                        break;
                    case self::EMAIL_IS_NOT_CONFIRMED:
                        return Yii::t('attr','No (Member has not yet confirmed indicated email)');
                        break;
                    default:
                        return $this->isEmailConfirmed;
                        break;
                }
            case 'isEmailVisible':
                switch($this->isEmailVisible)
                {
                    case self::EMAIL_IS_VISIBLE:
                        return Yii::t('attr','Yes (Email is visible by all members)');
                        break;
                    case self::EMAIL_IS_NOT_VISIBLE:
                        return Yii::t('attr','No (Email is not visible by other members)');
                        break;
                    case null:
                        return Yii::t('attr','By default (Email is not visible by other members)');
                        break;
                    default:
                        return $this->isEmailVisible;
                        break;
                }
            default:
                return $this->$attribute;
        }
    }

    /**
     * Returns data array of the attribute for create/update.
     * @param string the attribute name
     * @return array the attribute's data
     */
    public function getAttributeData($attribute)
    {
        switch($attribute)
        {
            case 'isEmailConfirmed':
                return array(
                    self::EMAIL_IS_NOT_CONFIRMED=>Yii::t('attr','No (Member has not yet confirmed indicated email)'),
                    self::EMAIL_IS_CONFIRMED=>Yii::t('attr','Yes (Member has confirmed indicated email)'),
                );
            case 'isEmailVisible':
                return array(
                    null=>Yii::t('attr','By default (Email is not visible by other members)'),
                    self::EMAIL_IS_NOT_VISIBLE=>Yii::t('attr','No (Email is not visible by other members)'),
                    self::EMAIL_IS_VISIBLE=>Yii::t('attr','Yes (Email is visible by all members)'),
                );
            default:
                return $this->$attribute;
        }
    }

    /**
     * Whether user email is visible by all members.
     * @return bool
     */
    public function isEmailVisible()
    {
        return $this->isEmailVisible==='1';
    }
}