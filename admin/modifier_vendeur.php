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
$stmt = $pdo->prepare("SELECT * FROM vendeurs WHERE id = ?");
$stmt->execute([$id]);
$vendeur = $stmt->fetch();

if (!$vendeur) {
    echo "Vendeur introuvable.";
    exit;
}

$succes = "";
$erreurs = [];

// Récupérer tous les sites avec entité associée
$sites = $pdo->query("SELECT s.*, e.nom AS entite_nom FROM sites s LEFT JOIN entites e ON s.entite_id = e.id ORDER BY e.nom, s.nom")->fetchAll();

// Récupérer les sites déjà affectés à ce vendeur
$stmt = $pdo->prepare("SELECT site_id FROM vendeurs_sites WHERE vendeur_id = ?");
$stmt->execute([$id]);
$sites_vendeur = array_column($stmt->fetchAll(), 'site_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
    $sites_affectes = $_POST['sites'] ?? [];

    if (empty($nom)) $erreurs[] = "Le nom est obligatoire.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";

    // Si mot de passe fourni, on le hashera
    $update_password = !empty($mot_de_passe);

    if (empty($erreurs)) {
        if ($update_password) {
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE vendeurs SET nom = ?, email = ?, mot_de_passe = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE vendeurs SET nom = ?, email = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $id]);
        }

        // Mettre à jour les affectations sites
        $pdo->prepare("DELETE FROM vendeurs_sites WHERE vendeur_id = ?")->execute([$id]);
        $stmt_insert = $pdo->prepare("INSERT INTO vendeurs_sites (vendeur_id, site_id) VALUES (?, ?)");
        foreach ($sites_affectes as $site_id) {
            $stmt_insert->execute([$id, (int)$site_id]);
        }

        // Ajout dans les logs
        $details = "Modification du vendeur $email (id $id)";
        if ($update_password) $details .= " + changement de mot de passe";
        $details .= ". Sites affectés : " . implode(', ', $sites_affectes);

        ajouter_log($pdo, 'admin', $_SESSION['admin_id'], 'modification_vendeur', $email, $details);

        $succes = "Vendeur modifié avec succès.";

        // Recharger les sites affectés
        $stmt = $pdo->prepare("SELECT site_id FROM vendeurs_sites WHERE vendeur_id = ?");
        $stmt->execute([$id]);
        $sites_vendeur = array_column($stmt->fetchAll(), 'site_id');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier vendeur</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Modifier vendeur</h1>

    <?php
    if ($succes) echo '<ul><li style="color:green;">' . $succes . '</li></ul>';
    if (!empty($erreurs)) {
        echo '<ul>';
        foreach ($erreurs as $e) echo '<li style="color:red;">' . $e . '</li>';
        echo '</ul>';
    }
    ?>

    <form method="post">
        <label>Nom *</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($vendeur['nom'] ?? '') ?>" required>
        <label>Email *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($vendeur['email'] ?? '') ?>" required>
        <label>Mot de passe (laisser vide pour ne pas changer)</label>
        <input type="password" name="mot_de_passe" placeholder="Nouveau mot de passe">
        <fieldset>
            <legend>Sites affectés</legend>
            <?php foreach ($sites as $site): ?>
                <label>
                    <input type="checkbox" name="sites[]" value="<?= $site['id'] ?>" <?= in_array($site['id'], $sites_vendeur) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($site['nom']) ?> (<?= htmlspecialchars($site['entite_nom']) ?>)
                </label><br>
            <?php endforeach; ?>
        </fieldset>
        <button type="submit" class="btn">Enregistrer</button>
    </form>

    <a href="vendeurs.php" style="display:block; margin-top: 20px;">Retour à la liste des vendeurs</a>
</div>
</body>
</html>
