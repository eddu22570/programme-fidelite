<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit;
    } else {
        $erreur = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Fidélité</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Connexion</h2>
    <?php if (!empty($erreur)) echo '<ul><li style="color:red">'.$erreur.'</li></ul>'; ?>
    <form method="post">
        Email : <input type="email" name="email" required><br>
        Mot de passe : <input type="password" name="mot_de_passe" required><br>
        <button type="submit">Se connecter</button>
    </form>
    <a href="reset_mdp.php">Mot de passe oublié ?</a><br>
    <a href="connexion_codebarre.php">Connexion par code-barres</a><br>
    <a href="inscription.php">Créer un compte</a>
</div>
</body>
</html>
