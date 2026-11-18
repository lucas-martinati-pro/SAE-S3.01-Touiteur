<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

debut();

title("Partie 1 - 1.1 : Exponentiation rapide");
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

show("expmod(3, 12, 7) = ", expmod(3, 12, 7));
show("Réponse : 1");

title("Partie 1 - 1.2 : PGCD (algorithme d'Euclide)");
function gcd(int $a, int $b): int {
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

show("gcd(12, 26) = ", gcd(12, 26));
show("Réponse : 2");

title("Partie 1 - 1.3 : Inverse modulaire (algorithme d'Euclide étendu)");
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
        return "L'inverse modulaire de $e mod $f n'existe pas.";
    }
}

show("inversemod(5, 26) = ", inversemod(5, 26));
show("Réponse : 21");
show("inversemod(2, 26) = ", inversemod(2, 26));
show("Réponse : L'inverse modulaire de 2 mod 26 n'existe pas.");

title("Partie 1 - 1.4 : Chiffrement et déchiffrement RSA");
function RSAencrypt(int $x, int $e, int $n): int {
    return expmod($x, $e, $n);
}

show("Chiffrement de 3 avec la clé public 5 et 85 : ", RSAencrypt(3,5,85));
show("Réponse : 73");

function RSAdecrypt(int $y, int $e, int $p, int $q): int {
    $n = $p * $q;
    $phin = ($p - 1) * ($q - 1);

    if (gcd($e, $phin) != 1) {
        throw new Exception("Erreur RSA : L'exposant e ($e) n'est pas premier avec φ(n) ($phin). La clé est invalide.");
    }

    $d = inversemod($e, $phin);

    return expmod($y, $d, $n);;
}

show("Dechiffrement de 2 avec la clé privée 5 et 17 (et e = 5) : ", RSAdecrypt(2,5,5,17));
show("Réponse : 32");

