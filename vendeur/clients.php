<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();

// Vérification de la connexion vendeur
if (!isset($_SESSION['vendeur_id'])) {
    header('Location: login.php');
    exit;
}

// On récupère tous les clients/utilisateurs, sans filtrage
$stmt = $pdo->query("SELECT u.*, s.nom AS site_nom 
    FROM utilisateurs u 
    LEFT JOIN sites s ON u.site_id = s.id 
    ORDER BY u.nom");
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tous les clients</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Tous les clients</h1>
    <?php if (empty($clients)): ?>
        <p>Aucun client à afficher.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Points</th>
                <th>Site</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($clients as $client): ?>
            <tr>
                <td><?= htmlspecialchars($client['nom'] ?? '') ?></td>
                <td><?= htmlspecialchars($client['email'] ?? '') ?></td>
                <td><?= (int)($client['points'] ?? 0) ?></td>
                <td><?= htmlspecialchars($client['site_nom'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <a href="index.php" style="display:block; margin-top: 20px;">Retour au tableau de bord</a>
</div>
</body>
</html>
