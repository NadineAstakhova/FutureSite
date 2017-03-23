<?php
/**
 * Created by PhpStorm.
 * User: Nadine
 * Date: 22.03.2017
 * Time: 23:09
 */

namespace app\models;


class RSA
{
    public $publicKey;
    private $privateKey;
    public $p;
    public $q;
    public $n;
    public $fi;


    public function generateKey(){
        $this->generateTwoPrimeNumbers();
        echo " ".$this->p. " ". $this->q. "<br>";
        $this->fi = $this->findFI($this->p, $this->q);
        echo  "<br> FI ";
        echo  $this->fi;
        $x = $this->findX( $this->fi);
        echo  "<br>X ";
        echo $x;
        $y = $this->findY($x,  $this->fi);
        echo  "<br>Y ";
        echo $y;
        $this->setPublicKey($x);
        $this->setPrivateKey($y);
    }


    /*
     * Random generate two number and check if its are prime
     */
    public function generateTwoPrimeNumbers(){
        $this->p = rand(1,1000);
        $this->q = rand(1,1000);

        //if ($this->p == $this->q || !$this->isPrime($this->p, 10) || !$this->isPrime($this->q, 10))
        if ($this->p == $this->q || gmp_prob_prime($this->p) == 0 || gmp_prob_prime($this->q) == 0)
            return $this->generateTwoPrimeNumbers();
        else
            return  $this->p;
    }

    //Miller Rabin Primality Test
    function isPrime($num, $k)
    {
        //1 is not prime
        if ($num == 2)
            return true;

        //2 is prime (the only even number that is prime)
        if ($num == 2)
            return true;
        if ($num < 2 || $num % 2 == 0)
            return false;

        $d = $num - 1;
        $s = 0;

        while ($d % 2 == 0) {
            $d /= 2;
            $s++;
        }

        for ($i = 0; $i < $k; $i++) {
            $a = rand(2, $num - 1);

            //bcpowmod — Raise an arbitrary precision number to another, reduced by a specified modulus
            $x = bcpowmod($a, $d, $num);
            if ($x == 1 || $x == $num - 1)
                continue;

            for ($j = 1; $j < $s; $j++) {
                //bcdmod - Get modulus of an arbitrary precision number
                //bcmul — Multiply two arbitrary precision numbers
                $x = bcmod(bcmul($x, $x), $num);
                if ($x == 1)
                    return false;
                if ($x == $num - 1)
                    continue 2;
            }
            return false;
        }
        return true;
    }

    function findFI($p, $q){
        $this->n = $p * $q;
        echo "<br>N ";
        echo $this->n;
        $fi = ($p - 1)*($q - 1);
        return $fi;
    }


    function findX($fi){
        $x = rand();
        if($x >= 2 && $x < $fi) {
            if($this->GCD($x, $fi) == 1)
                return $x;
            else
                return $this->findX($fi);
        }
        else
            return $this->findX($fi);

    }


	function GCD($a, $b){
        if ($a == 0)
            return $b;

        if ($b == 0)
            return $a;

        $large = $a > $b ? $a: $b;
        $small = $a > $b ? $b: $a;
        $remainder = $large % $small;
        return 0 == $remainder ? $small : $this->GCD( $small, $remainder );
	}

	function findY($x, $fi){
        $res = gmp_gcdext($x, $fi);
        $y = gmp_strval($res['s']);
        if( $y < 0)
            $y += $fi;
        return $y;

    }

    function setPublicKey($x){
	    $this->publicKey = array($this->n, $x);
    }

    public function getPublicKey(){
        return $this->publicKey;
    }

    private function setPrivateKey($y){
        $this->privateKey = array($this->n, $y);
    }

    public function getPrivateKey(){
        return $this->privateKey;
    }

    public function encrypt($str, $x, $n){
       // $m = $this->string_to_int($str);
        $arr = str_split($str);
        $m = array();
        for ($i = 0; $i < count($arr); $i++){
            $v = ord($arr[$i]);
            echo $v." ";
            $m[$i] = bcpowmod($v, $x, $n);

        }
        echo "<br>";
       // $code   = bcpowmod($m, $x, $n);
        return $m;
    }

    public function decrypt ($c, $y, $x, $n) {

        $code = array();
        $xy = $x * $y;
        for ($i = 0; $i < count($c); $i++){
           // $code[$i] = bcpowmod($c[$i], $y * $x, $n);
            //echo $c[$i]. " ".$xy;
            $code[$i] = chr(bcmod(gmp_pow($c[$i], $y), $this->n));
            echo $code[$i];

            //$code[$i] =bcmod(bcpow($c[$i], $xy), $this->n);
        }



        return $code;
    }

    /*
    * ENCRYPT function returns
    * X = M^E (mod N)
    */

    /* ФУНКЦИЯ ШИФРОВАНИЯ */
    public function encrypt2 ($m, $e, $n, $s=3) {
        $coded   = '';
        $max     = strlen( $this->fi);
        $packets = ceil($max/$s);

        for($i=0; $i<$packets; $i++){
            $packet = substr($this->fi, $i*$s, $s);
            $code   = '0';

            for($j=0; $j<$s; $j++){
                $code = bcadd($code, bcmul(ord($packet[$j]), bcpow('256',$j)));
            }

            /* возводим число $code в степень $e и получаем остаток от деление на $n*/
            $code   = bcpowmod($code, $e, $n);
            $coded .= $code.' ';
        }

        //return trim($coded);
        return trim($coded);
    }

    /*
   ENCRYPT function returns
   M = X^D (mod N)
   */
    public function decrypt2 ($c, $d, $n) {

        $coded   = split(' ', $c);
        $message = '';
        $max     = 1;//count($coded);

        for($i=0; $i<$max; $i++){
            $code = bcpowmod($coded[$i], $d, $n);

            while(bccomp($code, '0') != 0){
                $ascii    = bcmod($code, '256');
                $code     = bcdiv($code, '256', 0);
                $message .= chr($ascii);
            }
        }

        return $message;
    }

    function string_to_int($str)
    {
        $numbers = array();
        foreach (str_split($str) as $chr) {
            $numbers[] = sprintf('%03d', ord($chr));
        }
        return $numbers = join($numbers);
    }

    function int_to_string($numbers) // back from  string_to_int
    {
        return join(array_map('chr', str_split($numbers, 3)));
    }


}