title("Partie 1 - 1.5 : Génération de clés : le test de Miller Rabin");
subtitle("Q.1");
question("A.");
show(<<<HTML
D'après le Petit Théorème de Fermat : Soit p un nombre premier, alors ∀a ∈ ℤ, a^p ≡ a [p].
De plus, si p ne divise pas a (c'est-à-dire PGCD(a, p) = 1), alors a admet un inverse modulo p, noté a^(-1).
En multipliant par a^(-1), on obtient :
a^p * a^(-1) ≡ a * a^(-1) [p]
<=> a^(p - 1) ≡ 1 [p] car a * a^(-1) = 1
HTML);

question("B.");
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

show("nombreTemoinsFermat(561) : ", nombreTemoinsFermat(561));
show("Réponse : 0");
show("nombreTemoinsFermat(15) : ", nombreTemoinsFermat(15));
show("Réponse : 4");

question("C.");
show(<<<HTML
On peut donc constaté que le test de Fermat est incapable de détecter que 561 est composé. En effet, nôtre algorithme nombreTemoinsFermat
indique 0 pour la valeur 561 alors que 561 = 3 * 11 * 17.
HTML);

subtitle("Q.2");
question("A.");
show(<<<HTML
Par hypothèse, n est premier et a est premier avec n.
D'après le petit théorème de Fermat (démontré en Q1), on a donc a^(n-1) ≡ 1 [n].
Or, par définition, n - 1 = 2^s * d, d'où a^(2^s * d) = a^(n-1) ≡ 1 [n].
HTML);

question("B.");
show(<<<HTML
On a x^2 ≡ 1 [n]
<=> n | (x^2 - 1)
<=> n | (x - 1)(x + 1)
D'après le Lemme d'Euclide : Soit p un nombre premier, alors ∀b, c ∈ ℤ, p | bc <=> p | b ou p | c.
Donc n | (x - 1) ou n | (x + 1)
Donc x - 1 ≡ 0 [n] ou x + 1 ≡ 0 [n]
Donc x ≡ 1 [n] ou x ≡ -1 [n].
HTML);

question("C.");
show(<<<HTML
On cherche à montrer que a^d ≡ 1 [n] ou qu'il existe r ∈ {0, ..., s - 1} tel que a^(2^r *d) ≡ 1 [n]
Supposont qu'il existe aucun r ∈ {0, ..., s - 1} tel que a^(2^r *d) ≡ 1 [n].
On sait d'après le petit théorème de Fermet que a^(2^s *d) ≡ 1 [n], donc (a^(2^(s - 1) *d))^2 ≡ 1 [n]
Donc, d'après la propriété démontré en (B), on a donc :
a^(2^(s - 1) *d) ≡ 1 [n] ou a^(2^(s - 1) *d) ≡ -1 [n]
Or par hypothèse, aucun terme de la forme a^(2^r *d) (pour r ∈ {0, ..., s - 1}) n'est congu à -1 (mod n). On en déduit donc que a^(2^(s - 1) *d) ≡ 1 [n].
En répétant ce raisonnement de proche en proche (pour r = s - 2, s - 3, ... jusqu'à r = 0), chaque terme étant le carré du précédent et ne pouvant
jamais être congru à -1 (mod n), tous les termes valent 1 (mod n), jusqu'au premier terme de la suite : a^(2^0 *d) = a^d ≡ 1 [n].

Ainsi, si aucun terme ne vaut - 1 (mod n), on a nécessairement a^d ≡ 1 [n].
Dans tout les cas, l'une des conditions est bien vérifiés.
HTML);

title("Travail");
question("A.");
function estTemoin(int $a, int $d, int $s, int $n): bool {
    $x = expmod($a, $d, $n);

    if ($x == 1 || $x == $n - 1) {
        return false;
    }

    for ($i = 0; $i < $s - 1; $i++) {
        $x = $x**2 % $n;

        if ($x == $n - 1) {
            return false;
        }
    }

    return true;
}

question("B.");
function millerRabin(int $n, int $k): bool {
    if ($n <= 1) return false; // les nombres négatifs, 0 et 1 ne sont pas premiers
    if ($n == 2 || $n == 3) return true; // 2 et 3 sont premiers
    if ($n % 2 == 0) return false; // les nombres pairs sont forcément pas premier

    $s = 0;
    $d = $n - 1;

    // On compte le nombre de division possible de d par 2 avant que d ne soit plus pair
    while ($d % 2 == 0) {
        $s++;
        $d = $d/2;
    }

    // On test k fois n sur la base
    for ($i = 0; $i < $k; $i++) {
        $base = random_int(2, $n - 2);
        if (estTemoin($base, $d, $s, $n)) {
            return false;
        }
    }

    return true;
}

question("C.");
$resMiller = (millerRabin(561,20)) ? "premier" : "composé";
show("Test avec 561 et 20 : ", $resMiller);
$resMiller = (millerRabin(221,20)) ? "premier" : "composé";
show("Test avec 221 et 20 : ", $resMiller);
$resMiller = (millerRabin(997,20)) ? "premier" : "composé";
show("Test avec 997 et 20 : ", $resMiller);
$resMiller = (millerRabin(7919,20)) ? "premier" : "composé";
show("Test avec 7919 et 20 : ", $resMiller);

question("D.");
// version du premierAleatoire sans l'utilisation de GMP (utilisation de nos méthode)
function premierAleatoireNoGMP(int $min, int $max): int {
    $number = random_int($min, $max);
    while (!millerRabin($number, 20)) {
        $number = random_int($min, $max);
    }
    return $number;
}

// version du genererClesRSA sans l'utilisation de GMP (utilisation de nos méthode)
function genererClesRSANoGMP(int $min, int $max): array {
    // Définition des nombres premier aléatoires
    $primeN = [premierAleatoireNoGMP($min, $max), premierAleatoireNoGMP($min, $max)];
    // On vérifie que les p et q soient différents
    while ($primeN[0] === $primeN[1]) {
        $primeN = [premierAleatoireNoGMP($min, $max), premierAleatoireNoGMP($min, $max)];
    }
    // Calcule de n;
    $n = $primeN[0] * $primeN[1];
    // Calcule de φ(n)
    $phin = ($primeN[0] - 1) * ($primeN[1] - 1);

    // On choisie un e aléatoire supérieur à 1 et infèrieur à φ(n) car supérieur ne sert à rien.
    $e = random_int(1, $phin - 1);
    // Et on vérifie qu'il est bien premier avec n
    while (gcd($phin, $e) !== 1) {
        $e = random_int(1, $phin - 1);
    }

    // On récupère l'inverse de e par φ(n)
    $d = inversemod($e, $phin);

    return ['p' => $primeN[0], 'q' => $primeN[1], 'n' => $n, 'phi(n)' => $phin, 'e' => $e, 'd' => $d];
}

// version du premierAleatoire avec l'utilisation de GMP
function premierAleatoire(int $min, int $max): GMP {
    $number = gmp_random_range($min, $max);
    while (gmp_prob_prime($number, 20) === 0) {
        $number = gmp_random_range($min, $max);
    }
    return $number;
}

// version du genererClesRSA avec l'utilisation de GMP
// Pour utiliser la fonction avec des nombres sur 4048 bits, on met en paramètres soit des GMP, soit des string en plus.
function genererClesRSA(GMP|string|int $min, GMP|string|int $max): array {
    // Définition des nombres premier aléatoires
    $primeN = [premierAleatoire($min, $max), premierAleatoire($min, $max)];
    // On vérifie que les p et q soient différents
    while (gmp_cmp($primeN[0], $primeN[1]) === 0) {
        $primeN = [premierAleatoire($min, $max), premierAleatoire($min, $max)];
    }

    // Calcule de n;
    $n = $primeN[0] * $primeN[1];
    // Calcule de φ(n)
    $phin = ($primeN[0] - 1) * ($primeN[1] - 1);

    // On choisie un e aléatoire supérieur à 1 et infèrieur à φ(n) car supérieur ne sert à rien.
    $e = gmp_random_range(2, $phin - 1);
    // Et on vérifie qu'il est bien premier avec n
    while (gmp_gcd($phin, $e) != 1) {
        $e = gmp_random_range(1, $phin - 1);
    }

    // On récupère l'inverse de e par φ(n)
    $d = gmp_invert($e, $phin);

    return ['p' => $primeN[0], 'q' => $primeN[1], 'n' => $n, 'phi(n)' => $phin, 'e' => $e, 'd' => $d];
}

show("Clé avec des premiers dans [100, 999] : ");
dump(genererClesRSA(100, 999));

title("Partie 2 - Un protocole d'authentiffication par RSA");
subtitle("Q.1");
question("A.");
show(<<<HTML
La clé privée (11, 23) est admissible si et seulement si 23 et 11 sont premier entre eux, c'est-à-dire que le PGCD(23, 11) = 1 :
23 = 11 * 2 + 1, donc PGCD(23, 11) = 1.
Donc la clé (11, 23) est admissible. p = 11 et q = 23
HTML);

question("B.");
show(<<<HTML
n_B = pq = 11 * 23 = 253.
φ(n_B) = φ(pq) = (p - 1)(q - 1) = 10 * 22 = 220
HTML);

question("C.");
show(<<<HTML
Pour que la clé (253, 17) soit admissible (donc n_B = 253 et e_B = 17), il faut que e_B soit premier avec φ(n_B) (pour qu'il soit inversible).
Et e et φ(n_B) sont premier entre eux si et seulemenet si PGCD(φ(n_B), e_B) = 1 avec φ(n_B) = 220 et e_B = 17 :
220 = 17 * 12 + 16
17 = 16 + 1 Donc PGCD(220, 17) = 1.
Donc la clé (253, 17) est admissible.
HTML);

question("D.");
show(<<<HTML
d_B = (e_B)^(-1) [φ(n_B)], Il faut donc trouver l'inverse de e_B modulo φ(n) :
1 = 17 - 16
1 = 17 - (220 - 17 * 12)
1 = 17 * 13 + 220 * (-1)
Donc d_B = (e_B)^(-1) mod φ(n_B) = 13, en effet 17 * 33 ≡ 1 [220].
HTML);

question("E.");
show(<<<HTML
13 = 8 + 4 + 1
100^1 ≡ 100 [253]
100^2 ≡ 10000 [253] ≡ 133 [253]
100^4 ≡ 133^2 [253] ≡ 17689 ≡ 232 [253]
100^8 ≡ 232^2 [253] ≡ 53824 ≡ 188 [253]
100^13 ≡ 100^8 * 100^4 * 100 [253]
100^13 ≡ 188 * 232 * 100 [253]
100^13 ≡ 100 * 100 [253]
100^13 ≡ 100^2 [253]
100^13 ≡ 133 [253]
s = 133
HTML);

question("F.");
show(<<<HTML
Alice peut vérifier, à partir de la seule valeur s, que son interlocuteur est bien Bob en appliquant la clé publique de Bob à s (soit r = s^(e_B) mod n_B) afin de retrouver r et vérifier que r = 100.

De plus, si Bob avait chiffré sa réponse avec la clé publique (n_A, e_A) d'Alice, elle devrait d'abord déchiffrer le message y à l'aide de sa clé privée en réalisant l'opération
s = y^(d_A) mod n_A pour obtenir s.
Ensuite, elle devrait vérifier la signature sur s pour retrouver r en utilisant la clé publique de Bob, grâce au calcule r = s^(e_B) mod n_B
et enfin vérifier que r = 100.
HTML);

question("G.");
show(<<<HTML
17 = 16 + 1
133^2 ≡ 17689 [253] ≡ 232 [253] ≡ -21 [253]
133^4 ≡ (-21)² [253] ≡ 441 [253] ≡ -65 [253]
133^8 ≡ (-65)² [253] ≡ 4225 [253] ≡ -76 [253]
133^16 ≡ (-76)² [253] ≡ 5776 [253] ≡ -43 [253]
133^17 ≡ 133^16 * 133 [253]
133^17 ≡ -43 * 133 [253]
133^17 ≡ −5719 [253]
133^17 ≡ 100 [253]

On peut en conclure que r = 100. Donc le message proviens bien de Bob.
HTML);

question("H.");
show(<<<HTML
17 = 16 + 1
200^2 ≡ 40000 [253] ≡ 26 [253]
200^4 ≡ 26² [253] ≡ 676 [253] ≡ 170 [253] ≡ -83 [253]
200^8 ≡ (-83)² [253] ≡ 6889 [253] ≡ 58 [253]
200^16 ≡ 58² [253] ≡ 3364 [253] ≡ 75 [253]
200^17 ≡ 200^16 * 200 [253]
200^17 ≡ 75 * 200 [253]
200^17 ≡ 15000 [253]
200^17 ≡ 73 [253]

On peut en conclure que r = 73 et 73 =/ 100, donc Mallory n'as pas réussi à se faire passé pour Bob.
HTML);

question("I.");
tableau(
    ["Grandeur", "Valeur théorique", "Appel PHP", "Résultat PHP"],
    [
        ["n_B, φ(n_B)", "253 ; 220", "11*23 ; (11-1)*(23-1)", 11*23 . " ; " . (11-1)*(23-1)],
        ["d_B = e_B^(-1) mod φ(n_B)", "13", "inversemod(17, 220)", inversemod(17, 220)],
        ["signature s = 100^(d_B) mod n_B", "133", "expmod(100, 13, 253) ou RSAdecrypt(100, 17, 11, 23)", RSAdecrypt(100, 17, 11, 23)],
        ["vérification s^(e_B) mod n_B", "100", "expmod(133, 17, 253) ou RSAencrypt(133, 17, 253)", RSAencrypt(133, 17, 253)],
        ["imposteur 200^(e_B) mod n_B", "73", "expmod(200, 17, 253)", expmod(200, 17, 253)],
    ]
);
/**============================================
 *               METHODES AFFICHAGES
 *=============================================**/

function debut(): void {
    echo <<<'HTML'
    <!DOCTYPE html>
    <html lang="fr">
    <head>
    <meta charset="UTF-8">
    <title>TP RSA</title>
    <style>
    body { font-family: system-ui, sans-serif; max-width: 900px; margin: 2rem auto; padding: 0 1rem; line-height: 1.6; color: #d4d7de; background: #14161c; }
    h1 { font-size: 1.5rem; color: #7aa2f7; border-bottom: 2px solid #2a2f3d; padding-bottom: .3rem; margin-top: 3rem; }
    h2 { font-size: 1.2rem; color: #a9b1d6; margin: 1.5rem 0 .5rem; }
    h3 { font-size: .95rem; color: #14161c; background: #7aa2f7; display: inline-block; padding: .1rem .7rem; border-radius: 4px; margin: 1.2rem 0 .4rem; }
    .res { background: #1c1f2a; border-left: 3px solid #3b4261; padding: .4rem .8rem; margin: .3rem 0; border-radius: 0 4px 4px 0; }
    .res b { color: #ff9e64; font-family: monospace; font-size: .95rem; }
    pre { background: #1c1f2a; color: #c0caf5; padding: .8rem; border-radius: 4px; overflow-x: auto; border: 1px solid #2a2f3d; }

    table.verif { width: 100%; border-collapse: collapse; margin: .8rem 0; background: #1c1f2a; }
    table.verif th { text-align: left; color: #a9b1d6; background: #20243a; padding: .5rem .8rem; border: 1px solid #2a2f3d; }
    table.verif td { padding: .45rem .8rem; border: 1px solid #2a2f3d; }
    table.verif td code { color: #ff9e64; font-family: monospace; }
    </style>
    </head>
    <body>
    HTML;
}

function show(string $before, mixed $val = ""): void {
    $val = ($val === "") ? "" : "<b>" . $val . "</b>";
    echo "<div class='res'>" . nl2br($before) . $val . "</div>";
}

function title(string $title): void {
    echo "<h1>" . $title . "</h1>";
}

function subtitle(string $title): void {
    echo "<h2>" . $title . "</h2>";
}

function question(string $title): void {
    echo "<h3>" . $title . "</h3>";
}

function dump(mixed $val): void {
    echo "<pre>";
    var_dump($val);
    echo "</pre>";
}

// Chaque ligne : [grandeur, valeur théorique, appel PHP]
// $entetes : noms des colonnes, $lignes : tableau de lignes (chaque ligne = tableau de cellules)
function tableau(array $entetes, array $lignes): void {
    echo "<table class='verif'><tr>";
    foreach ($entetes as $entete) {
        echo "<th>" . htmlspecialchars($entete) . "</th>";
    }
    echo "</tr>";

    foreach ($lignes as $ligne) {
        echo "<tr>";
        foreach ($ligne as $cellule) {
            echo "<td>" . htmlspecialchars((string) $cellule) . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}