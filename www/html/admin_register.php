<?php
session_start();


if (!$_SESSION['user_id'] || !isAdmin($_SESSION['user_id'])) {
    die('Přístup zakázán');
}
function isAdmin($user_id) {
    
    
    $admin_ids = ['xxx-uuid-admina', 'yyy']; 
    return in_array($user_id, $admin_ids);
}



$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $jmeno = $_POST['jmeno'];

    $supabaseUrl = "https://opytqyxheeezvwncboly.supabase.co";
    $apiKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDc2NDAyMTMsImV4cCI6MjA2MzIxNjIxM30.h_DdvClVy4-xbEkQ3AWQose3dqPaxPQ1gl-LaLhwtCE"; 


    $inviteData = [
        "email" => $email
    ];

    $ch = curl_init("$supabaseUrl/auth/v1/admin/invite");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($inviteData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);

    $response = curl_exec($ch);
    $inviteResult = json_decode($response, true);
    curl_close($ch);

    if (isset($inviteResult["user"]["id"])) {
        $user_id = $inviteResult["user"]["id"];


        $profileData = [
            "id" => $user_id,
            "jmeno" => $jmeno,
            "vytvoreno" => date('c')
        ];

        $ch2 = curl_init("$supabaseUrl/rest/v1/profiles");
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($profileData));
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            "apikey: $apiKey",
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);
        curl_setopt($ch2, CURLOPT_POST, true);

        $response2 = curl_exec($ch2);
        curl_close($ch2);

        $success = "Uživatel byl pozván a záznam přidán do databáze.";
    } else {
        $error = "Chyba při registraci: " . ($inviteResult["msg"] ?? "Neznámá chyba.");
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Přidat uživatele (admin)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Přidání uživatele (admin)</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="p-4 border bg-white rounded shadow-sm">
        <div class="mb-3">
            <label for="email" class="form-label">E-mail uživatele</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="jmeno" class="form-label">Jméno</label>
            <input type="text" name="jmeno" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger">Pozvat uživatele</button>
    </form>
</div>
</body>
</html>
