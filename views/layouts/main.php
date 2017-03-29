<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\models\User;
use yii\bootstrap\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\VarDumper;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'DonNU',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => array_filter([
            Yii::$app->user->isGuest ?
                ['label' => 'Home', 'url' => ['/site/main']] :
                false,
            ['label' => 'RSA', 'url' => ['/site/crypt']],

            Yii::$app->user->isGuest ?
                ['label' => 'Sign Up', 'url' => ['/site/signup']] :
                false,
            Yii::$app->user->isGuest ?
                ['label' => 'Login', 'url' => ['/site/login']] :
                false,

            !Yii::$app->user->isGuest ?
                ['label' => 'Profile', 'url' => ['/profile/index']]:
                false,
            !Yii::$app->user->isGuest ?
                // User::getUsername(Yii::$app->user->identity->getId()) Yii::$app->user->identity->username
                ['label' => isset(Yii::$app->user->identity->username)?
                    'Logout (' .  Yii::$app->user->identity->username . ')' :
                    'Logout (' . User::getUsername(Yii::$app->user->identity->getId())  . ')',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post']]:
                false,
        ]),

    ]);
    NavBar::end();
    $identity = Yii::$app->getUser()->getIdentity();
    if (isset($identity->profile)) {
      //  VarDumper::dump($identity->profile, 10, true);
    }
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>

        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
