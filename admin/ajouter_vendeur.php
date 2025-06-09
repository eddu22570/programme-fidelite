<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$erreurs = [];
$succes = "";

// Récupérer la liste des sites pour le menu déroulant
$sites = $pdo->query("SELECT id, nom FROM sites ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $site_id = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;

    if (empty($nom)) $erreurs[] = "Le nom ne peut pas être vide.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
    if (strlen($mot_de_passe) < 6) $erreurs[] = "Mot de passe trop court (6 caractères min).";
    if (!$site_id) $erreurs[] = "Veuillez sélectionner un site.";

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM vendeurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) $erreurs[] = "Email déjà utilisé.";

    if (empty($erreurs)) {
        $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO vendeurs (nom, email, mot_de_passe, site_id, actif) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$nom, $email, $hash, $site_id]);
        $succes = "Vendeur ajouté avec succès.";

        // Récupérer l'id du vendeur ajouté pour le log
        $vendeur_id = $pdo->lastInsertId();
        ajouter_log($pdo, 'admin', $_SESSION['admin_id'], 'ajout_vendeur', $vendeur_id, "Ajout du vendeur $nom ($email), site_id=$site_id");
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter vendeur</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Ajouter un vendeur</h1>
    <?php
    if ($succes) echo '<ul><li style="color:green">'.$succes.'</li></ul>';
    if (!empty($erreurs)) {
        echo '<ul>';
        foreach ($erreurs as $e) echo '<li style="color:red">'.$e.'</li>';
        echo '</ul>';
    }
    ?>
    <form method="post">
        Nom : <input type="text" name="nom" value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>" required><br>
        Email : <input type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required><br>
        Mot de passe : <input type="password" name="mot_de_passe" required><br>
        Site :
        <select name="site_id" required>
            <option value="">-- Sélectionner un site --</option>
            <?php foreach ($sites as $site): ?>
                <option value="<?= (int)$site['id'] ?>" <?= (isset($_POST['site_id']) && $_POST['site_id'] == $site['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($site['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <button type="submit" class="btn">Ajouter</button>
    </form>
    <a href="vendeurs.php">Retour</a>
</div>
</body>
</html>
