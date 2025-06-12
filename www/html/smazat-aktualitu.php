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

if (isset($_GET['id'])) {
    $id_k_smazani = $_GET['id'];

    $ch = curl_init("$supabaseUrl/rest/v1/aktuality?id=eq.$id_k_smazani");
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        header("Location: aktuality.php?zprava=Aktualita byla úspěšně smazána.");
        exit;
    } else {
        $error_message = "Chyba při mazání aktuality. HTTP kód: $httpCode";
        if ($response) {
            $error_message .= "<br>Odpověď Supabase: <pre>" . htmlspecialchars($response) . "</pre>";
        }
        if ($curlError) {
            $error_message .= "<br>CURL chyba: " . htmlspecialchars($curlError);
        }
        $_SESSION['error_message_mazani'] = $error_message;
        header("Location: aktuality.php?zprava_chyba=Při mazání došlo k chybě.");
        exit;
    }

} else {
    header("Location: aktuality.php");
    exit;
}
?>