<?php
require_once __DIR__ . '/../includes/config.php';
session_start();

$token = $_GET['token'] ?? '';
$erreur = '';
$success = '';
$user = null;

// Vérifie le token
if ($token) {
    $stmt = $pdo->prepare("SELECT id, reset_token_expire FROM utilisateurs WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user || strtotime($user['reset_token_expire']) < time()) {
        $erreur = "Lien invalide ou expiré.";
        $user = null;
    }
} else {
    $erreur = "Lien invalide.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $mdp1 = $_POST['mdp1'];
    $mdp2 = $_POST['mdp2'];
    if (strlen($mdp1) < 6) {
        $erreur = "Le mot de passe doit faire au moins 6 caractères.";
    } elseif ($mdp1 !== $mdp2) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        $mdp_hash = password_hash($mdp1, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ?, reset_token = NULL, reset_token_expire = NULL WHERE id = ?");
        $stmt->execute([$mdp_hash, $user['id']]);
        $success = "Votre mot de passe a bien été réinitialisé. <a href='connexion.php'>Se connecter</a>";
        $user = null;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau mot de passe</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Définir un nouveau mot de passe</h2>
    <?php if ($success): ?>
        <div style="color:green;"><?= $success ?></div>
    <?php elseif ($erreur): ?>
        <div style="color:red;"><?= $erreur ?></div>
    <?php endif; ?>
    <?php if ($user): ?>
    <form method="post">
        <label>Nouveau mot de passe :
            <input type="password" name="mdp1" required>
        </label><br>
        <label>Confirmer le mot de passe :
            <input type="password" name="mdp2" required>
        </label><br>
        <button type="submit">Réinitialiser</button>
    </form>
    <?php endif; ?>
    <a href="connexion.php">Retour à la connexion</a>
</div>
</body>
</html>
