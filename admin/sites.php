<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$succes = "";
$erreurs = [];

// Récupérer les entités pour le formulaire
$entites = $pdo->query("SELECT * FROM entites ORDER BY nom")->fetchAll();

// Ajout d’un site
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $entite_id = $_POST['entite_id'] === '' ? null : (int)$_POST['entite_id'];

    if (empty($nom)) {
        $erreurs[] = "Le nom du site est obligatoire.";
    }
    if ($entite_id === null) {
        $erreurs[] = "L'entité est obligatoire.";
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("INSERT INTO sites (nom, adresse, entite_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$nom, $adresse, $entite_id])) {
            $succes = "Site ajouté avec succès.";
        } else {
            $erreurs[] = "Erreur lors de l'ajout du site.";
        }
    }
}

// Suppression d’un site
if (isset($_GET['supprimer'])) {
    $id_suppr = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM sites WHERE id = ?");
    $stmt->execute([$id_suppr]);
    header('Location: sites.php');
    exit;
}

// Récupérer la liste des sites avec le nom de l’entité associée
$sites = $pdo->query("
    SELECT s.*, e.nom AS entite_nom
    FROM sites s
    LEFT JOIN entites e ON s.entite_id = e.id
    ORDER BY s.nom
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des sites</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Gestion des sites</h1>

    <?php
    if ($succes) echo '<ul><li style="color:green;">' . $succes . '</li></ul>';
    if (!empty($erreurs)) {
        echo '<ul>';
        foreach ($erreurs as $e) echo '<li style="color:red;">' . $e . '</li>';
        echo '</ul>';
    }
    ?>

    <form method="post">
        <label>Nom du site *</label>
        <input type="text" name="nom" required>
        <label>Adresse</label>
        <input type="text" name="adresse">
        <label>Entité *</label>
        <select name="entite_id" required>
            <option value="">-- Choisir une entité --</option>
            <?php foreach ($entites as $entite): ?>
                <option value="<?= $entite['id'] ?>"><?= htmlspecialchars($entite['nom'] ?? '') ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="ajouter" class="btn">Ajouter</button>
    </form>

    <h2>Liste des sites</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Entité</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sites as $site): ?>
                <tr>
                    <td><?= $site['id'] ?></td>
                    <td><?= htmlspecialchars($site['nom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($site['adresse'] ?? '') ?></td>
                    <td><?= htmlspecialchars($site['entite_nom'] ?? 'Non rattaché') ?></td>
                    <td>
                        <a href="modifier_site.php?id=<?= $site['id'] ?>">Modifier</a> |
                        <a href="sites.php?supprimer=<?= $site['id'] ?>" onclick="return confirm('Supprimer ce site ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="index.php" style="display:block; margin-top: 20px;">Retour au tableau de bord</a>
</div>
</body>
</html>
