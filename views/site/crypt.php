<?php
/**
 * Created by PhpStorm.
 * User: Nadine
 * Date: 22.03.2017
 * Time: 23:06
 */
use app\models\RSA;

echo "Page with show how RSA work<br>";
$follow_text = "Some text";
echo $follow_text;

echo "<br>-----TestGenerate";
$rsaTest = new RSA();
$rsaTest->generateKey();
echo "<br>Public key is ";
$publicKey = $rsaTest->getPublicKey();
for ($i=0; $i < count($publicKey); $i++){
    echo "<br>".$publicKey[$i];
}

$prKey = $rsaTest->getPrivateKey();


echo "<br>Encode str";
echo "<br>";

$str = $rsaTest->encrypt($follow_text, $publicKey[1], $publicKey[0]);
echo "<br>Decode str";
echo "<br>";
$dec =  $rsaTest->decrypt($str, $publicKey[0]);

/*for ($i=0; $i < count( $str); $i++){
    echo  $str[$i]."<br>";
}
echo "<br>Decode str";
echo "<br>";

$dec =  $rsaTest->decrypt($str, $prKey[1],  $publicKey[1],$publicKey[0]);
for ($i=0; $i < count( $dec); $i++){
    echo  $dec[$i]."<br>";
}*/

//$rsaTest = new RSA();
?>

