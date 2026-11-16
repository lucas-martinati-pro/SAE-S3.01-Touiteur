<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//! Partie 1 - 1.1 : Exponentiation rapide
function expmod(int $x, int $e, int $n): int {
    $res = 1;
    // On prend le modulo de x par n
    $val = $x % $n;

    // On parcours le binaire de $e directement
    while ($e > 0) {
        // Si c'est une puissance de 2, alors on ajoute la valeur
        if ($e % 2 === 1) {
            $res = ($res * $val) % $n;
        }

        $val = ($val**2) % $n;
        $e = (int) ($e / 2);
    }

    return $res;
}


echo "expmod(3, 12, 7) = " . expmod(3, 12, 7) . "<br>";
echo "Réponse : 1 <br>";

echo "<hr>";
//! Partie 1 - 1.2 : PGCD (algorithme d'Euclide)
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
echo "Réponse : 2<br>";

echo "<hr>";
//! Partie 1 - 1.3 : Inverse modulaire (algorithme d'Euclide étendu)

function inversemod(int $e, int $f): int | string {
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
echo "inversemod(5,26) = " . inversemod(5, 26) . "<br>";
echo "Réponse : 21<br>";
echo "inversemod(2,26) = " . inversemod(5, 26) . "<br>";
echo "Réponse : L'inverse modulaire n'existe pas.<br>";

echo "<hr>";
//! Partie 1 - 1.4 : Chiffrement et déchiffrement RSA
function RSAencrypt(int $x, int $e, int $n){
    return $x**$e % $n;
}

echo "Chiffrement de 3 avec la clé public 5 et 85 : " .  RSAencrypt(3,5,85) . "<br>";

function RSAdecrypt(int $y, int $e, int $p, int $q){
    $n = $p * $q;
    $phin = ($p - 1) * ($q - 1);

    if (gcd($e, $phin) != 1) {
        throw new Exception("Erreur RSA : L'exposant e ($e) n'est pas premier avec phi(n) ($phin). La clé est invalide.");
    }

    $d = inversemod($e, $phin);

    return $y**$d % $n;
}

echo "Dechiffrement de 2 avec la clé privée 5 et 17 (et e = 5) : " .  RSAdecrypt(2,5,5,17) . "<br>";

echo "<hr>";
//! Partie 1 - 1.5 : Génération de clés : le test de Miller Rabin
//? Q.1 :
// A. D'après le Petit Théorème de Fermat : Soit p un nombre premier, alors ∀a ∈ ℤ, a^p ≡ a [p].
// De plus, si p ne divise pas a (c'est-à-dire PGCD(a, p) = 1), alors a admet un inverse modulo p, noté a^(-1).
// En multipliant par a^(-1), on obtient :
// a^p * a^(-1) ≡ a * a^(-1) [p] <=> a^(p - 1) ≡ 1 [p] car a * a^(-1) = 1

// B.
function nombreTemoinsFermat(int $n): int {
    $nbBases = 0;
    for ($a = 2; $a < $n; $a++) {
        // a doit être premier avec n, donc PGCD(a, n) = 1
        if (gcd($a, $n) === 1 && expmod($a, $n - 1, $n) !== 1) {
            $nbBases++;
        }
    }
    return $nbBases;
}

echo "nombreTemoinsFermat(561) = " . nombreTemoinsFermat(561) . "<br>";
echo "Réponse : 0<br>";
echo "nombreTemoinsFermat(15) = " . nombreTemoinsFermat(15) . "<br>";

// C. On peut donc constaté que le test de Fermat est incapable de détecter que 561 est composé. En effet, nôtre algorithme nombreTemoinsFermat
// indique 0 pour la valeur 561 alors que 561 = 3 * 11 * 17.

//? Q.2 :
// A. Par hypothèse, n est premier et a est premier avec n.
// D'après le petit théorème de Fermat (démontré en Q1), on a donc a^(n-1) ≡ 1 [n].
// Or, par définition, n - 1 = 2^s * d, d'où a^(2^s * d) = a^(n-1) ≡ 1 [n].

// B. on a x^2 ≡ 1 [n]
// <=> n | (x^2 - 1)
// <=> n | (x - 1)(x + 1)
// D'après le Lemme d'Euclide : Soit p un nombre premier, alors ∀b, c ∈ ℤ, p | bc <=> p | b ou p | c.
// Donc n | (x - 1) ou n | (x + 1)
// Donc x - 1 ≡ 0 [n] ou x + 1 ≡ 0 [n]
// Donc x ≡ 1 [n] ou x ≡ -1 [n].

// C. On cherche à montrer que a^d ≡ 1 [n] ou qu'il existe r ∈ {0, ..., s - 1} tel que a^(2^r *d) ≡ 1 [n]
// Supposont qu'il existe aucun r ∈ {0, ..., s - 1} tel que a^(2^r *d) ≡ 1 [n].
// On sait d'après le petit théorème de Fermet que a^(2^s *d) ≡ 1 [n], donc (a^(2^(s - 1) *d))^2 ≡ 1 [n]
// Donc, d'après la propriété démontré en (B), on a donc :
// a^(2^(s - 1) *d) ≡ 1 [n] ou a^(2^(s - 1) *d) ≡ -1 [n]
// Or par hypothèse, aucun terme de la forme a^(2^r *d) (pour r ∈ {0, ..., s - 1}) n'est congu à -1 (mod n). On en déduit donc que a^(2^(s - 1) *d) ≡ 1 [n].
// En répétant ce raisonnement de proche en proche (pour r = s - 2, s - 3, ... jusqu'à r = 0), chaque terme étant le carré du précédent et ne pouvant
// jamais être congru à -1 (mod n), tous les termes valent 1 (mod n), jusqu'au premier terme de la suite : a^(2^0 *d) = a^d ≡ 1 [n].

// Ainsi, si aucun terme ne vaut - 1 (mod n), on a nécessairement a^d ≡ 1 [n].
// Dans tout les cas, l'une des conditions est bien vérifiés.

// D.

//? Travail
// A.
function estTemoin(int $a, int $d, int $s, int $n) {
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

// B.
// C.

//! Partie 2 - Exercice 1
//? Q.1 :
// A.
// B.
// C.
// D.
// E.
// F.
// G.
// H.
// I.