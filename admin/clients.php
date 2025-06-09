<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$stmt = $pdo->query("SELECT * FROM utilisateurs");
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des clients</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Gestion des clients</h1>
    <table>
        <tr>
            <th>ID</th><th>Nom</th><th>Email</th><th>Points</th><th>Actions</th>
        </tr>
        <?php foreach ($clients as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['nom']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= (int)$c['points'] ?></td>
            <td>
                <a href="modifier_client.php?id=<?= $c['id'] ?>">Modifier</a> |
                <a href="supprimer_client.php?id=<?= $c['id'] ?>" onclick="return confirm('Supprimer ce client ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="index.php">Retour</a>
</div>
</body>
</html>
