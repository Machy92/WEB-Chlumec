<?php
session_start();

require_once 'config.php'; 

$user_is_logged_in = isset($_SESSION['user_id']); 

$top_skaters_query_params = 'select=jmeno,body,fotka_url,cislo_dresu,datum_narozeni,post&order=body.desc&limit=3';
$top_skaters = fetchSupabaseData('players', $top_skaters_query_params);
$top_goalies_query_params = 'select=jmeno,uspesnost,fotka_url,cislo_dresu,datum_narozeni&order=uspesnost.desc&limit=3';
$top_goalies = fetchSupabaseData('goalies', $top_goalies_query_params);
$carousel_items_query_params = 'select=image_url,title,description&is_active=eq.true&order=item_order.asc';
$carousel_items = fetchSupabaseData('carousel_items', $carousel_items_query_params);
$most_penalized_query_params = 'select=jmeno,fotka_url,trestne_minuty&order=trestne_minuty.desc&limit=1';
$most_penalized_player_data = fetchSupabaseData('players', $most_penalized_query_params);
$most_penalized_player = !empty($most_penalized_player_data) ? $most_penalized_player_data[0] : null;

function parse_date_from_text_string_for_index($datum_text) {
    if (empty($datum_text)) return null;
    $datum_text_pro_parsovani = preg_replace('/^[A-ZŽŠČŘĎŤŇÚŮÝÁÉÍÓa-zžščřďťňúůýáéíó]{2,3}\s+/u', '', $datum_text);
    $formats_to_try = ['d.m.Y, H:i', 'd.m.Y', 'j.n.Y, H:i', 'j.n.Y'];
    foreach ($formats_to_try as $format) {
        $date_obj = DateTime::createFromFormat($format, $datum_text_pro_parsovani);
        if ($date_obj) return $date_obj;
    }
    return null;
}

$last_match = null;
$faze_order_for_index = ['Play-Off', 'Nadstavba - skupina A', 'Základní část']; 
$last_matches_params = 'select=*,domaci_logo_url,hostujici_logo_url&odehrano=eq.true';
$last_matches_data = fetchSupabaseData('zapasy', $last_matches_params);

if (!empty($last_matches_data)) {
    usort($last_matches_data, function($a, $b) use ($faze_order_for_index) {
        $faze_a = $a['faze_souteze'] ?? 'Neznámá fáze';
        $faze_b = $b['faze_souteze'] ?? 'Neznámá fáze';
        $pos_a = array_search($faze_a, $faze_order_for_index);
        $pos_b = array_search($faze_b, $faze_order_for_index);
        if ($pos_a === false) $pos_a = 999;
        if ($pos_b === false) $pos_b = 999;
        if ($pos_a !== $pos_b) return $pos_a <=> $pos_b;
        $date_a = parse_date_from_text_string_for_index($a['datum_cas_text']);
        $date_b = parse_date_from_text_string_for_index($b['datum_cas_text']);
        if (!$date_a || !$date_b) return 0;
        return $date_b <=> $date_a;
    });
    if (isset($last_matches_data[0])) { $last_match = $last_matches_data[0]; }
}

$future_match = null;
$future_matches_params = 'select=*,domaci_logo_url,hostujici_logo_url&odehrano=eq.false&order=datum_cas_text.asc';
$potential_future_matches = fetchSupabaseData('zapasy', $future_matches_params);
if (!empty($potential_future_matches)) {
    $dnes_pulnoc = new DateTime(); 
    $dnes_pulnoc->setTime(0, 0, 0); 
    foreach ($potential_future_matches as $zapas) {
        $datum_zapasu_obj = parse_date_from_text_string_for_index($zapas['datum_cas_text']);
        if ($datum_zapasu_obj) {
            $datum_zapasu_obj->setTime(0, 0, 0); 
            if ($datum_zapasu_obj >= $dnes_pulnoc) {
                $future_match = $zapas;
                break;
            }
        }
    }
}

