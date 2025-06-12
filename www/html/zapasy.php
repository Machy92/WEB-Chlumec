<?php
session_start();
require_once 'config.php'; 

define('WARRIORS_TEAM_NAME_VARIANTS', ["HSÚ SHC Warriors Chlumec", "Warriors Chlumec", "Warriors Chlumec B", "SHC Warriors Chlumec"]);

$odehrane_params = 'select=*&odehrano=eq.true&order=datum_cas_text.desc';
$odehrane_zapasy_raw = fetchSupabaseData('zapasy', $odehrane_params);

$potencialni_budouci_params = 'select=*&odehrano=eq.false&order=datum_cas_text.asc'; 
$potencialni_budouci_zapasy_raw = fetchSupabaseData('zapasy', $potencialni_budouci_params);

$opravdove_budouci_zapasy = [];
if (!empty($potencialni_budouci_zapasy_raw)) {
    $dnes_pulnoc = new DateTime(); 
    $dnes_pulnoc->setTime(0, 0, 0); 

    foreach ($potencialni_budouci_zapasy_raw as $zapas) {
        $datum_text = $zapas['datum_cas_text'];
        $datum_zapasu_obj = null;
        $datum_text_pro_parsovani = preg_replace('/^[A-ZŽŠČŘĎŤŇÚŮÝÁÉÍÓa-zžščřďťňúůýáéíó]{2,3}\s+/u', '', $datum_text);
        $datum_text_pro_parsovani = str_replace('.', '.', $datum_text_pro_parsovani);

        $datum_zapasu_obj = DateTime::createFromFormat('d.m.Y, H:i', $datum_text_pro_parsovani);
        if (!$datum_zapasu_obj) {
            $datum_zapasu_obj = DateTime::createFromFormat('d.m.Y', $datum_text_pro_parsovani);
        }

        if ($datum_zapasu_obj) {
            $datum_zapasu_obj->setTime(0, 0, 0); 
            if ($datum_zapasu_obj >= $dnes_pulnoc) {
                $opravdove_budouci_zapasy[] = $zapas;
            }
        } else {
            error_log("Nepodařilo se rozparsovat datum pro budoucí zápas: " . $datum_text);
        }
    }
}

$faze_order = ['Play-Off', 'Nadstavba - skupina A', 'Základní část'];

$available_phases_played = [];
if (!empty($odehrane_zapasy_raw)) {
    foreach ($odehrane_zapasy_raw as $zapas) {
        $faze = $zapas['faze_souteze'] ?? 'Neznámá fáze';
        if (!in_array($faze, $available_phases_played)) {
            $available_phases_played[] = $faze;
        }
    }

    usort($available_phases_played, function($a, $b) use ($faze_order) {
        $pos_a = array_search($a, $faze_order);
        $pos_b = array_search($b, $faze_order);
        if ($pos_a === false && $pos_b === false) return strcmp($a, $b);
        if ($pos_a === false) return 1;
        if ($pos_b === false) return -1;
        return $pos_a - $pos_b;
    });
}

$selected_phase = $_GET['faze'] ?? 'vsechny'; 

$filtered_odehrane_zapasy_for_grouping = [];
if ($selected_phase === 'vsechny') {
    $filtered_odehrane_zapasy_for_grouping = $odehrane_zapasy_raw;
} else {
    if (!empty($odehrane_zapasy_raw)) {
        foreach ($odehrane_zapasy_raw as $zapas) {
            if (($zapas['faze_souteze'] ?? 'Neznámá fáze') === $selected_phase) {
                $filtered_odehrane_zapasy_for_grouping[] = $zapas;
            }
        }
    }
}

function group_games_by_phase($games_array, $phase_order_array) {
    $grouped_games = [];
    if (!empty($games_array)) {
        foreach ($games_array as $zapas) {
            $faze = $zapas['faze_souteze'] ?? 'Neznámá fáze';
            $grouped_games[$faze][] = $zapas;
        }
    }
    uksort($grouped_games, function($a, $b) use ($phase_order_array) {
        $pos_a = array_search($a, $phase_order_array);
        $pos_b = array_search($b, $phase_order_array);
        if ($pos_a === false && $pos_b === false) return strcmp($a, $b);
        if ($pos_a === false) return 1;
        if ($pos_b === false) return -1;
        return $pos_a - $pos_b;
    });
    return $grouped_games;
}

