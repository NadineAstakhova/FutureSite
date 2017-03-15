<?php
/**
 * Created by PhpStorm.
 * User: Nadine
 * Date: 14.02.2017
 * Time: 14:06
 */

namespace app\models;


use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;

class EmailConfirmForm  extends Model
{
    /**
     * @var User
     */
    private $_user;

    /**
     * Creates a form model given a token.
     *
     * @param  string $token
     * @param  array $config
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('Отсутствует код подтверждения.');
        }

        $this->_user = User::findByEmailConfirmToken($token);
        if (!$this->_user) {
            throw new InvalidParamException('Неверный токен.'.$token);
        }
        parent::__construct($config);
    }

    /**
     * Confirm email.
     *
     * @return boolean if email was confirmed.
     */
    public function confirmEmail()
    {
        \Yii::info('action email',$this->_user->getId());
        $user = $this->_user;
        $user->updateStatus($user->getId());

        return true;
    }

}