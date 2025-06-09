<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admins.php');
    exit;
}

$id = (int)$_GET['id'];

// On ne doit pas permettre à un admin de se supprimer lui-même
if ($id == $_SESSION['admin_id']) {
    header('Location: admins.php?err=supprimer_soi_meme');
    exit;
}

// Récupérer l'admin à supprimer pour le log
$stmt = $pdo->prepare("SELECT email, nom FROM admins WHERE id = ?");
$stmt->execute([$id]);
$adminToDelete = $stmt->fetch();

if (!$adminToDelete) {
    header('Location: admins.php');
    exit;
}

// Suppression après confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer']) && $_POST['confirmer'] === 'oui') {
    // Log avant suppression
    ajouter_log(
        $pdo,
        'admin',
        $_SESSION['admin_id'],
        'suppression_admin',
        $adminToDelete['email'],
        "Suppression de l'admin {$adminToDelete['email']} (id $id)"
    );

    // Suppression réelle
    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: admins.php?msg=suppression_ok');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer un admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Supprimer un administrateur</h2>
    <div class="alert alert-warning">
        <strong>Attention !</strong> Voulez-vous vraiment supprimer l'administrateur <b><?= htmlspecialchars($adminToDelete['email']) ?></b> (<?= htmlspecialchars($adminToDelete['nom']) ?>) ?
    </div>
    <form method="post">
        <button type="submit" name="confirmer" value="oui" class="btn btn-danger">Oui, supprimer</button>
        <a href="admins.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
</body>
</html>