function daysUntilBirthday($birthDate) {
    if (!$birthDate) return 366;
    try {
        $today = new DateTime();
        $birthDateObj = new DateTime($birthDate);
        $birthDateThisYear = new DateTime($today->format('Y') . '-' . $birthDateObj->format('m-d'));
        if ($birthDateThisYear < $today) { $birthDateThisYear->modify('+1 year'); }
        $interval = $today->diff($birthDateThisYear);
        return intval($interval->format('%a'));
    } catch (Exception $e) { return 366; }
}
$closestBirthdayPlayer = null;
$minDays = 366;
$players_for_birthday_check = [];
if (!empty($top_skaters)) { $players_for_birthday_check = array_merge($players_for_birthday_check, $top_skaters); }
if (!empty($top_goalies)) { $players_for_birthday_check = array_merge($players_for_birthday_check, $top_goalies); }

if (!empty($players_for_birthday_check)) {
    foreach ($players_for_birthday_check as $player) {
        if (!empty($player['datum_narozeni'])) {
            $days = daysUntilBirthday($player['datum_narozeni']);
            if ($days < $minDays) {
                $minDays = $days;
                $closestBirthdayPlayer = $player;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warriors Chlumec</title>
    <link rel="icon" type="image/x-icon" href="chlumeclogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        html { box-sizing: border-box; height: 100%; margin: 0; padding: 0; }
        *, *:before, *:after { box-sizing: inherit; }
        body { 
            margin: 0; padding: 0; 
            background-color: #f8f9fa; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
        }
        main { 
            flex-grow: 1;
        }
        .carousel-item img { height: 400px; object-fit: cover; transition: height 0.3s ease-in-out; }
        .carousel-caption { background-color: rgba(0, 0, 0, 0.5); padding: 5px 10px; border-radius: 5px; width: 90%; max-width: 600px; text-align: center; left: 50%; transform: translateX(-50%);}
        .section-title { font-size: 1.6rem; font-weight: 700; opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease-out, transform 0.6s ease-out; margin-bottom: 1.5rem;}
        .animate { opacity: 1 !important; transform: translateY(0) !important; }
        .match-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.07); margin-bottom: 1rem; height: 100%; display: flex; flex-direction: column; }
        .match-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .match-header h4 { font-size: 1.2rem; font-weight: 700; margin: 0; }
        .match-type { font-size: 0.8rem; font-weight: 700; padding: 4px 8px; border-radius: 5px; background-color: #e9ecef; color: #495057; }
        .match-body { display: flex; justify-content: space-between; align-items: center; text-align: center; flex-grow: 1; }
        .match-team { flex: 1; }
        .match-team img { height: 60px; width: 60px; object-fit: contain; margin-bottom: 10px; }
        .match-team h5 { font-size: 1rem; font-weight: 700; min-height: 40px; }
        .match-details { padding: 0 15px; }
        .match-score { font-size: 2.5rem; font-weight: 700; }
        .match-status { font-size: 0.8rem; color: #6c757d; }
        .match-date-time { font-size: 1.2rem; font-weight: 700; white-space: nowrap; }
        .match-footer { text-align: center; margin-top: 20px; }
        .players-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 1.8rem; margin-top: 1rem; }
        .players-grid .card { flex: 0 1 180px; max-width: 180px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.07); text-align: center; border: none; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .players-grid .card:hover { transform: translateY(-8px); box-shadow: 0 12px 24px rgba(0,0,0,0.12); }
        .players-grid .card-img-top { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin: 20px auto 15px; border: 4px solid #f0f0f0; }
        .players-grid .card-body { padding: 0 15px 20px; }
        .players-grid .card-title { font-size: 1rem; font-weight: 700; text-transform: uppercase; }
        .players-grid .card-text { font-size: 0.9rem; color: #666; }
        .sidebar-title { font-size: 0.9rem; font-weight: 700; color: #6c757d; margin-bottom: 0.75rem; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        .sidebar-card { background: #ffffff; border-radius: 12px; padding: 20px 15px; text-align: center; width: 100%; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #e9ecef; transition: box-shadow 0.2s ease-in-out, transform 0.2s ease-in-out; }
        .sidebar-card img { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 12px; }
        .sidebar-card h6 { font-size: 1rem; font-weight: 700; color: #1a2a4c; text-transform: uppercase; margin-bottom: 0.25rem; }
        .sidebar-card p { font-size: 0.8rem; margin-bottom: 0; color: #555; min-height: 36px; }
        a.sidebar-card-link { text-decoration: none; color: inherit; display: block; margin-bottom: 1rem; }
        a.sidebar-card-link:last-child { margin-bottom: 0; }
        a.sidebar-card-link .sidebar-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); cursor: pointer; }
        .manage-carousel-container { text-align: right; margin-bottom: 1rem; margin-top: 1rem; }
        .player-triangle-wrapper { margin: 0 auto; }
        .player-triangle-wrapper .players-grid { gap: 1rem; }
        .player-triangle-wrapper .players-grid .card { max-width: 160px; flex-basis: 160px; }
        .player-triangle-wrapper .players-grid .card-img-top { width: 90px; height: 90px; }
        .player-triangle-wrapper .players-grid .card-title { font-size: 0.9rem; }
        @media (max-width: 991.98px) { 
             .main-content-row { order: 2; } 
             .sidebar-row { order: 1; margin-bottom: 2rem; }
        }
        @media (max-width: 576px) {
            .section-title { font-size: 1.4rem; }
            .match-card { padding: 15px; }
            .match-score { font-size: 2rem; }
        }
        @media (min-width: 1800px) {
            .carousel-item img { height: 650px; }
            .carousel-control-prev-icon, .carousel-control-next-icon { width: 3rem; height: 3rem; }
            .carousel-caption h5 { font-size: 1.8rem; }
            .carousel-caption p { font-size: 1.1rem; }
            .players-grid .card { max-width: 220px; flex-basis: 220px; }
            .players-grid .card-img-top { width: 140px; height: 140px; }
            .players-grid .card-title { font-size: 1.15rem; }
            .players-grid .card-text { font-size: 1rem; }
            .match-card { padding: 30px; }
            .match-header h4 { font-size: 1.4rem; }
            .match-team img { height: 80px; width: 80px; }
            .match-team h5 { font-size: 1.2rem; }
            .match-score { font-size: 3.5rem; }
            .match-date-time { font-size: 1.4rem; }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const titles = document.querySelectorAll(".section-title");
            function checkScroll() {
                titles.forEach(title => {
                    const rect = title.getBoundingClientRect();
                    if (rect.top < window.innerHeight - 100) { title.classList.add("animate"); }
                });
            }
            window.addEventListener("scroll", checkScroll);
            checkScroll();
        });
    </script>
</head>
<body>
<nav>
    <?php include 'header.php'; ?>
</nav>
<div class="container-fluid px-0">
    <?php
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        echo "<div class='alert alert-{$type} text-center mb-0 rounded-0'><i class='fa-solid fa-bell me-2'></i>{$message}</div>";
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
    ?>
    <div id="articlesCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php if (!empty($carousel_items)): ?>
                <?php foreach ($carousel_items as $index => $item): ?>
                    <button type="button" data-bs-target="#articlesCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="carousel-inner">
            <?php if (!empty($carousel_items)): ?>
                <?php foreach ($carousel_items as $index => $item): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($item['title']) ?>">
                        <div class="carousel-caption">
                            <h5><?= htmlspecialchars($item['title']) ?></h5>
                            <p><?= htmlspecialchars($item['description']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active">
                    <img src="images/default_carousel.jpg" class="d-block w-100" alt="Výchozí obrázek">
                    <div class="carousel-caption"><h5>Vítejte na stránkách Warriors Chlumec</h5><p>Obsah karuselu bude brzy doplněn.</p></div>
                </div>
            <?php endif; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#articlesCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Předchozí</span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#articlesCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Další</span></button>
    </div>
</div>
<div class="container manage-carousel-container">
    <?php if ($user_is_logged_in): ?>
        <a href="admin_carousel.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-cog"></i> Spravovat Karusel</a>
    <?php endif; ?>
</div>
<main class="container py-3 py-md-4">
    <div class="row">
        <div class="col-lg-1 d-none d-lg-block"></div>
        <div class="col-lg-8 col-md-12 main-content-row">
            <h3 class="section-title text-uppercase text-center">Nejúspěšnější hráči</h3>
            <div class="player-triangle-wrapper">
                <div class="players-grid">
                    <?php if (!empty($top_skaters)): ?>
                        <?php foreach ($top_skaters as $player): ?>
                            <div class="card" style="cursor:pointer;" onclick="window.location.href='profil_hrace.php?jmeno=<?= urlencode($player['jmeno']) ?>'">
                                <img src="<?= htmlspecialchars($player['fotka_url'] ?? 'images/default_photo.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($player['jmeno']) ?>">
                                <div class="card-body">
                                    <h6 class="card-title text-uppercase fw-semibold"><?= htmlspecialchars($player['jmeno']) ?></h6>
                                    <p class="card-text">Body: <strong><?= htmlspecialchars($player['body']) ?></strong></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">Žádní hráči k zobrazení.</p>
                    <?php endif; ?>
                </div>
            </div>
            <h3 class="mt-5 section-title text-uppercase text-center">Nejúspěšnější brankáři</h3>
            <div class="player-triangle-wrapper">
                <div class="players-grid">
                    <?php if (!empty($top_goalies)): ?>
                        <?php foreach ($top_goalies as $goalie): ?>
                            <div class="card" style="cursor:pointer;" onclick="window.location.href='profil_hrace.php?jmeno=<?= urlencode($goalie['jmeno']) ?>'">
                                <img src="<?= htmlspecialchars($goalie['fotka_url'] ?? 'images/default_photo.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($goalie['jmeno']) ?>">
                                <div class="card-body">
                                    <h6 class="card-title text-uppercase fw-semibold"><?= htmlspecialchars($goalie['jmeno']) ?></h6>
                                    <p class="card-text">Úspěšnost: <strong><?= htmlspecialchars(number_format($goalie['uspesnost'] ?? 0, 2)) ?>%</strong></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">Žádní brankáři k zobrazení.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-12 sidebar-row">
            <div class="sticky-top" style="top: 20px;">
                <?php if ($closestBirthdayPlayer && isset($closestBirthdayPlayer['jmeno'])): ?>
                    <a href="profil_hrace.php?jmeno=<?= urlencode($closestBirthdayPlayer['jmeno']) ?>" class="sidebar-card-link">
                        <div> <h5 class="sidebar-title">Nejbližší narozeniny</h5>
                            <div class="sidebar-card">
                                <img src="<?= htmlspecialchars($closestBirthdayPlayer['fotka_url'] ?? 'images/default_photo.jpg') ?>" alt="<?= htmlspecialchars($closestBirthdayPlayer['jmeno']) ?>">
                                <h6><?= htmlspecialchars($closestBirthdayPlayer['jmeno']) ?></h6>
                                <p>
                                    <?php
                                    if(isset($closestBirthdayPlayer['datum_narozeni'])) {
                                        $birthDate = new DateTime($closestBirthdayPlayer['datum_narozeni']);
                                        $today = new DateTime();
                                        $age = $today->diff($birthDate)->y;
                                    } else {
                                        $age = '';
                                    }
                                    if ($minDays === 0) {
                                        echo "<strong>Právě dnes slaví " . ($age ? $age . '.' : '') . "!</strong><br>Přejeme všechno nejlepší!";
                                    } elseif ($minDays === 1) {
                                        echo "Narozeniny oslaví <strong>již zítra!</strong>";
                                    } else {
                                        echo "Narozeniny oslaví za <strong>{$minDays}</strong> dní.";
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php elseif ($closestBirthdayPlayer): ?>
                    <div>
                        <h5 class="sidebar-title">Nejbližší narozeniny</h5>
                        <div class="sidebar-card">
                             <p class="text-muted">Informace o narozeninách nejsou kompletní.</p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($most_penalized_player && isset($most_penalized_player['jmeno']) && isset($most_penalized_player['trestne_minuty']) && $most_penalized_player['trestne_minuty'] > 0): ?>
                    <a href="profil_hrace.php?jmeno=<?= urlencode($most_penalized_player['jmeno']) ?>" class="sidebar-card-link">
                        <div class="mt-4"> <h5 class="sidebar-title">Král trestné lavice</h5>
                            <div class="sidebar-card">
                                <img src="<?= htmlspecialchars($most_penalized_player['fotka_url'] ?? 'images/default_photo.jpg') ?>" alt="<?= htmlspecialchars($most_penalized_player['jmeno']) ?>">
                                <h6><?= htmlspecialchars($most_penalized_player['jmeno']) ?></h6>
                                <p>
                                    Celkem <strong><?= htmlspecialchars($most_penalized_player['trestne_minuty']) ?></strong> tr. min.
                                </p>
                            </div>
                        </div>
                    </a>
                 <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<div class="container my-4 my-md-5"> 
    <div class="row">
        <div class="col-lg-6 d-flex mb-3 mb-lg-0">
            <div class="match-card w-100">
                <div class="match-header">
                    <h4>Poslední zápas</h4>
                    <?php if($last_match && !empty($last_match['faze_souteze'])): ?>
                        <span class="match-type"><?= htmlspecialchars($last_match['faze_souteze']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($last_match): ?>
                    <div class="match-body">
                        <div class="match-team">
                            <img src="<?= htmlspecialchars($last_match['domaci_logo_url'] ?? 'images/default_logo.png') ?>" alt="<?= htmlspecialchars($last_match['domaci_tym']) ?>">
                            <h5><?= htmlspecialchars($last_match['domaci_tym']) ?></h5>
                        </div>
                        <div class="match-details">
                            <div class="match-score">
                                <span><?= htmlspecialchars($last_match['domaci_skore']) ?></span>
                                <span>:</span>
                                <span><?= htmlspecialchars($last_match['hostujici_skore']) ?></span>
                            </div>
                            <div class="match-status">Konečný stav</div>
                        </div>
                        <div class="match-team">
                            <img src="<?= htmlspecialchars($last_match['hostujici_logo_url'] ?? 'images/default_logo.png') ?>" alt="<?= htmlspecialchars($last_match['hostujici_tym']) ?>">
                            <h5><?= htmlspecialchars($last_match['hostujici_tym']) ?></h5>
                        </div>
                    </div>
                    <div class="match-footer">
                        <a href="zapasy.php" class="btn btn-outline-dark btn-sm">Všechny odehrané zápasy</a>
                    </div>
                <?php else: ?>
                     <div class="match-body"><p class="mb-0 text-muted">Žádný odehraný zápas k zobrazení.</p></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-6 d-flex">
            <div class="match-card w-100">
                 <div class="match-header">
                    <h4>Příští zápas</h4>
                    <?php if($future_match && !empty($future_match['faze_souteze'])): ?>
                        <span class="match-type"><?= htmlspecialchars($future_match['faze_souteze']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($future_match): ?>
                    <div class="match-body">
                        <div class="match-team">
                            <img src="<?= htmlspecialchars($future_match['domaci_logo_url'] ?? 'images/default_logo.png') ?>" alt="<?= htmlspecialchars($future_match['domaci_tym']) ?>">
                            <h5><?= htmlspecialchars($future_match['domaci_tym']) ?></h5>
                        </div>
                        <div class="match-details">
                            <div class="match-date-time"><?= htmlspecialchars($future_match['datum_cas_text']) ?></div>
                        </div>
                        <div class="match-team">
                             <img src="<?= htmlspecialchars($future_match['hostujici_logo_url'] ?? 'images/default_logo.png') ?>" alt="<?= htmlspecialchars($future_match['hostujici_tym']) ?>">
                            <h5><?= htmlspecialchars($future_match['hostujici_tym']) ?></h5>
                        </div>
                    </div>
                    <div class="match-footer">
                        <a href="zapasy.php" class="btn btn-dark btn-sm">Všechny budoucí zápasy</a>
                    </div>
                <?php else: ?>
                    <div class="match-body"><p class="mb-0 text-muted">Žádný budoucí zápas k zobrazení.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<footer>
    <?php include 'footer.php'; ?>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>