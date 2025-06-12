<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$supabaseUrl = 'https://opytqyxheeezvwncboly.supabase.co';
$supabaseServiceKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0NzY0MDIxMywiZXhwIjoyMDYzMjE2MjEzfQ.j5P0CgFejLb99zkwP-4SdUZ6IC-z8HvCY9D0JL0ovWQ'; 

$userId = $_SESSION['user_id'];

$headers = [
    "apikey: $supabaseServiceKey",
    "Authorization: Bearer $supabaseServiceKey"
];

$ch = curl_init("$supabaseUrl/rest/v1/profiles?user_id=eq.$userId&select=pozice");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers
]);
$response = curl_exec($ch);
curl_close($ch);

$adminData = json_decode($response, true);
if (empty($adminData) || $adminData[0]['pozice'] !== 'admin') {
    die("Přístup odepřen. Tato stránka je pouze pro administrátory.");
}


$inviteMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invite_user'])) {
    $email = $_POST['email'];
    $jmeno = $_POST['jmeno'];
    $pozice = $_POST['pozice'];

    $postData = json_encode([
        'email' => $email,
        'data' => [
            'jmeno' => $jmeno,
            'pozice' => $pozice,
            'role' => 'user' 
        ]
    ]);

    $ch_invite = curl_init("$supabaseUrl/auth/v1/invite");
    curl_setopt_array($ch_invite, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            "apikey: $supabaseServiceKey",
            "Authorization: Bearer $supabaseServiceKey",
            "Content-Type: application/json"
        ]
    ]);
    $inviteResponse = curl_exec($ch_invite);
    $httpcode = curl_getinfo($ch_invite, CURLINFO_HTTP_CODE);
    curl_close($ch_invite);

    if ($httpcode == 200) {
        $inviteMessage = '<div class="alert alert-success">Pozvánka pro ' . htmlspecialchars($email) . ' byla úspěšně odeslána.</div>';
    } else {
        $errorDetails = json_decode($inviteResponse, true);
        $inviteMessage = '<div class="alert alert-danger">Nepodařilo se odeslat pozvánku. Chyba: ' . htmlspecialchars($errorDetails['msg'] ?? 'Neznámá chyba') . '</div>';
    }
}


$ch_users = curl_init("$supabaseUrl/rest/v1/rpc/get_all_users_with_profiles");
curl_setopt_array($ch_users, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POST => true, 
]);
$usersResponse = curl_exec($ch_users);
curl_close($ch_users);
$users = json_decode($usersResponse, true);

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Správa uživatelů</title>
    <link rel="icon" href="chlumeclogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'header.php';?>

<div class="container mt-5 mb-5">
    <h1 class="mb-4">Správa uživatelů</h1>

    <div class="card shadow-sm mb-5">
        <div class="card-header">
            <h5 class="mb-0">Pozvat nového uživatele</h5>
        </div>
        <div class="card-body">
            <?php echo $inviteMessage;?>
            <form method="POST" action="sprava_uzivatelu.php">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jmeno" class="form-label">Jméno a příjmení</label>
                        <input type="text" class="form-control" id="jmeno" name="jmeno" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="pozice" class="form-label">Pozice</label>
                    <input type="text" class="form-control" id="pozice" name="pozice" required>
                </div>
                <button type="submit" name="invite_user" class="btn btn-secondary">
                    <i class="fa-solid fa-paper-plane"></i> Odeslat pozvánku
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Seznam uživatelů</h5>
        </div>
        <div class="card-body">
            <?php if (isset($_GET['delete']) && $_GET['delete'] == 'ok'): ?>
                <div class="alert alert-success">Uživatel byl úspěšně smazán.</div>
            <?php endif; ?>
            <?php if (isset($_GET['delete']) && $_GET['delete'] == 'error'): ?>
                <div class="alert alert-danger">Uživatele se nepodařilo smazat.</div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Jméno</th>
                            <th>Email</th>
                            <th>Pozice</th>
                            <th class="d-none d-lg-table-cell">Registrován</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="6" class="text-center">Nenalezeni žádní uživatelé.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['jmeno'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['pozice'] ?? 'N/A') ?></td>
                                    <td class="d-none d-lg-table-cell"><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['id'] !== $userId): ?>
                                        <a href="delete_user.php?id=<?= htmlspecialchars($user['id']) ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Opravdu chcete smazat tohoto uživatele? Tato akce je nevratná.');"
                                           aria-label="Smazat uživatele">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include 'footer.php';?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>