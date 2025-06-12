<?php
session_start();

$supabaseUrl = 'https://opytqyxheeezvwncboly.supabase.co';
$supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0NzY0MDIxMywiZXhwIjoyMDYzMjE2MjEzfQ.j5P0CgFejLb99zkwP-4SdUZ6IC-z8HvCY9D0JL0ovWQ';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json"
    ];

    $data = json_encode([
        "email" => $email,
        "password" => $password
    ]);

    $ch = curl_init("$supabaseUrl/auth/v1/token?grant_type=password");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code === 200 && isset($result['access_token']) && isset($result['user']['id'])) {
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_email'] = $result['user']['email'];

        $userId = $result['user']['id'];
        $queryUrl = "$supabaseUrl/rest/v1/profiles?user_id=eq.$userId&select=pozice"; 

        $ch2 = curl_init($queryUrl);
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "apikey: $supabaseKey",
                "Authorization: Bearer {$result['access_token']}", 
                "Content-Type: application/json",
            ]
        ]);

        $response2 = curl_exec($ch2);
        $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);

        $profileData = json_decode($response2, true);
        
        if ($http_code2 === 200 && !empty($profileData) && isset($profileData[0]['pozice'])) {
            $_SESSION['pozice'] = $profileData[0]['pozice'];
        } else {
            $_SESSION['pozice'] = null;
        }

        $_SESSION['flash_message'] = "Byl jste úspěšně přihlášen.";
        $_SESSION['flash_type'] = "success";
        header("Location: profil.php");
        exit;
    } else {
        if (isset($result['error_description'])) {
            $error = htmlspecialchars($result['error_description']);
        } elseif(isset($result['error'])) {
            $error = htmlspecialchars($result['error']);
        } elseif ($http_code === 400) {
            $error = "Neplatné přihlašovací údaje. Zkontrolujte e-mail a heslo.";
        }
        else {
            $error = "Přihlášení se nezdařilo. Zkuste to prosím znovu.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení – Warriors Chlumec</title>
    <link rel="icon" type="image/x-icon" href="chlumeclogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .main-login-content {
            flex-grow: 1; 
            display: flex;
            align-items: center; 
            justify-content: center;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="main-login-content">
        <div class="container">
            <h2 class="text-center mb-4">Přihlášení uživatele</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center"><?= $error ?></div>
            <?php endif; ?>
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5 col-xl-4">
                    <div class="card shadow-lg">
                        <div class="card-body p-4 p-md-5">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">E-mail:</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label fw-bold">Heslo:</label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-danger w-100 btn-lg">Přihlásit se</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>