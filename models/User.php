<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $password_reset_token
 * @property string $password_hash
 * @property string $auth_key
 * @property string $full_name
 * @property string $email
 * @property int $dob
 * @property string $description
 * @property double $shown_balance
 * @property int $created_at
 * @property int $updated_at
 * @property int $status
 * @property string $phone
 *
 * @property Advertiser[] $advertisers
 * @property Lead[] $leads
 * @property Ticket[] $tickets
 * @property Ticket[] $tickets0
 * @property TicketMessage[] $ticketMessages
 * @property Transaction[] $transactions
 * @property Transaction[] $transactions0
 * @property UserAuthAssignment[] $userAuthAssignments
 * @property Webmaster[] $webmasters
 */

class User extends \yii\db\ActiveRecord implements IdentityInterface
{

    const STATUS_DELETED = 0;
    const STATUS_FULL_DELETED = -1;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username','email','password'],'required'],

            //[['username', 'password', 'full_name', 'email', 'dob', 'description', 'shown_balance', 'created_at', 'updated_at', 'status'], 'required'],
            [['password', 'full_name', 'description'], 'string'],
            [['dob', 'created_at', 'updated_at', 'status'], 'integer'],
            [['shown_balance'], 'number'],
            [['username', 'password_reset_token', 'password_hash', 'auth_key', 'email'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'full_name' => Yii::t('app', 'Full Name'),
            'email' => Yii::t('app', 'Email'),
            'dob' => Yii::t('app', 'Dob'),
            'description' => Yii::t('app', 'Description'),
            'shown_balance' => Yii::t('app', 'Shown Balance'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'status' => Yii::t('app', 'Status'),
            'phone' => Yii::t('app', 'Phone'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertisers()
    {
        return $this->hasMany(Advertiser::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeads()
    {
        return $this->hasMany(Lead::className(), ['callcenter_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::className(), ['support_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTickets0()
    {
        return $this->hasMany(Ticket::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTicketMessages()
    {
        return $this->hasMany(TicketMessage::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::className(), ['from_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions0()
    {
        return $this->hasMany(Transaction::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAuthAssignments()
    {
        return $this->hasMany(UserAuthAssignment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebmasters()
    {
        return $this->hasMany(Webmaster::className(), ['user_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    public static function findByPhone($phone)
    {
        if (empty(static::findOne(['phone' => $phone, 'status' => self::STATUS_ACTIVE]))){
            return static::findOne(['phone' => '+7'.$phone, 'status' => self::STATUS_ACTIVE]);
        }
        else
            return static::findOne(['phone' => $phone, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $validatePassword = false;
        if(!isset($this->password_hash) || empty($this->password_hash)){
            if(md5('%'.$password.'%') == $this->password){
                $validatePassword = true;
                $this->password_hash = Yii::$app->security->generatePasswordHash($password);
                $this->auth_key = Yii::$app->security->generateRandomString();
                $allRoles = \Yii::$app->authManager->getRolesByUser($this->id);
                if(!isset($allRoles) || empty($allRoles)){
                    $auth = Yii::$app->authManager;
                    $userRole = $auth->getRole('user');
                    $auth->assign($userRole, $this->id);
                }
                if(!$this->save()){
                    //print_r($this->errors);die();
                };
            }
        }else{
            $validatePassword = Yii::$app->security->validatePassword($password, $this->password_hash);
        }
        return $validatePassword;
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function getUsersPays(){
        return $this->hasMany(Transactions::className(), ['id' => 'user_id']);
    }

    public function getPromoCodes(){
        return Codes::find()->where(['user_id' => $this->id])->all();
    }
}
