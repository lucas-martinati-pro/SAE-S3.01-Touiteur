<?php

function gcd(int $a, int $b) : int {
    
    if ($b > $a){
        $tmp = $b;
        $b = $a;
        $a = $tmp;
    }

    while ($b != 0){
        $reste = $a % $b;
        $a = $b;
        $b = $reste;
    }

    return $a;
}

echo gcd(12, 26);

function RSAencrypt($x, $e, $n){
    return $x**$e % $n;
}

echo "Chiffrement de 3 avec la clé public 5 et 85 : " .  RSAencrypt(3,5,85) . "<br>";

function RSAdecrypt($y, $e, $p, $q){
    $n = $p * $q;
    $phin = ($p - 1) * ($q - 1);

    if (gcd($e, $phin) != 1) {
        throw new Exception("Erreur RSA : L'exposant e ($e) n'est pas premier avec phi(n) ($phin). La clé est invalide.");
    }

    $d = inversermod($e, $phin);

    return $y**$d % $n;
}

echo "Dechiffrement de 2 avec la clé privée 5 et 17 : " .  RSAdecrypt(2,5,5,17) . "<br>";