$odehrane_zapasy_podle_faze = group_games_by_phase($filtered_odehrane_zapasy_for_grouping, $faze_order);
$budouci_zapasy_podle_faze = group_games_by_phase($opravdove_budouci_zapasy, $faze_order); 

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přehled Zápasů - Warriors Chlumec</title>
    <link rel="icon" type="image/x-icon" href="chlumeclogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .game-card { border: 1px solid #e0e0e0; border-radius: .375rem; margin-bottom: 1rem; padding: 1rem; background-color: #fff; transition: box-shadow .15s ease-in-out; }
        .game-card:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); }
        .game-card.warriors-win { border-left: 5px solid #198754; }
        .game-card.warriors-loss { border-left: 5px solid #dc3545; }
        .game-card.warriors-draw { border-left: 5px solid #ffc107; }
        .team-name-home, .team-name-away { font-weight: bold; }
        .score-section { font-weight: bold; font-size: 1.25rem; white-space: nowrap; }
        .vs-separator { color: #6c757d; margin: 0 0.5rem; }
        .game-meta { font-size: 0.875rem; color: #6c757d; }
        .section-heading { margin-top: 2.5rem; margin-bottom: 0.5rem; padding-bottom: 0.75rem; font-weight: bold; font-size: 1.75rem; border-bottom: 2px solid #6c757d; }
        .phase-title { margin-top: 1.5rem; margin-bottom: 1rem; border-bottom: 1px dashed #ccc; padding-bottom: 0.5rem; color: #333; font-weight: bold; font-size: 1.25rem; }
        .game-status { font-size: 0.8rem; font-style: italic; }
        .filter-form .form-select { max-width: 300px; } 
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-4 mb-5">
        <h1 class="text-center mb-5">Přehled Zápasů</h1>

        <div class="row">
            <div class="col-lg-6">
                <h2 class="section-heading">Odehrané zápasy</h2>

                <form method="GET" action="zapasy.php" class="mb-3 filter-form">
                    <label for="faze_select" class="form-label">Filtrovat podle fáze:</label>
                    <select name="faze" id="faze_select" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                        <option value="vsechny" <?= ($selected_phase === 'vsechny' ? 'selected' : '') ?>>Všechny fáze</option>
                        <?php foreach ($available_phases_played as $faze_option): ?>
                            <option value="<?= htmlspecialchars($faze_option) ?>" <?= ($selected_phase === $faze_option ? 'selected' : '') ?>>
                                <?= htmlspecialchars($faze_option) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if (!empty($odehrane_zapasy_podle_faze)): ?>
                    <?php foreach ($odehrane_zapasy_podle_faze as $faze => $zapasy_ve_fazi): ?>
                        <h3 class="phase-title"><?= htmlspecialchars($faze) ?></h3>
                        <?php foreach ($zapasy_ve_fazi as $zapas): ?>
                            <?php
                            $card_border_class = '';
                            if ($zapas['odehrano']) {
                                if ($zapas['vysledek_warriors'] === 'vyhra') $card_border_class = ' warriors-win';
                                elseif ($zapas['vysledek_warriors'] === 'prohra') $card_border_class = ' warriors-loss';
                                elseif ($zapas['vysledek_warriors'] === 'remiza') $card_border_class = ' warriors-draw';
                            }
                            ?>
                            <div class="game-card shadow-sm<?= $card_border_class ?>">
                                <div class="row align-items-center">
                                    <div class="col-12 col-sm-5 text-sm-end team-name-home">
                                        <?= htmlspecialchars($zapas['domaci_tym']) ?>
                                    </div>
                                    <div class="col-12 col-sm-2 text-center score-section my-2 my-sm-0">
                                        <span><?= htmlspecialchars($zapas['domaci_skore']) ?></span>
                                        <span class="vs-separator">:</span>
                                        <span><?= htmlspecialchars($zapas['hostujici_skore']) ?></span>
                                    </div>
                                    <div class="col-12 col-sm-5 text-sm-start team-name-away">
                                        <?= htmlspecialchars($zapas['hostujici_tym']) ?>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-12 text-center game-meta">
                                        <?= htmlspecialchars($zapas['datum_cas_text']) ?>
                                        | <span class="game-status">Odehráno</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php elseif ($selected_phase !== 'vsechny' && empty($odehrane_zapasy_podle_faze)): ?>
                     <div class="alert alert-secondary text-center mt-4">Pro vybranou fázi nebyly nalezeny žádné odehrané zápasy.</div>
                <?php else: ?>
                    <div class="alert alert-secondary text-center mt-4">Nebyly nalezeny žádné odehrané zápasy.</div>
                <?php endif; ?>
            </div>

            <div class="col-lg-6">
                <h2 class="section-heading">Budoucí zápasy</h2>
                <?php if (!empty($budouci_zapasy_podle_faze)): ?>
                     <?php foreach ($budouci_zapasy_podle_faze as $faze => $zapasy_ve_fazi): ?>
                        <h3 class="phase-title"><?= htmlspecialchars($faze) ?></h3>
                        <?php foreach ($zapasy_ve_fazi as $zapas): ?>
                            <div class="game-card shadow-sm">
                                <div class="row align-items-center">
                                    <div class="col-12 col-sm-5 text-sm-end team-name-home">
                                        <?= htmlspecialchars($zapas['domaci_tym']) ?>
                                    </div>
                                    <div class="col-12 col-sm-2 text-center score-section my-2 my-sm-0">
                                        <span class="vs-separator">vs</span>
                                    </div>
                                    <div class="col-12 col-sm-5 text-sm-start team-name-away">
                                        <?= htmlspecialchars($zapas['hostujici_tym']) ?>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-12 text-center game-meta">
                                        <?= htmlspecialchars($zapas['datum_cas_text']) ?>
                                        | <span class="game-status">Připravuje se</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-secondary text-center mt-4">Aktuálně nejsou naplánovány žádné budoucí zápasy.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>