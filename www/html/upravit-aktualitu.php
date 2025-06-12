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
$aktualita = null;

if (isset($_GET['id'])) {
    $id_k_uprave = $_GET['id'];

    $ch = curl_init("$supabaseUrl/rest/v1/aktuality?id=eq.$id_k_uprave");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $data = json_decode($response, true);
        if (!empty($data)) {
            $aktualita = $data[0];
        } else {
            $zprava = "Aktualita s daným ID nebyla nalezena.";
        }
    } else {
        $zprava = "Chyba při načítání aktuality pro úpravu. HTTP kód: $httpCode";
        if ($response) {
            $zprava .= "<br>Odpověď Supabase: <pre>" . htmlspecialchars($response) . "</pre>";
        }
        if ($curlError) {
            $zprava .= "<br>CURL chyba: " . htmlspecialchars($curlError);
        }
    }
} else {
    header("Location: aktuality.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_upravovane = $_POST['id'];
    $nadpis = $_POST['nadpis'] ?? '';
    $obsah = $_POST['obsah'] ?? '';
    $obrazek_url = $_POST['obrazek_url'] ?? '';

    if (!empty($nadpis) && !empty($obsah)) {
        $data_pro_update = json_encode([
            'nadpis' => $nadpis,
            'obsah' => $obsah,
            'obrazek_url' => $obrazek_url,
            'datum' => date('c') 
        ]);

        $ch = curl_init("$supabaseUrl/rest/v1/aktuality?id=eq.$id_upravovane");
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $data_pro_update,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            header("Location: aktuality.php?zprava=Aktualita byla úspěšně upravena.");
            exit;
        } else {
            $zprava = "Chyba při ukládání upravené aktuality. HTTP kód: $httpCode";
            if ($response) {
                $zprava .= "<br>Odpověď Supabase: <pre>" . htmlspecialchars($response) . "</pre>";
            }
            if ($curlError) {
                $zprava .= "<br>CURL chyba: " . htmlspecialchars($curlError);
            }
        }
    } else {
        $zprava = "Vyplňte prosím nadpis a obsah.";
    }
}

if (!$aktualita && empty($zprava)) {
    $zprava = "Chyba: Aktualita nebyla načtena.";
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Upravit aktualitu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Upravit aktualitu</h2>

    <?php if ($zprava): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($zprava) ?></div>
    <?php endif; ?>

    <?php if ($aktualita): ?>
        <form method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($aktualita['id']) ?>">
            <div class="mb-3">
                <label for="nadpis" class="form-label">Nadpis</label>
                <input type="text" class="form-control" id="nadpis" name="nadpis" value="<?= htmlspecialchars($aktualita['nadpis']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="obsah" class="form-label">Obsah</label>
                <textarea class="form-control" id="obsah" name="obsah" rows="6" required><?= htmlspecialchars($aktualita['obsah']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="obrazek_url" class="form-label">URL obrázku (volitelné)</label>
                <input type="url" class="form-control" id="obrazek_url" name="obrazek_url" value="<?= htmlspecialchars($aktualita['obrazek_url']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Uložit změny</button>
            <a href="aktuality.php" class="btn btn-secondary">Zpět</a>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>