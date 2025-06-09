<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['vendeur_id'])) {
    header('Location: login.php');
    exit;
}

// Récupération des infos vendeur
$stmt = $pdo->prepare("SELECT nom, email FROM vendeurs WHERE id = ?");
$stmt->execute([$_SESSION['vendeur_id']]);
$vendeur = $stmt->fetch();

$erreurs = [];
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien_mdp = $_POST['ancien_mdp'];
    $nouveau_mdp = $_POST['nouveau_mdp'];
    $confirmer_mdp = $_POST['confirmer_mdp'];

    // Vérification de l'ancien mot de passe
    $stmt = $pdo->prepare("SELECT mot_de_passe FROM vendeurs WHERE id = ?");
    $stmt->execute([$_SESSION['vendeur_id']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($ancien_mdp, $hash)) {
        $erreurs[] = "Ancien mot de passe incorrect.";
    } elseif (strlen($nouveau_mdp) < 6) {
        $erreurs[] = "Le nouveau mot de passe doit faire au moins 6 caractères.";
    } elseif ($nouveau_mdp !== $confirmer_mdp) {
        $erreurs[] = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
    } else {
        // Mise à jour du mot de passe
        $new_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE vendeurs SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$new_hash, $_SESSION['vendeur_id']]);
        $succes = "Mot de passe modifié avec succès !";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profil vendeur</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <div style="text-align:right;">
        <a href="index.php" class="btn">Espace vendeur</a>
        <a href="deconnexion.php" class="btn">Déconnexion</a>
    </div>
    <h1>Mon profil vendeur</h1>
    <p><strong>Nom :</strong> <?= htmlspecialchars($vendeur['nom']) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($vendeur['email']) ?></p>

    <h3>Modifier mon mot de passe</h3>
    <?php
    if ($succes) echo '<ul><li style="color:green">'.$succes.'</li></ul>';
    if (!empty($erreurs)) {
        echo '<ul>';
        foreach ($erreurs as $e) echo '<li style="color:red">'.$e.'</li>';
        echo '</ul>';
    }
    ?>
    <form method="post">
        <label>Ancien mot de passe :</label>
        <input type="password" name="ancien_mdp" required><br>
        <label>Nouveau mot de passe :</label>
        <input type="password" name="nouveau_mdp" required><br>
        <label>Confirmer le nouveau mot de passe :</label>
        <input type="password" name="confirmer_mdp" required><br>
        <button type="submit" class="btn">Changer le mot de passe</button>
    </form>
</div>
</body>
</html>
