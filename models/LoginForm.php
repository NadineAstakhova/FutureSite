<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            \Yii::info('validate password','user');
            Yii::getLogger()->flush(true);
            if (!$user || (($user->findByUsername($this->username))===NULL))
            {
                \Yii::trace( "username was not founded");
                $this->addError('username', "username was not found");
            }
            elseif (!$user || !$user->validatePassword($this->password, $this->username))
            {
                \Yii::trace( "password was not founded");
                $this->addError('password', $this->password);
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            //find user in db
            $this->_user = User::findByUsername($this->username);
			if(!is_null($this->_user))
            //set start time of session
            User::setLastVisit( $this->_user->getId());
        }

        return $this->_user;
    }
}
