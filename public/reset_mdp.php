<?php
require_once __DIR__ . '/../includes/config.php';
session_start();

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Génère un token sécurisé et une date d'expiration (1h)
        $token = bin2hex(random_bytes(32));
        $expire = date('Y-m-d H:i:s', time() + 3600);

        // Stocke le token en base
        $stmt = $pdo->prepare("UPDATE utilisateurs SET reset_token = ?, reset_token_expire = ? WHERE id = ?");
        $stmt->execute([$token, $expire, $user['id']]);

        // Prépare le lien de réinitialisation
        $reset_link = "https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/reset_mdp_nouveau.php?token=$token";

        // Envoi email (ici version simple, à adapter selon ton serveur)
        $to = $email;
        $subject = "Réinitialisation de votre mot de passe";
        $message = "Bonjour,\n\nPour réinitialiser votre mot de passe, cliquez sur ce lien :\n$reset_link\n\nCe lien est valable 1 heure.";
        $headers = "From: no-reply@".$_SERVER['HTTP_HOST']."\r\n";
        // Utilise mail() ou une librairie (PHPMailer, etc.) en production
        mail($to, $subject, $message, $headers);

        $success = "Un email de réinitialisation vient d'être envoyé.";
    } else {
        $error = "Aucun compte trouvé avec cet email.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Réinitialiser mon mot de passe</h2>
    <?php if ($success): ?>
        <div style="color:green;"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div style="color:red;"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <label>Votre email :
            <input type="email" name="email" required>
        </label><br>
        <button type="submit">Recevoir le lien de réinitialisation</button>
    </form>
    <a href="connexion.php">Retour à la connexion</a>
</div>
</body>
</html>
