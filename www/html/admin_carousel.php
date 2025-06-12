<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['user_id'])) { 

    die("Přístup odepřen. Pro přístup na tuto stránku se musíte přihlásit.");
}

$message = '';
$message_type = 'info'; 
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    $message_type = $_SESSION['admin_message_type'] ?? 'info';
    unset($_SESSION['admin_message']);
    unset($_SESSION['admin_message_type']);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $image_url = trim($_POST['image_url'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $item_order = filter_input(INPUT_POST, 'item_order', FILTER_VALIDATE_INT, ["options" => ["default" => 0]]);
    $is_active = isset($_POST['is_active']); 
    $id_to_update = filter_input(INPUT_POST, 'id_to_update', FILTER_VALIDATE_INT);

    if ($action === 'add' || $action === 'update') {
        if (empty($image_url)) {
            $_SESSION['admin_message'] = "URL obrázku je povinné.";
            $_SESSION['admin_message_type'] = "danger";
        } else {
            $payload = [
                'image_url' => $image_url,
                'title' => $title,
                'description' => $description,
                'item_order' => $item_order,
                'is_active' => $is_active
            ];
            if ($action === 'add') {
                executeSupabaseWrite('POST', 'carousel_items', $payload);
            } elseif ($action === 'update' && $id_to_update) {
                executeSupabaseWrite('PATCH', 'carousel_items', $payload, '?id=eq.' . $id_to_update);
            }

            header("Location: admin_carousel.php"); 
            exit;
        }
    }
}


if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_to_delete = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id_to_delete) {
        executeSupabaseWrite('DELETE', 'carousel_items', null, '?id=eq.' . $id_to_delete);
    }
    header("Location: admin_carousel.php");
    exit;
}


$edit_item = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_to_edit = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id_to_edit) {
        $edit_data_array = fetchSupabaseData('carousel_items', 'select=*&id=eq.' . $id_to_edit);
        if (!empty($edit_data_array)) {
            $edit_item = $edit_data_array[0];
        }
    }
}


$items = fetchSupabaseData('carousel_items', 'select=*&order=item_order.asc');

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Administrace Karuselu</title>
    <link rel="icon" type="image/x-icon" href="chlumeclogo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php';?>
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Administrace Karuselu</h2>
            <a href="index.php" class="btn btn-light">Zpět na hlavní stránku</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= $edit_item ? 'Upravit položku karuselu' : 'Přidat novou položku do karuselu' ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="admin_carousel.php">
                    <input type="hidden" name="action" value="<?= $edit_item ? 'update' : 'add' ?>">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="id_to_update" value="<?= htmlspecialchars($edit_item['id']) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="image_url" class="form-label">URL/cesta obrázku <small>(např. images/carousel/obr1.jpg)</small>*</label>
                        <input type="text" class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($edit_item['image_url'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Titulek</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($edit_item['title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Popis</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($edit_item['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="item_order" class="form-label">Pořadí <small>(číslo, menší se zobrazí dříve)</small></label>
                            <input type="number" class="form-control" id="item_order" name="item_order" value="<?= htmlspecialchars($edit_item['item_order'] ?? 0) ?>">
                        </div>
                        <div class="col-md-6 mb-3 align-self-center">
                            <div class="form-check mt-3">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= ($edit_item['is_active'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Aktivní (zobrazit v karuselu)</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success"><?= $edit_item ? 'Uložit změny' : 'Přidat položku' ?></button>
                    <?php if ($edit_item): ?>
                        <a href="admin_carousel.php" class="btn btn-secondary">Zrušit úpravu a přidat novou</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <h3>Existující položky karuselu</h3>
        <?php if (!empty($items)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Náhled</th>
                            <th>Titulek</th>
                            <th>URL Obrázku</th>
                            <th>Pořadí</th>
                            <th>Aktivní</th>
                            <th style="width: 150px;">Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title'] ?? 'Obrázek karuselu') ?>" style="max-height: 40px; max-width: 80px; object-fit: cover;"></td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td><small><?= htmlspecialchars($item['image_url']) ?></small></td>
                                <td><?= htmlspecialchars($item['item_order']) ?></td>
                                <td><?= $item['is_active'] ? '<span class="badge bg-success">Ano</span>' : '<span class="badge bg-secondary">Ne</span>' ?></td>
                                <td>
                                    <a href="admin_carousel.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary mb-1">Upravit</a>
                                    <a href="admin_carousel.php?action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger mb-1" onclick="return confirm('Opravdu smazat tuto položku? Toto nelze vrátit zpět.');">Smazat</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Zatím nebyly přidány žádné položky do karuselu.</div>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>