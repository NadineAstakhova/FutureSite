<?php
/**
 * Created by PhpStorm.
 * User: Nadine
 * Date: 14.02.2017
 * Time: 13:57
 */

namespace app\models;


use Swift_Plugins_LoggerPlugin;
use Swift_Plugins_Loggers_ArrayLogger;
use Yii;
use yii\base\Model;

class SignupForm extends Model
{

    public $username;
    public $email;
    public $password;
    public $name;
    public $surname;
    public $password_repeat;

    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'match', 'pattern' => '#^[\w_-]+$#i'],
            ['username', 'unique', 'targetClass' => User::className(), 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::className(), 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'required'],
            ['password_repeat', 'compare', 'compareAttribute'=>'password'],

            ['name', 'required'],
            ['name', 'string'],

            ['surname', 'required'],
            ['surname', 'string'],


        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->surname = $this->surname;
            $user->name = $this->name;
            $user->setPassword($this->password);
            $user->status = User::STATUS_WAIT;
            $user->generateAuthKey();
            $user->generateEmailConfirmToken();

            if ($user->insertData()) {
                $mail = Yii::$app->get('mailer');
                try {
                    $mail->compose('emailConfirm', ['user' => $user])
                        ->setFrom('nadine.astakhova@gmail.com')
                        ->setTo($this->email)
                        ->setSubject('Email confirmation for ' . Yii::$app->name)
                        ->send();
                    $logger = new Swift_Plugins_Loggers_ArrayLogger();
                    $mail->getSwiftMailer()->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
                    return $user;
                }
                catch (\Swift_TransportException $e) {
                    echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                }
            }
        }

        return null;
    }
}