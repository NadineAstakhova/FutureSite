<?php

namespace app\controllers;

use app\models\EmailConfirmForm;
use app\models\SignupForm;
use app\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout','signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
            'eauth' => array(
                // required to disable csrf validation on OpenID requests
                'class' => \nodge\eauth\openid\ControllerBehavior::className(),
                'only' => array('login'),
            ),
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return Yii::$app->user->isGuest ?
            $this->redirect(['main'], 301):
            $this->redirect(['profile/index'], 301);
    }

    public function actionMain()
    {
        return $this->render('main');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $serviceName = Yii::$app->getRequest()->getQueryParam('service');
        Yii::$app->session->set('serviceName', $serviceName);

            if (isset($serviceName)) {
                /** @var $eauth \nodge\eauth\ServiceBase */
                $eauth = Yii::$app->get('eauth')->getIdentity($serviceName);
                $eauth->setRedirectUrl(Yii::$app->getUrlManager()->createAbsoluteUrl('site/indexface'));
                $eauth->setCancelUrl(Yii::$app->getUrlManager()->createAbsoluteUrl('site/login'));
                try {
                    if ($eauth->authenticate()) {
                        var_dump($eauth->getIsAuthenticated(), $eauth->getAttributes());

                        Yii::$app->session->set('eauthId', $eauth);
                        $identity = User::findByEAuth($eauth);
                        Yii::$app->getUser()->login($identity);

                        Yii::$app->session->set('eauth', $identity->getAttributes());
                        $str =  substr($identity['id'], strpos($identity['id'], '-') + 1, strlen($identity['id']));
                        //if this social user already exists
                        if(User::existsSocialUser($str)){
                            $user = User::findByUsername($identity['username']);
                            //set new start session time
                            User::setLastVisit($user->getId());
                        }

                        \Yii::trace("eauthId?", "tut");

                        // special redirect with closing popup window
                        $eauth->redirect();
                    } else {
                        // close popup window and redirect to cancelUrl
                        $eauth->cancel();
                    }
                } catch (\nodge\eauth\ErrorException $e) {
                    // save error to show it later
                    Yii::$app->getSession()->setFlash('error', 'EAuthException: ' . $e->getMessage());
                    echo $e->getMessage();
                }
            }


        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->session->set('eauthUser', "false");
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                Yii::$app->getSession()->setFlash('success', 'Подтвердите ваш электронный адрес.');
                //chose correct page
                return $this->render('about');
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }


    public function actionEmailconfirm($token){
        try {
            $model = new EmailConfirmForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->confirmEmail()) {
            Yii::$app->getSession()->setFlash('success', 'Спасибо! Ваш Email успешно подтверждён.');
            return $this->render('emailconfirm', ['model' => $model]);
        } else {
            Yii::$app->getSession()->setFlash('error', 'Ошибка подтверждения Email.');
        }

    }

    //enter for social user profile
    public function actionIndexface(){
        $getId = Yii::$app->session->get('eauthId');
        $model = User::findByEAuth($getId);
        Yii::$app->getUser()->login($model);
        Yii::$app->session->set('eauthUser',$model);
        $str =  substr($model['id'], strpos($model['id'], '-') + 1, strlen($model['id']));
        if(!User::existsSocialUser($str)) {
            $userF = new User();
            $userF->name = $model['name'];
            $userF->surname = $model['surname'];
            $userF->username = $model['username'];
            $userF->insertData($str);
        }
        //$idSocialUser = Yii::$app->session->get('idSocialUser');
        //User::setLastVisit($idSocialUser);
        return $this->render('indexface');
    }

    public function actionCrypt(){
        return $this->render('crypt');
    }


}
