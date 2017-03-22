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


    /*
     * Random generate two number and check if its are prime
     */
    public function generateTwoPrimeNumbers(){
        $this->p = rand(1,19);
        $this->q = rand(1,19);
        $check_p = $this->isPrime($this->p);
        $check_q = $this->isPrime($this->q);
        if($check_p != 2 || $check_q != 2)
            return $this->generateTwoPrimeNumbers();
        else
            return true;
    }

    function isPrime($num) {
        //1 is not prime
        if($num == 1)
            return false;

        //2 is prime (the only even number that is prime)
        if($num == 2)
            return true;

        /**
         * if the number is divisible by two, then it's not prime and it's no longer
         * needed to check other even numbers
         */
        if($num % 2 == 0) {
            return false;
        }

        /**
         * Checks the odd numbers. If any of them is a factor, then it returns false.
         * The sqrt can be an aproximation, hence just for the sake of
         * security, one rounds it to the next highest integer value.
         */
        $ceil = ceil(sqrt($num));
        for($i = 3; $i <= $ceil; $i = $i + 2) {
            if($num % $i == 0)
                return false;
        }

        return true;
    }
	function GCD(){
	}


}