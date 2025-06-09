<?php
require '../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_barre = trim($_POST['code_barre']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Recherche de l'utilisateur avec ce code-barres
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE code_barre = ?");
    $stmt->execute([$code_barre]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit;
    } else {
        $erreur = "Code-barres ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion par code-barres - Fidélité</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Connexion par code-barres</h2>
    <?php if (!empty($erreur)) echo '<ul><li style="color:red">'.$erreur.'</li></ul>'; ?>
    <form method="post">
        <label>Code-barres :</label>
        <input type="text" name="code_barre" required><br>
        <label>Mot de passe :</label>
        <input type="password" name="mot_de_passe" required><br>
        <button type="submit">Se connecter</button>
    </form>
    <a href="connexion.php">Connexion classique</a>
</div>
</body>
</html>