<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

// Récupérer la liste des sites pour le formulaire
$sites = $pdo->query("SELECT id, nom FROM sites ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Ajouter une offre
if (isset($_POST['ajouter'])) {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $date_debut = $_POST['date_debut'] ?: null;
    $date_fin = $_POST['date_fin'] ?: null;
    if ($titre) {
        $stmt = $pdo->prepare("INSERT INTO offres (titre, description, date_debut, date_fin) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titre, $description, $date_debut, $date_fin]);
        $offre_id = $pdo->lastInsertId();
        if (!empty($_POST['sites'])) {
            $stmt2 = $pdo->prepare("INSERT INTO offre_sites (offre_id, site_id) VALUES (?, ?)");
            foreach ($_POST['sites'] as $site_id) {
                $stmt2->execute([$offre_id, $site_id]);
            }
        }
        $message = "Offre ajoutée !";
    }
}

// Activer/désactiver une offre
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE offres SET actif = 1 - actif WHERE id = ?");
    $stmt->execute([$id]);
    $message = "Statut de l'offre modifié.";
}

// Supprimer une offre
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM offres WHERE id = ?");
    $stmt->execute([$id]);
    $message = "Offre supprimée.";
}

// Liste des offres
$offres = $pdo->query("SELECT * FROM offres ORDER BY date_debut DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque offre, récupérer les sites associés
foreach ($offres as &$offre) {
    $stmt = $pdo->prepare("SELECT s.nom FROM offre_sites os JOIN sites s ON os.site_id = s.id WHERE os.offre_id = ?");
    $stmt->execute([$offre['id']]);
    $offre['sites_noms'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
unset($offre);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Offres</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .offres-table { width:100%; border-collapse:collapse; margin-top:24px; }
        .offres-table th, .offres-table td { padding:8px 12px; border-bottom:1px solid #e0e0e0; }
        .offres-table th { background:#f5fafd; color:#2193b0; }
        .offres-table tr.inactive { color:#aaa; background:#f9f9f9; }
        .offres-actions a { margin-right:8px; }
        .add-offre-form { margin:32px 0 24px 0; padding:20px; background:#f5fafd; border-radius:14px; }
        .add-offre-form input, .add-offre-form textarea, .add-offre-form select { width:100%; margin-bottom:12px; padding:8px; border-radius:4px; border:1px solid #ddd; }
        .add-offre-form button { margin-top:8px; }
        .sites-list { font-size: 0.96em; color: #217dbb; }
    </style>
</head>
<body>
<div class="container">
    <nav style="text-align:right;">
        <a href="index.php" class="btn">Retour admin</a>
        &nbsp;|&nbsp;
        <a href="deconnexion.php" class="btn">Se déconnecter</a>
    </nav>
    <h1>Gestion des Offres</h1>
    <?php if ($message): ?>
        <div class="message" style="color:green;"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" class="add-offre-form">
        <h2>Ajouter une offre</h2>
        <input type="text" name="titre" placeholder="Titre de l'offre" required>
        <textarea name="description" placeholder="Description"></textarea>
        <input type="date" name="date_debut" placeholder="Date de début">
        <input type="date" name="date_fin" placeholder="Date de fin">
        <label for="sites">Sites concernés :</label>
        <select name="sites[]" id="sites" multiple size="4">
            <?php foreach ($sites as $site): ?>
                <option value="<?= $site['id'] ?>"><?= htmlspecialchars($site['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <small>Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs sites</small>
        <button type="submit" name="ajouter">Ajouter l'offre</button>
    </form>

    <h2>Liste des offres</h2>
    <table class="offres-table">
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Début</th>
            <th>Fin</th>
            <th>Sites concernés</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($offres as $offre): ?>
            <tr class="<?= !$offre['actif'] ? 'inactive' : '' ?>">
                <td><?= htmlspecialchars($offre['titre']) ?></td>
                <td><?= nl2br(htmlspecialchars($offre['description'])) ?></td>
                <td><?= htmlspecialchars($offre['date_debut']) ?></td>
                <td><?= htmlspecialchars($offre['date_fin']) ?></td>
                <td class="sites-list">
                    <?php if (!empty($offre['sites_noms'])): ?>
                        <?= implode(', ', array_map('htmlspecialchars', $offre['sites_noms'])) ?>
                    <?php else: ?>
                        <span style="color:#aaa;">Tous</span>
                    <?php endif; ?>
                </td>
                <td><?= $offre['actif'] ? 'Active' : 'Inactive' ?></td>
                <td class="offres-actions">
                    <a href="?toggle=1&id=<?= $offre['id'] ?>" class="btn"><?= $offre['actif'] ? 'Désactiver' : 'Activer' ?></a>
                    <a href="?delete=1&id=<?= $offre['id'] ?>" class="btn" onclick="return confirm('Supprimer cette offre ?')">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($offres)): ?>
            <tr><td colspan="7" style="text-align:center;color:#aaa;">Aucune offre pour l’instant.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
