<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Sécurité : accès réservé aux admins connectés
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM admins ORDER BY id ASC");
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des administrateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Administrateurs</h2>
    <a href="admin_ajout.php" class="btn btn-primary mb-3">➕ Ajouter un admin</a>
    <a href="index.php" class="btn btn-primary mb-3">Accueil</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($admins as $admin): ?>
            <tr>
                <td><?= htmlspecialchars($admin['nom']) ?></td>
                <td><?= htmlspecialchars($admin['email']) ?></td>
                <td>
                    <a href="admin_modif.php?id=<?= $admin['id'] ?>" class="btn btn-sm btn-warning">✏️ Modifier</a>
                    <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                        <a href="admin_suppr.php?id=<?= $admin['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression ?')">🗑️ Supprimer</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
