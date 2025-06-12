<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$supabaseUrl = 'https://opytqyxheeezvwncboly.supabase.co';
$supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0NzY0MDIxMywiZXhwIjoyMDYzMjE2MjEzfQ.j5P0CgFejLb99zkwP-4SdUZ6IC-z8HvCY9D0JL0ovWQ';

$headers = [
    "apikey: $supabaseKey",
    "Authorization: Bearer $supabaseKey",
    "Content-Type: application/json"
];

$zprava = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nadpis = $_POST['nadpis'] ?? '';
    $obsah = $_POST['obsah'] ?? '';
    $obrazek_url = $_POST['obrazek_url'] ?? '';
$autor = $_SESSION['user_id'] ?? null;
    if (!empty($nadpis) && !empty($obsah)) {
        $data = json_encode([
            'nadpis' => $nadpis,
            'obsah' => $obsah,
            'obrazek_url' => $obrazek_url,
            'autor' => $autor,
            'datum' => date('c') 
        ]);

        $ch = curl_init("$supabaseUrl/rest/v1/aktuality");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    header("Location: aktuality.php");
    exit;
} else {
    $zprava = "Chyba při ukládání aktuality. HTTP kód: $httpCode<br>";
    $zprava .= "Odpověď Supabase: <pre>" . htmlspecialchars($response) . "</pre><br>";
    if ($curlError) {
        $zprava .= "CURL chyba: " . htmlspecialchars($curlError);
    }
}

    } else {
        $zprava = "Vyplňte prosím nadpis a obsah.";
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Přidat aktualitu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Přidat novou aktualitu</h2>

    <?php if ($zprava): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($zprava) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="nadpis" class="form-label">Nadpis</label>
            <input type="text" class="form-control" id="nadpis" name="nadpis" required>
        </div>
        <div class="mb-3">
            <label for="obsah" class="form-label">Obsah</label>
            <textarea class="form-control" id="obsah" name="obsah" rows="6" required></textarea>
        </div>
        <div class="mb-3">
            <label for="obrazek_url" class="form-label">URL obrázku (volitelné)</label>
            <input type="url" class="form-control" id="obrazek_url" name="obrazek_url">
        </div>
        <button type="submit" class="btn btn-primary">Přidat</button>
        <a href="aktuality.php" class="btn btn-secondary">Zpět</a>
    </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
