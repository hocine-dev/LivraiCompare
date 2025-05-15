<?php
// Générateur de SQL optimisé pour la table `tarif`
$filePath = __DIR__ . '/insert_tarifs.sql';
$file = fopen($filePath, 'w');

// 1. Liste des wilayas (id => zone)
$wilayas = [
    1 => 'D', 2 => 'B', 3 => 'C', 4 => 'C', 5 => 'C', 6 => 'B',
    7 => 'C', 8 => 'D', 9 => 'A', 10 => 'B', 11 => 'D', 12 => 'D',
    13 => 'D', 14 => 'B', 15 => 'B', 16 => 'A', 17 => 'C', 18 => 'B',
    19 => 'B', 20 => 'C', 21 => 'C', 22 => 'C', 23 => 'C', 24 => 'C',
    25 => 'C', 26 => 'A', 27 => 'C', 28 => 'B', 29 => 'C', 30 => 'C',
    31 => 'C', 32 => 'C', 33 => 'D', 34 => 'B', 35 => 'A', 36 => 'C',
    37 => 'C', 38 => 'B', 39 => 'C', 40 => 'C', 41 => 'C', 42 => 'A',
    43 => 'C', 44 => 'A', 45 => 'C', 46 => 'B', 47 => 'C', 48 => 'B',
    49 => 'C', 50 => 'D', 51 => 'C', 52 => 'D', 53 => 'D', 54 => 'D',
    55 => 'D', 56 => 'D', 57 => 'D', 58 => 'D',
];

// 2. Sociétés et leurs barèmes tarifaires et délais (+2 jours déjà inclus)
$companies = [
    1 => [ // Yalidine
        'rates' => [
            'intra'    => 350,
            'same'     => 500,
            'adjacent' => 700,
            'far2'     => 900,
            'far3'     => 1100,
        ],
        'delays' => [
            'intra'    => 3,
            'same'     => 4,
            'adjacent' => 5,
            'far2'     => 7,
            'far3'     => 9,
        ],
    ],
    2 => [ // ZR
        'rates' => [
            'intra'    => 300,
            'same'     => 450,
            'adjacent' => 650,
            'far2'     => 850,
            'far3'     => 1050,
        ],
        'delays' => [
            'intra'    => 3,
            'same'     => 3,
            'adjacent' => 4,
            'far2'     => 4,
            'far3'     => 5,
        ],
    ],
    3 => [ // Norest
        'rates' => [
            'intra'    => 500,
            'same'     => 900,
            'adjacent' => 1300,
            'far2'     => 1800,
            'far3'     => 2300,
        ],
        'delays' => [
            'intra'    => 3,
            'same'     => 4,
            'adjacent' => 5,
            'far2'     => 6,
            'far3'     => 7,
        ],
    ],
];

// Fonction pour calculer la distance entre deux zones
function zoneDistance(string $z1, string $z2): int {
    $order = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4];
    return abs($order[$z1] - $order[$z2]);
}

// 3. Génération des insertions par lots
$batchSize = 500;
$rows = [];
$count = 0;

foreach ($wilayas as $origId => $origZone) {
    foreach ($wilayas as $destId => $destZone) {
        foreach ($companies as $socId => $cfg) {
            if ($origId === $destId) {
                $rate = $cfg['rates']['intra'];
                $delay = $cfg['delays']['intra'];
            } elseif ($origZone === $destZone) {
                $rate = $cfg['rates']['same'];
                $delay = $cfg['delays']['same'];
            } else {
                $dist = zoneDistance($origZone, $destZone);
                if ($dist === 1) {
                    $rate = $cfg['rates']['adjacent'];
                    $delay = $cfg['delays']['adjacent'];
                } elseif ($dist === 2) {
                    $rate = $cfg['rates']['far2'];
                    $delay = $cfg['delays']['far2'];
                } else {
                    $rate = $cfg['rates']['far3'];
                    $delay = $cfg['delays']['far3'];
                }
            }

            $rows[] = sprintf("(%d, %d, %d, %.2f, %d)", $origId, $destId, $socId, $rate, $delay);
            $count++;

            if ($count % $batchSize === 0) {
                $sql = "INSERT INTO tarif (origine_wilaya_id, destination_wilaya_id, societe_id, tarif, delai_livraison) VALUES\n";
                $sql .= implode(",\n", $rows) . ";\n";
                fwrite($file, $sql);
                $rows = [];
            }
        }
    }
}

// Écriture des lignes restantes
if (!empty($rows)) {
    $sql = "INSERT INTO tarif (origine_wilaya_id, destination_wilaya_id, societe_id, tarif, delai_livraison) VALUES\n";
    $sql .= implode(",\n", $rows) . ";\n";
    fwrite($file, $sql);
}

fclose($file);
echo "Fichier SQL optimisé généré : $filePath\n";
