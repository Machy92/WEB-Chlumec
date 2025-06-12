<?php
session_start();


$supabaseUrl = 'https://opytqyxheeezvwncboly.supabase.co';
$supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDc2NDAyMTMsImV4cCI6MjA2MzIxNjIxM30.h_DdvClVy4-xbEkQ3AWQose3dqPaxPQ1gl-LaLhwtCE';
$headers = [
    "apikey: $supabaseKey",
    "Authorization: " . (isset($_SESSION['access_token']) ? "Bearer " . $_SESSION['access_token'] : "Bearer " . $supabaseKey),
    "Content-Type: application/json"
];



$chAktuality = curl_init("$supabaseUrl/rest/v1/aktuality?select=*&order=datum.desc");
curl_setopt_array($chAktuality, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers]);
$responseAktuality = curl_exec($chAktuality);
if ($responseAktuality === false) { die("Chyba při načítání aktualit: " . curl_error($chAktuality)); }
$data = json_decode($responseAktuality, true);
curl_close($chAktuality);


$chProfiles = curl_init("$supabaseUrl/rest/v1/profiles?select=user_id,jmeno");
curl_setopt_array($chProfiles, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers]);
$responseProfiles = curl_exec($chProfiles);
if ($responseProfiles === false) { die("Chyba při načítání profilů: " . curl_error($chProfiles)); }
$profiles = json_decode($responseProfiles, true);
curl_close($chProfiles);


$autoriMap = [];
if (is_array($profiles)) {
    foreach ($profiles as $profil) {
        $autoriMap[$profil['user_id']] = $profil['jmeno'];
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktuality</title>
    <link rel="icon" href="chlumeclogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    
    <style>

        html {
            height: 100%;
        }
        body {
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }

        .container.mt-5.mb-5 {
            flex-grow: 1;
        }

        
        .card-img-top {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }
        .card-text-truncate {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 4; 
            overflow: hidden;
        }
        .read-more-btn {
            display: none; 
        }
    </style>
</head>
<body>
    <nav>
        <?php include 'header.php'; ?>
    </nav>

<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4">Aktuality</h2>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="text-center mb-4">
            <a href="pridat-aktualitu.php" class="btn btn-secondary"><i class="fa-solid fa-plus"></i> Přidat aktualitu</a>
        </div>
    <?php endif; ?>

    <?php if (empty($data)): ?>
        <p class="text-center">Zatím žádné aktuality.</p>
    <?php else: ?>
        <div class="row g-4" id="masonry-grid" data-masonry='{"percentPosition": true, "itemSelector": ".grid-item"}'>
            <?php foreach ($data as $clanek): ?>
                <?php
                    $autor_id = $clanek['autor'] ?? null;
                    $autor_jmeno = 'Neznámý';
                    if ($autor_id && isset($autoriMap[$autor_id])) {
                        $autor_jmeno = $autoriMap[$autor_id];
                    }
                ?>
                <div class="col-sm-6 col-lg-4 mb-4 grid-item">
                    <div class="card shadow-sm">
                        <?php if (!empty($clanek['obrazek_url'])): ?>
                            <img src="<?= htmlspecialchars($clanek['obrazek_url']) ?>" class="card-img-top" alt="Obrázek článku">
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($clanek['nadpis']) ?></h5>
                            <p class="card-text card-text-truncate card-text-content">
                                <?= nl2br(htmlspecialchars($clanek['obsah'])) ?>
                            </p>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-auto read-more-btn" data-bs-toggle="modal" data-bs-target="#aktualitaModal-<?= $clanek['id'] ?>">
                                Číst více
                            </button>
                        </div>
                        <div class="card-footer text-muted small">
                            <div><i class="fa-regular fa-user"></i> <?= htmlspecialchars($autor_jmeno) ?></div>
                            <div><i class="fa-regular fa-clock"></i> <?= date('d.m.Y H:i', strtotime($clanek['datum'])) ?></div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="mt-2">
                                    <a href="upravit-aktualitu.php?id=<?= htmlspecialchars($clanek['id']) ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-pen-to-square"></i> Upravit</a>
                                    <a href="smazat-aktualitu.php?id=<?= htmlspecialchars($clanek['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Opravdu chcete smazat tuto aktualitu?')"><i class="fa-solid fa-trash-can"></i> Smazat</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="aktualitaModal-<?= $clanek['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title"><?= htmlspecialchars($clanek['nadpis']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                            <div class="modal-body">
                                <?php if (!empty($clanek['obrazek_url'])): ?>
                                    <img src="<?= htmlspecialchars($clanek['obrazek_url']) ?>" class="img-fluid rounded mb-3" alt="Obrázek článku">
                                <?php endif; ?>
                                <p><?= nl2br(htmlspecialchars($clanek['obsah'])) ?></p>
                                <hr>
                                <div class="text-muted small">
                                    <i class="fa-regular fa-user"></i> <?= htmlspecialchars($autor_jmeno) ?><br>
                                    <i class="fa-regular fa-clock"></i> <?= date('d.m.Y H:i', strtotime($clanek['datum'])) ?>
                                </div>
                            </div>
                            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zavřít</button></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer>
    <?php include 'footer.php'; ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>

<script>
    function checkReadMoreButtons() {
        const textContainers = document.querySelectorAll('.card-text-content');
        textContainers.forEach(container => {
            const button = container.parentElement.querySelector('.read-more-btn');
            if (button) {

                button.style.display = 'none';

                if (container.scrollHeight > container.clientHeight) {
                    button.style.display = 'inline-block';
                }
            }
        });
    }


    window.addEventListener('load', function() {
        checkReadMoreButtons();


        setTimeout(function() {
            checkReadMoreButtons();

            var gridElement = document.querySelector('#masonry-grid');
            if (gridElement && typeof Masonry !== 'undefined' && Masonry.data(gridElement)) {
                 Masonry.data(gridElement).layout();
            }
        }, 500);
    });

   
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            var gridElement = document.querySelector('#masonry-grid');
            if (gridElement && typeof Masonry !== 'undefined' && Masonry.data(gridElement)) {
                 Masonry.data(gridElement).layout();
            }
            checkReadMoreButtons();
        }, 250);
    });
</script>

</body>
</html>