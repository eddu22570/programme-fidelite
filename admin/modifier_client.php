<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: clients.php');
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();

if (!$client) {
    echo "Client introuvable.";
    exit;
}

$succes = "";
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $points = (int)$_POST['points'];
    $code_barre = trim($_POST['code_barre']);

    if (empty($nom)) $erreurs[] = "Le nom ne peut pas être vide.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
    if (empty($code_barre)) $erreurs[] = "Le code-barres ne peut pas être vide.";

    // Vérifier unicité du code-barres (hors ce client)
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE code_barre = ? AND id != ?");
    $stmt->execute([$code_barre, $id]);
    if ($stmt->fetch()) {
        $erreurs[] = "Ce code-barres est déjà utilisé par un autre client.";
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, email=?, points=?, code_barre=? WHERE id=?");
        $stmt->execute([$nom, $email, $points, $code_barre, $id]);
        $succes = "Client modifié avec succès.";
        // Recharger les infos
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        $client = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier client</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Modifier client</h1>
    <?php
    if ($succes) echo '<ul><li style="color:green">'.$succes.'</li></ul>';
    if (!empty($erreurs)) {
        echo '<ul>';
        foreach ($erreurs as $e) echo '<li style="color:red">'.$e.'</li>';
        echo '</ul>';
    }
    ?>
    <form method="post">
        Nom : <input type="text" name="nom" value="<?= htmlspecialchars($client['nom']) ?>" required><br>
        Email : <input type="email" name="email" value="<?= htmlspecialchars($client['email']) ?>" required><br>
        Points : <input type="number" name="points" value="<?= (int)$client['points'] ?>" min="0" required><br>
        Code-barres : <input type="text" name="code_barre" value="<?= htmlspecialchars($client['code_barre']) ?>" required><br>
        <button type="submit" class="btn">Enregistrer</button>
    </form>
    <a href="clients.php">Retour</a>
</div>
</body>
</html>
