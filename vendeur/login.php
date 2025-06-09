<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $stmt = $pdo->prepare("SELECT * FROM vendeurs WHERE email = ? AND actif = 1");
    $stmt->execute([$email]);
    $vendeur = $stmt->fetch();

    if ($vendeur && password_verify($mot_de_passe, $vendeur['mot_de_passe'])) {
        $_SESSION['vendeur_id'] = $vendeur['id'];
        header('Location: index.php');
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
    <title>Connexion vendeur</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Connexion vendeur</h2>
    <?php if (!empty($erreur)) echo '<ul><li style="color:red">'.$erreur.'</li></ul>'; ?>
    <form method="post">
        Email : <input type="email" name="email" required><br>
        Mot de passe : <input type="password" name="mot_de_passe" required><br>
        <button type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>
