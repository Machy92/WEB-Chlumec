<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Editovat aktualitu</title>
    <link rel="icon" type="image/x-icon" href="chlumeclogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Editovat aktualitu</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="title" class="form-label">Název:</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($article["title"]) ?>" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Obsah:</label>
            <textarea id="content" name="content" rows="5" class="form-control" required><?= htmlspecialchars($article["content"]) ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">💾 Uložit změny</button>
    </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
