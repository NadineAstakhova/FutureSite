<?php
/**
 * Created by PhpStorm.
 * User: Nadine
 * Date: 16.02.2017
 * Time: 15:00
 */
use app\models\User;

/* @var $model app\models\User */
$getModel = Yii::$app->session->get('eauthUser');
echo "<h2>Hello, ".$getModel['username']."</h2>";
$idSocialUser = Yii::$app->session->get('idSocialUser');
$get = Yii::$app->session->get('idSession');
Yii::$app->session->set('idSession', $get);
$arr = User::getUserHistory($idSocialUser)?>
<img src="<?=$getModel['photo']?>" />
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
          //  echo date_format( $dateEnd,"d.m.y H:i");
           // echo "</td>";
            echo "</tr>";
        }

        ?>

        </tbody>
    </table>

</div>