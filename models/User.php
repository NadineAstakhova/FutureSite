<?php

namespace app\models;

use nodge\eauth\ErrorException;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class User extends  ActiveRecord implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;
    public $email;
    public $password_hash;
    public $email_confirm_token;
    public $name;
    public $surname;
    public  $birthday;
    public $photo;
    public $social;
    /**
     * @var array EAuth attributes
     */
    public $profile;
    private static $users = [
        '100' => [
            'id' => '100',
            'username' => 'admin',
            'password' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ],
        '101' => [
            'id' => '101',
            'username' => 'demo',
            'password' => 'demo',
            'authKey' => 'test101key',
            'accessToken' => '101-token',
        ],
    ];

    const STATUS_BLOCKED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_WAIT = 2;

    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getStatusesArray(), $this->status);
    }

    public static function getStatusesArray()
    {
        return [
            self::STATUS_BLOCKED => 'Blocked',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_WAIT => 'Wait',
        ];
    }

    public function rules()
    {
        return [
            ['username', 'required'],
            ['username', 'match', 'pattern' => '#^[\w_-]+$#i'],
            ['username', 'unique', 'targetClass' => self::className(), 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 60],

            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => self::className(), 'message' => 'This email address has already been taken.'],
            ['email', 'string', 'max' => 255],

            ['name', 'required'],
            ['name', 'name'],
            ['name', 'string', 'max' => 100],

            ['surname', 'required'],
            ['surname', 'surname'],
            ['surname', 'string', 'max' => 100],

            ['status', 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::getStatusesArray())],

        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'username' => 'username',
            'name'=>'name',
            'surname'=>'surname',
            'created_at' => 'created',
            'status' => 'status',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        if (Yii::$app->getSession()->has('user-'.$id)) {
            return new self(Yii::$app->getSession()->get('user-'.$id));
        }
        else {
            return static::findOne(['id' => $id]);
            //return isset(self::$users[$id]) ? new self(self::$users[$id]) : null;
        }

    }

    public static  function findUser($id){
        $hash = (new \yii\db\Query())
            ->from('users')
            ->where('id=:id', [':id' => $id])
            ->one();
        if($hash !== null)
            return $hash;
        else
            return false;
    }


    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('findIdentityByAccessToken is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
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
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public static function getUsername($id){
        $hash = (new \yii\db\Query())
            ->select('username')
            ->from('users')
            ->where('id=:id', [':id' => $id])
            ->one();
        return $hash['username'];
    }


    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return bool
     */
    public function validatePassword($password, $username)
    {
      //  return Yii::$app->security->validatePassword($password, $this->password_hash);
        $hash = (new \yii\db\Query())
            ->from('users')
            ->where('username=:username', [':username' => $username])
            ->one();

        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash['password_hash'])){
            return static::findOne(['username'=>$username, 'password_hash'=>$password]);
        }
        else
            return Yii::$app->security->validatePassword($password, $hash['password_hash']);
    }

    /**
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

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->generateAuthKey();
            }
            return true;
        }
        return false;
    }

    /**
     * @param string $email_confirm_token
     * @return static|null
     */
    public static function findByEmailConfirmToken($email_confirm_token)
    {
        return static::findOne(['email_confirm_token' => $email_confirm_token, 'status' => self::STATUS_WAIT]);
    }

    /**
     * Generates email confirmation token
     */
    public function generateEmailConfirmToken()
    {
        $this->email_confirm_token = Yii::$app->security->generateRandomString();
    }

    /**
     * Removes email confirmation token
     */
    public function removeEmailConfirmToken($id)
    {
        $db = Yii::$app->db->createCommand();
        $db->update('users', [
            'email_confirm_token' => null,
        ], 'id=:id', [':id' => $id])->execute();
        return true;
    }

    public function insertData($social = null)
    {
        $db = Yii::$app->db->createCommand();
        if($social == null) {
            $result = $db->insert('users', [
                'username' => $this->username,
                'email' => $this->email,
                'password_hash' => $this->password_hash,
                'email_confirm_token' => $this->email_confirm_token,
                'name' => $this->name,
                'surname' => $this->surname,
                'status' => 2,
            ])->execute();
            if($result != null)
                return true;
            else
                return false;
        }
        else{
            $result = $db->insert('users', [
                'username' => $this->username,
                'email' => 'facebook',
                'password_hash' => '12345678',
                'email_confirm_token' => null,
                'name' => $this->name,
                'surname' => $this->surname,
                'status' => 1,
                'social' => $social,
            ])->execute();
            if($result != null) {
                //first enter for social user
                $user = User::findByUsername($this->username);
                User::setLastVisit($user->getId());
                return true;
            }
            else
                return false;
        }


    }

    public function updateStatus($id){
        $db = Yii::$app->db->createCommand();
        $db->update('users', [
            'status' => self::STATUS_ACTIVE,
            'email_confirm_token' => null,
        ], 'id=:id', [':id' => $id])->execute();
        return true;
    }

    public static  function getUserHistory($id)
    {
        // self::setLastVisit($id);
        $query = new Query;
        $query->select(['users_history.start_time', 'users_history.user_ip', 'users_history.end_time'])
            ->from('users_history')
            ->where(['users_history.fk_user' => $id]);
        $command = $query->createCommand();
        $usersarr = $command->QueryAll();
        return $usersarr;
    }


    public static function setLastVisit($id)
    {
        $last_visit =  date("Y-m-d H:i:s");
        $db = Yii::$app->db->createCommand();
        $result = $db->insert('users_history', [
            'start_time' => $last_visit,
            'user_ip' =>$_SERVER['REMOTE_ADDR'],
            'fk_user' => $id,
        ])->execute();


        if($result != null) {
            $hash = (new \yii\db\Query())
                ->select('id')
                ->from('users_history')
                ->where('fk_user=:fk_user', [':fk_user' => $id])
                ->andWhere('start_time=:time', [':time'=> $last_visit])
                ->one();

            Yii::$app->session->set('idSession', $hash['id'] );
            \Yii::trace( "ID?",  $hash['id']);
            return true;
        }
        else
            return false;
    }

    public static function setEndVisit($id)
    {
        $last_visit =  date("Y-m-d H:i:s");
        $db = Yii::$app->db->createCommand();
        $db->update('users_history', [
            'end_time' => $last_visit,
        ], 'id=:id', [':id' => $id])->execute();
        return true;
    }


    public static function existsSocialUser($social){
        $hash = (new \yii\db\Query())
            ->select('id')
            ->from('users')
            ->where('social=:social', [':social' => $social])
            ->one();
        Yii::$app->session->set('idSocialUser', $hash['id'] );
        if ($hash != null)
            return true;
        else
            return false;
    }
    /**
     * @param \nodge\eauth\ServiceBase $service
     * @return User
     * @throws ErrorException
     */
    public static function findByEAuth($service) {
        if (!$service->getIsAuthenticated()) {
            throw new ErrorException('EAuth user should be authenticated before creating identity.');
        }

        $id = $service->getServiceName().'-'.$service->getId();
        $attributes = [
            'id' => $id,
            'username' => $service->getAttribute('name'),
            'email' => $service->getAttribute('email'),
            'birthday' =>$service->getAttribute('birthday'),
            'photo'  =>$service->getAttribute('photo'),
            'name' => $service->getAttribute('first_name'),
            'surname' => $service->getAttribute('last_name'),
            'authKey' => md5($id),
            'profile' => $service->getAttributes(),
        ];
        $attributes['profile']['service'] = $service->getServiceName();
        Yii::$app->getSession()->set('user-'.$id, $attributes);
        //\Yii::trace( "Facebook", ArrayHelper::htmlEncode($attributes));
        return new self($attributes);
    }
}
