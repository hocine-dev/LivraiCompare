<?php
ini_set('memory_limit', '512M'); // Increase memory limit to 512 MB
// Liste des communes de la wilaya d'Alger (code wilaya 16)
$communes = [
    ['code' => 1601, 'name' => 'Alger Centre'],
    ['code' => 1602, 'name' => 'Sidi M\'Hamed'],
    ['code' => 1603, 'name' => 'El Madania'],
    ['code' => 1604, 'name' => 'Belouizdad'],
    ['code' => 1605, 'name' => 'Bab El Oued'],
    ['code' => 1606, 'name' => 'Bologhine'],
    ['code' => 1607, 'name' => 'Casbah'],
    ['code' => 1608, 'name' => 'Oued Koriche'],
    ['code' => 1609, 'name' => 'Bir Mourad Raïs'],
    ['code' => 1610, 'name' => 'El Biar'],
    ['code' => 1611, 'name' => 'Bouzareah'],
    ['code' => 1612, 'name' => 'Birkhadem'],
    ['code' => 1613, 'name' => 'El Harrach'],
    ['code' => 1614, 'name' => 'Baraki'],
    ['code' => 1615, 'name' => 'Oued Smar'],
    ['code' => 1616, 'name' => 'Bachdjerrah'],
    ['code' => 1617, 'name' => 'Hussein Dey'],
    ['code' => 1618, 'name' => 'Kouba'],
    ['code' => 1619, 'name' => 'Bourouba'],
    ['code' => 1620, 'name' => 'Dar El Beïda'],
    ['code' => 1621, 'name' => 'Bab Ezzouar'],
    ['code' => 1622, 'name' => 'Ben Aknoun'],
    ['code' => 1623, 'name' => 'Dely Ibrahim'],
    ['code' => 1624, 'name' => 'El Hammamet'],
    ['code' => 1625, 'name' => 'Raïs Hamidou'],
    ['code' => 1626, 'name' => 'Djasr Kasentina'],
    ['code' => 1627, 'name' => 'El Mouradia'],
    ['code' => 1628, 'name' => 'Hydra'],
    ['code' => 1629, 'name' => 'Mohammadia'],
    ['code' => 1630, 'name' => 'Bordj El Kiffan'],
    ['code' => 1631, 'name' => 'El Magharia'],
    ['code' => 1632, 'name' => 'Beni Messous'],
    ['code' => 1633, 'name' => 'Les Eucalyptus'],
    ['code' => 1634, 'name' => 'Birtouta'],
    ['code' => 1635, 'name' => 'Tessala El Merdja'],
    ['code' => 1636, 'name' => 'Ouled Chebel'],
    ['code' => 1637, 'name' => 'Sidi Moussa'],
    ['code' => 1638, 'name' => 'Aïn Taya'],
    ['code' => 1639, 'name' => 'Bordj El Bahri'],
    ['code' => 1640, 'name' => 'El Marsa'],
    ['code' => 1641, 'name' => 'H\'raoua'],
    ['code' => 1642, 'name' => 'Rouïba'],
    ['code' => 1643, 'name' => 'Reghaïa'],
    ['code' => 1644, 'name' => 'Aïn Benian'],
    ['code' => 1645, 'name' => 'Staoueli'],
    ['code' => 1646, 'name' => 'Zeralda'],
    ['code' => 1647, 'name' => 'Mahelma'],
    ['code' => 1648, 'name' => 'Rahmania'],
    ['code' => 1649, 'name' => 'Souidania'],
    ['code' => 1650, 'name' => 'Cheraga'],
    ['code' => 1651, 'name' => 'Ouled Fayet'],
    ['code' => 1652, 'name' => 'El Achour'],
    ['code' => 1653, 'name' => 'Draria'],
    ['code' => 1654, 'name' => 'Douera'],
    ['code' => 1655, 'name' => 'Baba Hassen'],
    ['code' => 1656, 'name' => 'Khraicia'],
    ['code' => 1657, 'name' => 'Saoula'],
];

$modes = ['domicile', 'bureau'];
$urgences = ['standard', 'two-day', 'same-day', 'express'];
$poids = [
    ['min' => 0, 'max' => 5],
    ['min' => 5, 'max' => 10],
    ['min' => 10, 'max' => null],
];
$prixColis = [
    ['min' => 0, 'max' => 10000],
    ['min' => 10000, 'max' => 30000],
    ['min' => 30000, 'max' => null],
];
$societes = [
    ['id' => 1, 'name' => 'Yalidine Express'],
    ['id' => 2, 'name' => 'NOEST Express'],
    ['id' => 3, 'name' => 'ZR Express'],
];
$tarifs = [
    1 => [300, 1900],
    2 => [500, 2000],
    3 => [400, 1500],
];

// Fonction pour extraire la wilaya d’un code commune
function getWilayaId($code) {
    return (int) substr(strval($code), 0, 2);
}

function getDelai($urgence) {
    switch ($urgence) {
        case 'same-day': return 8;
        case 'express': return 12;
        case 'two-day': return 48;
        default: return 72;
    }
}

$file = fopen('tarifs.sql', 'w');
fwrite($file, "INSERT INTO tarif (origine_wilaya_id, origine_commune_id, destination_wilaya_id, destination_commune_id, societe_id, mode, urgence, poids_min, poids_max, tarif, prix_colis_min, prix_colis_max, delai_heures) VALUES\n");

$batchSize = 1000;
$batch = [];

foreach ($communes as $origine) {
    foreach ($communes as $destination) {
        if ($origine['code'] == $destination['code']) {
            continue; // Skip if origin and destination are the same
        }

        $origine_wilaya_id = (int) substr($origine['code'], 0, 2);
        $destination_wilaya_id = (int) substr($destination['code'], 0, 2);

        foreach ($modes as $mode) {
            foreach ($urgences as $urgence) {
                foreach ($poids as $intervalPoids) {
                    foreach ($prixColis as $intervalPrix) {
                        foreach ($societes as $societe) {
                            $tarif = rand($tarifs[$societe['id']][0], $tarifs[$societe['id']][1]);
                            $delai_heures = rand(6, 72); // Exemple de délai aléatoire

                            $batch[] = sprintf(
                                "(%d, %d, %d, %d, %d, '%s', '%s', %.2f, %s, %.2f, %.2f, %s, %d)",
                                $origine_wilaya_id,
                                $origine['code'],
                                $destination_wilaya_id,
                                $destination['code'],
                                $societe['id'],
                                $mode,
                                $urgence,
                                $intervalPoids['min'],
                                $intervalPoids['max'] ?? 'NULL',
                                $tarif,
                                $intervalPrix['min'],
                                $intervalPrix['max'] ?? 'NULL',
                                $delai_heures
                            );

                            if (count($batch) >= $batchSize) {
                                fwrite($file, implode(",\n", $batch) . ",\n");
                                $batch = [];
                            }
                        }
                    }
                }
            }
        }
    }
}

if (!empty($batch)) {
    fwrite($file, implode(",\n", $batch) . ";\n");
} else {
    fseek($file, -2, SEEK_END);
    fwrite($file, ";\n");
}

fclose($file);
echo "SQL script generated and saved to tarifs.sql\n";