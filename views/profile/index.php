<?php
/**
 * Created by PhpStorm.
 * User: Nadine
 * Date: 14.02.2017
 * Time: 23:19
 */
use app\models\User;
use yii\widgets\DetailView;?>



<?
//$test = Yii::$app->session->get('user');
/* @var $model app\models\User */



?>
<? DetailView::widget([
    'model' => $model,
    'attributes' => [
        'username',
        'email',
    ],
]);

echo "<h2>Hello, ".$model['username']."</h2>";


$arr = User::getUserHistory($model['id']);

?>
<h3>Your history: </h3>
<div class="container">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>IP</th>
            <th>Start session. Time</th>

        </tr>
        </thead>
        <tbody>
        <?php
        for($i = 0; $i < count($arr)-1; $i++){
            echo "<tr><td>";
            echo $arr[$i]['user_ip'];
            echo "</td><td>";
            $date = date_create($arr[$i]['start_time']);
            echo date_format( $date,"d.m.y H:i");
            echo "</td>";
           // $dateEnd = date_create($arr[$i]['end_time']);
           // echo date_format( $dateEnd,"d.m.y H:i");
           // echo "</td>";
            echo "</tr>";
        }

        ?>

        </tbody>
    </table>

</div>
