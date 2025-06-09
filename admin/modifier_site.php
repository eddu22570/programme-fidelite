<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: sites.php');
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ?");
$stmt->execute([$id]);
$site = $stmt->fetch();

if (!$site) {
    echo "Site introuvable.";
    exit;
}

$entites = $pdo->query("SELECT * FROM entites ORDER BY nom")->fetchAll();

$succes = "";
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $stmt = $pdo->prepare("UPDATE sites SET nom = ?, adresse = ?, entite_id = ? WHERE id = ?");
        if ($stmt->execute([$nom, $adresse, $entite_id, $id])) {
            $succes = "Site modifié avec succès.";
            // Recharger les données
            $stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ?");
            $stmt->execute([$id]);
            $site = $stmt->fetch();
        } else {
            $erreurs[] = "Erreur lors de la modification.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier site</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Modifier site</h1>

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
        <input type="text" name="nom" value="<?= htmlspecialchars($site['nom'] ?? '') ?>" required>
        <label>Adresse</label>
        <input type="text" name="adresse" value="<?= htmlspecialchars($site['adresse'] ?? '') ?>">
        <label>Entité *</label>
        <select name="entite_id" required>
            <option value="">-- Choisir une entité --</option>
            <?php foreach ($entites as $entite): ?>
                <option value="<?= $entite['id'] ?>" <?= ($site['entite_id'] == $entite['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($entite['nom'] ?? '') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn">Enregistrer</button>
    </form>

    <a href="sites.php" style="display:block; margin-top: 20px;">Retour à la liste des sites</a>
</div>
</body>
</html>
