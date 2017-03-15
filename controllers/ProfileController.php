<?php
/**
 * Created by PhpStorm.
 * User: Nadine
 * Date: 14.02.2017
 * Time: 23:26
 */

namespace app\controllers;


use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class ProfileController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    public function actionIndex()
    {
            $model = $this->findModel();
            return $this->render('index', [
                'model' => $model,
            ]);
    }



    /**
     * @return User the loaded model
     */
    private function findModel()
    {
        return User::findUser(Yii::$app->user->identity->getId());
    }
}