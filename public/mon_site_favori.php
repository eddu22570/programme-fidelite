<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_favori_id'])) {
    $site_favori_id = !empty($_POST['site_favori_id']) ? (int)$_POST['site_favori_id'] : null;
    $stmt = $pdo->prepare("UPDATE utilisateurs SET site_favori_id = ? WHERE id = ?");
    $stmt->execute([$site_favori_id, $_SESSION['user_id']]);
    $message = "Votre site favori a été mis à jour.";
}

// Récupérer la liste des sites
$sites = $pdo->query("SELECT id, nom FROM sites ORDER BY nom")->fetchAll();

// Récupérer le site favori actuel du client
$stmt = $pdo->prepare("SELECT site_favori_id FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$site_favori_id = $stmt->fetchColumn();

// Récupérer le nom du site favori (si sélectionné)
$nom_site_favori = null;
if ($site_favori_id) {
    $stmt = $pdo->prepare("SELECT nom FROM sites WHERE id = ?");
    $stmt->execute([$site_favori_id]);
    $nom_site_favori = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon site favori</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .favori-form { margin: 30px 0; }
        .favori-label { font-weight: bold; }
        .success { color: green; margin-bottom: 20px; }
        .site-favori { margin-bottom: 20px; font-size: 1.1em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mon site favori</h1>
        <?php if ($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($nom_site_favori): ?>
            <div class="site-favori">
                <span class="favori-label">Votre site favori actuel :</span>
                <strong><?= htmlspecialchars($nom_site_favori) ?></strong>
            </div>
        <?php else: ?>
            <div class="site-favori">
                <span class="favori-label">Vous n'avez pas encore choisi de site favori.</span>
            </div>
        <?php endif; ?>

        <form method="post" class="favori-form">
            <label for="site_favori_id" class="favori-label">Sélectionnez votre site favori :</label>
            <select name="site_favori_id" id="site_favori_id">
                <option value="">-- Aucun favori --</option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?= $site['id'] ?>" <?= ($site['id'] == $site_favori_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($site['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Enregistrer</button>
        </form>

        <p style="margin-top:30px;color:#666;">
            <em>Astuce : vous pouvez utiliser votre compte sur tous les sites, mais votre favori sera affiché en priorité.</em>
        </p>
        <a href="dashboard.php">Retour à mon compte</a>
    </div>
</body>
</html>
