<?php
function fetchSupabaseData($table, $queryParams) {
    $supabaseUrl = 'https://opytqyxheeezvwncboly.supabase.co';
    $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDc2NDAyMTMsImV4cCI6MjA2MzIxNjIxM30.h_DdvClVy4-xbEkQ3AWQose3dqPaxPQ1gl-LaLhwtCE';

    $url = "{$supabaseUrl}/rest/v1/{$table}?{$queryParams}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $supabaseKey, 'Authorization: Bearer ' . $supabaseKey, 'Prefer: return=representation']);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$jmeno = $_GET['jmeno'] ?? '';
if (empty($jmeno)) { die("Nebylo zadáno jméno hráče."); }

$hrac = null;
$typ_hrace = '';

$queryParams = 'select=*&jmeno=eq.' . urlencode($jmeno);
$data_hrac = fetchSupabaseData('players', $queryParams);

if (!empty($data_hrac)) {
    $hrac = $data_hrac[0];
    $typ_hrace = 'player';
} else {
    $data_brankar = fetchSupabaseData('goalies', $queryParams);
    if (!empty($data_brankar)) {
        $hrac = $data_brankar[0];
        $typ_hrace = 'goalie';
    }
}

if (!$hrac) { die("Hráč nebo brankář s tímto jménem nebyl nalezen."); }

$vek = 'N/A';
$datum_narozeni_formatovane = 'N/A';
if (!empty($hrac['datum_narozeni'])) {
    try {
        $datum_narozeni_obj = new DateTime($hrac['datum_narozeni']);
        $dnes = new DateTime();
        $rozdil = $dnes->diff($datum_narozeni_obj);
        $vek = $rozdil->y;
        $datum_narozeni_formatovane = $datum_narozeni_obj->format('d.m.Y'); 
    } catch (Exception $e) {
        $vek = 'N/A';
        $datum_narozeni_formatovane = 'N/A';
    }
}

$pozice_cele_jmeno = 'Neznámý';
if ($typ_hrace === 'goalie') {
    $pozice_cele_jmeno = 'Brankář';
} elseif (isset($hrac['post'])) {
    if ($hrac['post'] === 'U') {
        $pozice_cele_jmeno = 'Útočník';
    } elseif ($hrac['post'] === 'O') {
        $pozice_cele_jmeno = 'Obránce';
    }
}

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($hrac['jmeno']) ?> - Profil hráče</title>
    <link rel="icon" type="image/x-icon" href="chlumeclogo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f4f4; }
        .player-profile { max-width: 600px; margin: 40px auto; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; }
        .player-img { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; margin-bottom: 15px; border: 3px solid #eee; }
        .stat-table { margin-top: 20px; text-align: left; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <div class="player-profile">
        <img src="<?= htmlspecialchars($hrac['fotka_url'] ?? 'images/default_photo.jpg') ?>" alt="<?= htmlspecialchars($hrac['jmeno']) ?>" class="player-img">
        <h2><?= htmlspecialchars($hrac['jmeno']) ?></h2>
        <p><strong>Pozice:</strong> <?= htmlspecialchars($pozice_cele_jmeno) ?></p>
        <p><strong>Číslo dresu:</strong> <?= htmlspecialchars($hrac['cislo_dresu'] ?? 'N/A') ?></p>
        <p><strong>Věk:</strong> <?= htmlspecialchars($vek) ?></p>
        <p><strong>Datum narození:</strong> <?= htmlspecialchars($datum_narozeni_formatovane) ?></p>

        <h4 class="mt-4">Statistiky sezóny 2024/25</h4>
        <table class="table table-striped stat-table">
            <tbody>
            <?php if ($typ_hrace === 'player'): ?>
                <tr><th>Odehrané zápasy</th><td><?= htmlspecialchars($hrac['zapasy'] ?? 0) ?></td></tr>
                <tr><th>Góly</th><td><?= htmlspecialchars($hrac['goly'] ?? 0) ?></td></tr>
                <tr><th>Asistence</th><td><?= htmlspecialchars($hrac['asistence'] ?? 0) ?></td></tr>
                <tr><th>Body</th><td><?= htmlspecialchars($hrac['body'] ?? 0) ?></td></tr>
                <tr><th>Trestné minuty</th><td><?= htmlspecialchars($hrac['trestne_minuty'] ?? 0) ?></td></tr>
            <?php elseif($typ_hrace === 'goalie'): ?>
                <tr><th>Odehrané zápasy</th><td><?= htmlspecialchars($hrac['zapasy'] ?? 0) ?></td></tr>
                <tr><th>Odchytané minuty</th><td><?= htmlspecialchars($hrac['odchytane_minuty'] ?? 0) ?></td></tr>
                <tr><th>Úspěšnost zákroků</th><td><?= htmlspecialchars(number_format($hrac['uspesnost'] ?? 0, 2)) ?> %</td></tr>
                <tr><th>Trestné minuty</th><td><?= htmlspecialchars($hrac['trestne_minuty'] ?? 0) ?></td></tr>
            <?php else: ?>
                <tr><td colspan="2" class="text-center">Statistiky pro tento typ hráče nejsou dostupné.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <a href="soupisky.php" class="btn btn-dark mt-3">Zpět na soupisku</a>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>