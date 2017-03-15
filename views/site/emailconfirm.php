<?php
/**
 * Created by PhpStorm.
 * User: Nadine
 * Date: 14.02.2017
 * Time: 14:38
 */
use yii\bootstrap\Alert;

/* @var $model app\models\EmailConfirmForm */
if($model)
    echo "<div class='alert alert-success' role='alert'>
        Спасибо! Ваш Email успешно подтверждён.
    </div>";

else
    echo Alert::widget([
        'options' => [
            'class' => 'alert-danger'
        ],
        'body' => 'Ошибка! Что-то пошло не так'
    ]);
?>

