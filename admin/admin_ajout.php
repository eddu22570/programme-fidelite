<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mdp = $_POST['mdp'];

    if (empty($nom)) $erreurs[] = "Nom requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
    if (strlen($mdp) < 6) $erreurs[] = "Mot de passe trop court (6 caractères min).";

    // Email unique
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) $erreurs[] = "Email déjà utilisé.";

    if (empty($erreurs)) {
        $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (nom, email, mot_de_passe) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $email, $mdp_hash]);

        // Ajout dans les logs
        ajouter_log($pdo, 'admin', $_SESSION['admin_id'], 'ajout_admin', $email, "Ajout de l'admin $email");

        header('Location: admins.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Ajouter un administrateur</h2>
    <?php if ($erreurs): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $e) echo "<div>$e</div>"; ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Mot de passe</label>
            <input type="password" name="mdp" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Créer l'admin</button>
        <a href="admins.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
</body>
</html>
