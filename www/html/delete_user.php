<?php
session_start();


if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$supabaseUrl = 'https://opytqyxheeezvwncboly.supabase.co';

$supabaseServiceKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0NzY0MDIxMywiZXhwIjoyMDYzMjE2MjEzfQ.j5P0CgFejLb99zkwP-4SdUZ6IC-z8HvCY9D0JL0ovWQ'; 

$adminUserId = $_SESSION['user_id'];
$userToDeleteId = $_GET['id'];





if ($adminUserId === $userToDeleteId) {
    header("Location: sprava_uzivatelu.php?delete=error");
    exit;
}




$ch = curl_init("$supabaseUrl/auth/v1/admin/users/$userToDeleteId");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "DELETE",
    CURLOPT_HTTPHEADER => [
        "apikey: $supabaseServiceKey",
        "Authorization: Bearer $supabaseServiceKey"
    ]
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


if ($httpcode == 200) {
    header("Location: sprava_uzivatelu.php?delete=ok");
} else {
    header("Location: sprava_uzivatelu.php?delete=error");
}
exit;