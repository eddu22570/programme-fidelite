<?php
// Active l'affichage des erreurs pour le debug (à retirer en prod)
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$succes = "";
$erreurs = [];

// Suppression récursive d'une entité et de ses enfants
function supprimerEntiteRecursif($pdo, $id) {
    $stmt = $pdo->prepare("SELECT id FROM entites WHERE parent_id = ?");
    $stmt->execute([$id]);
    $enfants = $stmt->fetchAll();
    foreach ($enfants as $enfant) {
        supprimerEntiteRecursif($pdo, $enfant['id']);
    }
    $stmt = $pdo->prepare("DELETE FROM entites WHERE id = ?");
    $stmt->execute([$id]);
}

// Suppression
if (isset($_GET['supprimer'])) {
    $id_suppr = (int)$_GET['supprimer'];
    supprimerEntiteRecursif($pdo, $id_suppr);
    header('Location: entites.php');
    exit;
}

// Pour modification
$modifier = false;
$entite_modif = ['id'=>null, 'nom'=>'', 'description'=>'', 'parent_id'=>null];

if (isset($_GET['modifier'])) {
    $modifier = true;
    $id_modif = (int)$_GET['modifier'];
    $stmt = $pdo->prepare("SELECT * FROM entites WHERE id = ?");
    $stmt->execute([$id_modif]);
    $entite_modif = $stmt->fetch();
    if (!$entite_modif) {
        $erreurs[] = "Entité introuvable.";
        $modifier = false;
        $entite_modif = ['id'=>null, 'nom'=>'', 'description'=>'', 'parent_id'=>null];
    }
}

// Traitement formulaire (ajout ou modification)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $parent_id = ($_POST['parent_id'] === '' ? null : (int)$_POST['parent_id']);
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;

    if (empty($nom)) $erreurs[] = "Le nom est obligatoire.";
    if ($id !== null && $parent_id === $id) $erreurs[] = "Une entité ne peut pas être son propre parent.";

    // Boucle dans l'arborescence
    function estDescendant($pdo, $id, $parent_id) {
        if ($parent_id === null) return false;
        if ($parent_id === $id) return true;
        $stmt = $pdo->prepare("SELECT parent_id FROM entites WHERE id = ?");
        $stmt->execute([$parent_id]);
        $parent = $stmt->fetchColumn();
        return estDescendant($pdo, $id, $parent);
    }
    if ($id !== null && estDescendant($pdo, $id, $parent_id)) {
        $erreurs[] = "Le parent sélectionné est un descendant de cette entité.";
    }

    if (empty($erreurs)) {
        if ($id === null) {
            $stmt = $pdo->prepare("INSERT INTO entites (nom, description, parent_id) VALUES (?, ?, ?)");
            if ($stmt->execute([$nom, $description, $parent_id])) {
                $succes = "Entité ajoutée avec succès.";
            } else {
                $erreurs[] = "Erreur lors de l'insertion en base.";
            }
        } else {
            $stmt = $pdo->prepare("UPDATE entites SET nom=?, description=?, parent_id=? WHERE id=?");
            if ($stmt->execute([$nom, $description, $parent_id, $id])) {
                $succes = "Entité modifiée avec succès.";
            } else {
                $erreurs[] = "Erreur lors de la mise à jour.";
            }
        }
        header('Location: entites.php');
        exit;
    }
}

// Affichage arborescent
function afficherEntites($pdo, $parent_id = null, $niveau = 0) {
    $sql = "SELECT * FROM entites WHERE parent_id ";
    $sql .= is_null($parent_id) ? "IS NULL" : "= ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(is_null($parent_id) ? [] : [$parent_id]);
    $entites = $stmt->fetchAll();

    foreach ($entites as $entite) {
        echo str_repeat('&nbsp;', $niveau * 6);
        echo htmlspecialchars($entite['nom'] ?? '') . " ";
        echo "<a href='entites.php?modifier=" . $entite['id'] . "'>[Modifier]</a> ";
        echo "<a href='entites.php?supprimer=" . $entite['id'] . "' onclick=\"return confirm('Supprimer cette entité et ses sous-entités ?')\">[Supprimer]</a><br>";
        afficherEntites($pdo, $entite['id'], $niveau + 1);
    }
}

// Pour le select parent
function afficherOptions($pdo, $excludeId, $selectedId, $parentId = null, $niveau = 0) {
    $sql = "SELECT * FROM entites WHERE parent_id ";
    $sql .= is_null($parentId) ? "IS NULL" : "= ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(is_null($parentId) ? [] : [$parentId]);
    $entites = $stmt->fetchAll();

    foreach ($entites as $entite) {
        if ($entite['id'] == $excludeId) continue;
        $selected = ($entite['id'] == $selectedId) ? 'selected' : '';
        echo str_repeat('&nbsp;', $niveau * 4);
        echo "<option value='{$entite['id']}' $selected>" . str_repeat('— ', $niveau) . htmlspecialchars($entite['nom'] ?? '') . "</option>";
        afficherOptions($pdo, $excludeId, $selectedId, $entite['id'], $niveau + 1);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des entités hiérarchiques</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        form { margin-bottom: 30px; }
        label { display: block; margin-top: 10px; }
        input[type=text], textarea, select { width: 300px; padding: 6px; }
        .btn { margin-top: 12px; padding: 8px 16px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Gestion des entités hiérarchiques</h1>
    <?php
    if ($succes) echo '<ul><li style="color:green;">'.$succes.'</li></ul>';
    if (!empty($erreurs)) {
        echo '<ul>';
        foreach ($erreurs as $e) echo '<li style="color:red;">'.$e.'</li>';
        echo '</ul>';
    }
    ?>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($entite_modif['id'] ?? '') ?>">
        <label>Nom *</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($entite_modif['nom'] ?? '') ?>" required>
        <label>Description</label>
        <input type="text" name="description" value="<?= htmlspecialchars($entite_modif['description'] ?? '') ?>">
        <label>Entité parente</label>
        <select name="parent_id">
            <option value="">-- Aucune (racine) --</option>
            <?php afficherOptions($pdo, $entite_modif['id'], $entite_modif['parent_id']); ?>
        </select>
        <button type="submit" class="btn"><?= $modifier ? 'Modifier' : 'Ajouter' ?> l'entité</button>
        <?php if ($modifier): ?>
            <a href="entites.php" style="margin-left: 15px;">Annuler</a>
        <?php endif; ?>
    </form>
    <h2>Liste des entités</h2>
    <?php afficherEntites($pdo); ?>
    <a href="index.php" style="display:block; margin-top: 20px;">Retour au tableau de bord</a>
</div>
</body>
</html>
