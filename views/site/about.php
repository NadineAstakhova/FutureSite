<?php

/* @var $this yii\web\View */

use yii\helpers\Html;




$getModel = Yii::$app->session->get('serviceName');
\Yii::trace( "serviceName?", $getModel);

?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
<?php
   echo "<div class='alert alert-success' role='alert'>
        На Ваш Email отправлено письмо для подтверждения регистрации.
    </div>";
	?>

    
</div>
