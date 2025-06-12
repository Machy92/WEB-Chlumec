<?php
session_start();
function fetchSupabaseData($table, $queryParams = 'select=*') {
    $supabaseUrl = 'https://opytqyxheeezvwncboly.supabase.co';
    $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDc2NDAyMTMsImV4cCI6MjA2MzIxNjIxM30.h_DdvClVy4-xbEkQ3AWQose3dqPaxPQ1gl-LaLhwtCE';

    $url = "{$supabaseUrl}/rest/v1/{$table}?{$queryParams}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $supabaseKey, 'Authorization: Bearer ' . $supabaseKey]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$players = fetchSupabaseData('players', 'select=jmeno,post,cislo_dresu,datum_narozeni,fotka_url');
$goalies = fetchSupabaseData('goalies', 'select=jmeno,cislo_dresu,datum_narozeni,fotka_url');

$roster = array_merge($players ?? [], $goalies ?? []);

$pozice_map = ["Brankář" => "Brankáři", "Obránce" => "Obránci", "Útočník" => "Útočníci"];
$rozdelene = [];
foreach ($roster as $hrac) {
    $pozice_db = $hrac['post'] ?? 'Brankář';
    $pozice_klic = '';
    if ($pozice_db === 'U') $pozice_klic = 'Útočník';
    elseif ($pozice_db === 'O') $pozice_klic = 'Obránce';
    elseif ($pozice_db === 'B' || $pozice_db === 'Brankář') $pozice_klic = 'Brankář';
    
    if (!empty($pozice_klic)) {
        $rozdelene[$pozice_klic][] = $hrac;
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soupiska - Warriors Chlumec</title>
    <link rel="icon" type="image/x-icon" href="chlumeclogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .player-card { border: 1px solid #ddd; border-radius: 10px; padding: 20px; text-align: center; background-color: white; position: relative; transition: 0.3s; height: 100%; }
        .player-card:hover { box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1); }
        .player-card img { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; margin-bottom: 10px; background-color: #eee; }
        .player-name { font-size: 18px; font-weight: bold; }
        .player-info { font-size: 14px; color: #666; }
        .stretched-link { position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1; }
    </style>
</head>
<body>
<nav><?php include 'header.php'; ?></nav>

<div class="container py-5">
    <h1 class="text-center mb-5">Soupiska týmu Warriors Chlumec</h1>

    <?php if (!empty($rozdelene)): ?>
        <?php foreach ($pozice_map as $pozice_klic => $pozice_nazev): ?>
            <?php if (!isset($rozdelene[$pozice_klic]) || empty($rozdelene[$pozice_klic])) continue; ?>
            
            <h2 class="h4 border-bottom pb-2 mb-4"><?= $pozice_nazev ?></h2>
            <div class="row g-4 mb-5">
                <?php foreach ($rozdelene[$pozice_klic] as $hrac): ?>
                    <?php
                    $vek = 'N/A';
                    if (!empty($hrac['datum_narozeni'])) {
                        $datum_narozeni = new DateTime($hrac['datum_narozeni']);
                        $dnes = new DateTime();
                        $rozdil = $dnes->diff($datum_narozeni);
                        $vek = $rozdil->y;
                    }
                    ?>
                    <div class="col-6 col-md-4 col-lg-3 d-flex">
                        <div class="player-card w-100">
                            <img src="<?= htmlspecialchars($hrac['fotka_url'] ?? 'images/default_photo.jpg') ?>" alt="<?= htmlspecialchars($hrac['jmeno']) ?>">
                            <div class="player-name"><?= htmlspecialchars($hrac['jmeno']) ?></div>
                            <div class="player-info">
                                Číslo: <?= htmlspecialchars($hrac['cislo_dresu'] ?? 'N/A') ?><br>
                                Věk: <?= htmlspecialchars($vek) ?>
                            </div>
                            <a href="profil_hrace.php?jmeno=<?= urlencode($hrac['jmeno']) ?>" class="stretched-link"></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning text-center">Data soupisky se nepodařilo načíst, nebo je soupiska prázdná.</div>
    <?php endif; ?>
</div>

<footer class="mt-5"><?php include 'footer.php'; ?></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>