<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: vendeurs.php');
    exit;
}

$id = (int)$_GET['id'];

// Récupérer l'email du vendeur avant suppression (pour le log)
$stmt = $pdo->prepare("SELECT email FROM vendeurs WHERE id = ?");
$stmt->execute([$id]);
$vendeur = $stmt->fetch();

if ($vendeur) {
    ajouter_log(
        $pdo,
        'admin',
        $_SESSION['admin_id'],
        'suppression_vendeur',
        $vendeur['email'],
        "Suppression du vendeur {$vendeur['email']} (id $id)"
    );
}

// Suppression réelle
$stmt = $pdo->prepare("DELETE FROM vendeurs WHERE id = ?");
$stmt->execute([$id]);

header('Location: vendeurs.php');
exit;
?>
