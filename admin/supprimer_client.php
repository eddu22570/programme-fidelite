<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: clients.php');
    exit;
}

$id = (int)$_GET['id'];

// Récupérer l'email du client avant suppression (pour le log)
$stmt = $pdo->prepare("SELECT email FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();

if ($client) {
    ajouter_log(
        $pdo,
        'admin',
        $_SESSION['admin_id'],
        'suppression_client',
        $client['email'],
        "Suppression du client {$client['email']} (id $id)"
    );
}

// Suppression réelle
$stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);

header('Location: clients.php');
exit;
?>
