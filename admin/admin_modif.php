<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admins.php');
    exit;
}

$id = (int)$_GET['id'];

// Récupérer l'admin à modifier
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$id]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: admins.php');
    exit;
}

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);

    if (empty($nom)) $erreurs[] = "Nom requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";

    // Email unique (hors cet admin)
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) $erreurs[] = "Email déjà utilisé.";

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("UPDATE admins SET nom = ?, email = ? WHERE id = ?");
        $stmt->execute([$nom, $email, $id]);

        // Ajout dans les logs
        ajouter_log($pdo, 'admin', $_SESSION['admin_id'], 'modification_admin', $email, "Modification de l'admin $email (id $id)");

        header('Location: admins.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Modifier un administrateur</h2>
    <?php if ($erreurs): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $e) echo "<div>$e</div>"; ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($admin['nom']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="admins.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
</body>
</html>
