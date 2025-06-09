<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$stmt = $pdo->query("SELECT * FROM vendeurs");
$vendeurs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des vendeurs</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Gestion des vendeurs</h1>
    <a href="ajouter_vendeur.php" class="btn">Ajouter un vendeur</a>
    <table>
        <tr>
            <th>ID</th><th>Nom</th><th>Email</th><th>Actif</th><th>Actions</th>
        </tr>
        <?php foreach ($vendeurs as $v): ?>
        <tr>
            <td><?= $v['id'] ?></td>
            <td><?= htmlspecialchars($v['nom']) ?></td>
            <td><?= htmlspecialchars($v['email']) ?></td>
            <td><?= $v['actif'] ? 'Oui' : 'Non' ?></td>
            <td>
                <a href="modifier_vendeur.php?id=<?= $v['id'] ?>">Modifier</a> |
                <a href="supprimer_vendeur.php?id=<?= $v['id'] ?>" onclick="return confirm('Supprimer ce vendeur ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="index.php">Retour</a>
</div>
</body>
</html>
