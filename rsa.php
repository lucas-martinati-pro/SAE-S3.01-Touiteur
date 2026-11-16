<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//! Partie 1 - 1.1 : Exponentiation rapide
function decomposer(int $e): array {
    $puissances = [];
    $puissanceDe2 = 1; // Représente 1, puis 2, 4, 8, 16...

    while ($e > 0) {
        // Si le reste de la division par 2 est 1, on garde cette puissance
        if ($e % 2 === 1) {
            $puissances[] = $puissanceDe2;
        }

        // On passe à la puissance de 2 suivante
        $puissanceDe2 *= 2;

        // On divise $n par 2
        $e = (int) ($e / 2); // On peut utiliser intdiv($e, 2)
    }

    // Remet les nombres dans l'ordre décroissant (ex: 16, 4, 1)
    return array_reverse($puissances);
}

function expmod(int $x, int $e, int $n): int {
    // On décompose l'exposent en somme de puissances de 2
    $puissances = decomposer($e);

    // On calcules chaques composents du résultat modulo n
    $numbers = [];
    $val = $x;
    for ($i = 2; $i <= $puissances[0]; $i *= 2) {
        // On fait la puissance par 2
        $val *= $val;
        // On ajoute la valeur modulo n à la liste des nombres à multiplier dans le résultat seulement si la puissance est dans la liste
        if (in_array($i, $puissances)) $numbers[] = $val % $n;
    }

    // On reconstruit le résultat
    $res = 1;
    foreach ($numbers as $number) {
        $res *= $number;
        // On réalise le modulo n sur le résultat à chaque étape pour simplifier au maximum les calculs
        $res %= $n;
    }

    return $res;
}


echo "expmod(3, 12, 7) = " . expmod(3, 12, 7) . "<br>";
echo "Réponse : 1 <br>";

echo "<hr>";
//! Partie 1 - 1.2 :  PGCD (algorithme d'Euclide)
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

echo "gcd(12, 26) = " . gcd(12, 26) . "<br>";
echo "Réponse : 2 <br>";

echo "<hr>";
//! Partie 1 - 1.3 : Inverse modulaire (algorithme d'Euclide étendu)

function inversmod($e, $f): int | string
{
    $r0 = $e;
    $u0 = 1;
    $r1 = $f;
    $u1 = 0;

    while ($r1 > 0) {

        $q = (int) ($r0 / $r1);

        if ($q < 0) {
            $q * (-1);
        }

        $tempR = $r0;
        $tempU = $u0;

        $r0 = $r1;
        $u0 = $u1;

        $r1 = $tempR - $q * $r1;
        $u1 = $tempU - $q * $u1;



    }

    if ($r0 === 1) {
        return (($u0 % $f) + $f) % $f;
    } else {
        return "L'inverse modulaire n'existe pas.";

    }

}
echo "inversmod(5,26) = " . inversmod(5, 26);
echo "Réponse : 2 <br>";
echo "inversmod(2,26) = " . inversmod(5, 26);

echo "<hr>";
//! Partie 1 - 1.4 Chiffrement et déchiffrement RSA
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

    $d = inversmod($e, $phin);

    return $y**$d % $n;
}

echo "Dechiffrement de 2 avec la clé privée 5 et 17 (et e = 5) : " .  RSAdecrypt(2,5,5,17) . "<br>";

echo "<hr>";
//! Partie 1 - 1.5 : Génération de clés : le test de Miller Rabin
//? Q.1 :
// A
// B
// C

//? Q.2 :
// A
// B
// C
// D

//? Travail
// A
function estTemoin($a, $d, $s, $n) {
    $x = expmod($a,$d,$n);

    if ($x == 1 || $x == $n - 1) {
        return false;
    }

    for($i = 0 ; $i < $s - 1 ; $i++) {

        $x = ($x * $x) % $n;

        if ($x == $n - 1) {
            return false;
        }

    }

    return true;
}

// B
function millerRabin($n, $k) {

    if($n <= 1) return false; // les nombres négatifs, 0 et 1 ne sont pas premiers
    if($n == 2 || $n == 3) return true; // 2 et 3 sont premiers
    if ($n % 2 == 0) return false; // les nombres pairs ne sont forcément pas premier

    $s = 0;
    $d = $n - 1;

    while($d % 2 == 0){
        $s++;
        $d = $d/2;
    }

    for($i = 0; $i < $k ; $i++){
        $base = random_int(2,$n-2);
        if(estTemoin($base, $d, $s, $n )) {
            return false;
        }
    }
    
    return true;
}

$resMiller = (millerRabin(561,20)) ? "premier" : "composé";
echo $resMiller . "    ";
$resMiller = (millerRabin(221,20)) ? "premier" : "composé";
echo $resMiller . "    ";
$resMiller = (millerRabin(997,20)) ? "premier" : "composé";
echo $resMiller . "    ";
$resMiller = (millerRabin(7919,20)) ? "premier" : "composé";
echo $resMiller . "    ";

// C

//! Partie 2 - Exercice 1
//? Q.1 :
// A
// B
// C
// D
// E
// F
// G
// H
// I