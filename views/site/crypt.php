<?php
/**
 * Created by PhpStorm.
 * User: Nadine
 * Date: 22.03.2017
 * Time: 23:06
 */
use app\models\RSA;

echo "Page with show how RSA work<br>";
$follow_text = "Some text here";
echo "Our text ".$follow_text;
$rsa = new RSA();
$rsa->generateTwoPrimeNumbers();
echo "<br>p = ".$rsa->p;
echo "<br>q = ".$rsa->q;


?>
