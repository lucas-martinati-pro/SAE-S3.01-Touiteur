<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1.1 : Exponentiation rapide
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

echo expmod(3, 12, 7) . " = 1 <br>";