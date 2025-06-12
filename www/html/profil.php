<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$supabaseUrl = 'https://opytqyxheeezvwncboly.supabase.co';
$supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDc2NDAyMTMsImV4cCI6MjA2MzIxNjIxM30.h_DdvClVy4-xbEkQ3AWQose3dqPaxPQ1gl-LaLhwtCE';
$userId = $_SESSION['user_id'];
$access_token = $_SESSION['access_token'] ?? $supabaseKey; 

$headers = [
    "apikey: $supabaseKey",
    "Authorization: Bearer $access_token", 
    "Content-Type: application/json"
];

$ch = curl_init("$supabaseUrl/rest/v1/user_profiles_with_email?user_id=eq.$userId&select=jmeno,pozice,vytvoreno,email");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$profile = null;
$error = null;

if ($http_code === 200) {
    $data = json_decode($response, true);
    $profile = $data[0] ?? null;
    if (!$profile) {
        $error = "Profil pro daného uživatele nebyl nalezen.";
    }
} else {
    $error = "Nepodařilo se načíst profil. Zkuste to prosím později.";
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil uživatele - Warriors Chlumec</title>
    <link rel="icon" href="chlumeclogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> 
    <style>
        html {
            box-sizing: border-box;
            height: 100%;
            margin: 0;
            padding: 0;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh; 
            display: flex;
            flex-direction: column;
        }
        main.page-content { 
            flex-grow: 1; 
        }
        .profile-container-wrapper { 
            width: 100%;
        }
        .profile-content-card {
            max-width: 700px; 
            margin-left: auto;
            margin-right: auto;
        }
        .list-group-item strong.d-block {
            margin-bottom: 0.2rem;
        }
        .btn-warning, .btn-outline-danger, .btn-success {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<main class="page-content container my-4 my-md-5">
    <div class="profile-container-wrapper"> 
        <div class="profile-content-card"> 
            <?php
            if (isset($_SESSION['flash_message'])) {
                $message = $_SESSION['flash_message'];
                $type = $_SESSION['flash_type'] ?? 'info';
                echo "<div class='alert alert-{$type} text-center shadow-sm'><i class='fa-solid fa-check-circle me-2'></i>{$message}</div>";
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_type']);
            }
            ?>
            <?php if (isset($_GET['heslo_zmeneno'])): ?>
                <?php 
                $alert_type = 'danger';
                $message = 'Při změně hesla nastala neznámá chyba.';
                if ($_GET['heslo_zmeneno'] === 'ok') {
                    $alert_type = 'success';
                    $message = '<i class="fa-solid fa-check-circle me-2"></i>Heslo bylo úspěšně změněno.';
                } elseif ($_GET['heslo_zmeneno'] === 'chyba') {
                    $message = '<i class="fa-solid fa-xmark-circle me-2"></i>Při změně hesla nastala chyba. Zkontrolujte zadané údaje.';
                } elseif ($_GET['heslo_zmeneno'] === 'neshoda') {
                    $message = '<i class="fa-solid fa-triangle-exclamation me-2"></i>Nové heslo a potvrzení hesla se neshodují.';
                } elseif ($_GET['heslo_zmeneno'] === 'stareSpatne') {
                    $message = '<i class="fa-solid fa-triangle-exclamation me-2"></i>Zadané staré heslo není správné.';
                } elseif ($_GET['heslo_zmeneno'] === 'slabe') {
                    $message = '<i class="fa-solid fa-triangle-exclamation me-2"></i>Nové heslo je příliš slabé. Musí mít alespoň 8 znaků, obsahovat velké i malé písmeno a číslici.';
                }
                ?>
                <div class="alert alert-<?= $alert_type ?> text-center shadow-sm">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center shadow-sm"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($profile): ?>
                <div class="card shadow-lg rounded-4 border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h2 class="card-title mb-1">
                                <?= htmlspecialchars($profile['jmeno']) ?>
                            </h2>
                            <p class="text-muted fs-5">
                                <i class="fa-solid fa-briefcase me-1"></i> <?= htmlspecialchars($profile['pozice'] ?? 'Pozice neuvedena') ?>
                            </p>
                        </div>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex align-items-start py-3">
                                <i class="fa-solid fa-envelope fa-lg text-muted me-3 mt-1" style="width: 24px;"></i>
                                <div>
                                    <strong class="d-block">E-mail</strong>
                                    <span><?= htmlspecialchars($profile['email'] ?? 'Neuveden') ?></span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-start py-3">
                                <i class="fa-solid fa-calendar-plus fa-lg text-muted me-3 mt-1" style="width: 24px;"></i>
                                <div>
                                    <strong class="d-block">Datum registrace</strong>
                                    <span><?= htmlspecialchars(date('d. m. Y H:i', strtotime($profile['vytvoreno']))) ?></span>
                                </div>
                            </li>
                        </ul>
                        <div class="collapse" id="changePasswordForm">
                            <div class="border p-3 p-md-4 rounded-3 bg-light-subtle mb-4 shadow-sm">
                                <h5 class="mb-3 fw-semibold text-center">Změna hesla</h5>
                                <form method="post" action="change_password.php">
                                    <div class="mb-3">
                                        <label for="old_password" class="form-label">Současné heslo</label>
                                        <input type="password" name="old_password" id="old_password" class="form-control form-control-lg" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Nové heslo (min. 8 znaků, velká, malá, číslice)</label>
                                        <input type="password" name="new_password" id="new_password" class="form-control form-control-lg" required minlength="8">
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Zopakujte nové heslo</label>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-lg" required minlength="8">
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 btn-lg">
                                        <i class="fa-solid fa-floppy-disk me-2"></i>Uložit změnu
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-warning btn-lg" type="button" data-bs-toggle="collapse" data-bs-target="#changePasswordForm" aria-expanded="false" aria-controls="changePasswordForm">
                                <i class="fa-solid fa-key me-2"></i>Změnit heslo
                            </button>
                            <a href="logout.php" class="btn btn-outline-danger btn-lg">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Odhlásit se
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                 <div class="alert alert-info text-center shadow-sm">Profil nebyl nalezen.</div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>