<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

if (!isset($_GET['id'])) { header('Location: entites.php'); exit; }
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM entites WHERE id=?");
$stmt->execute([$id]);
$entite = $stmt->fetch();
if (!$entite) { echo "Entité introuvable."; exit; }

$succes = "";
$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $desc = trim($_POST['description']);
    if (empty($nom)) $erreurs[] = "Le nom est obligatoire.";
    if (empty($erreurs)) {
        $stmt = $pdo->prepare("UPDATE entites SET nom=?, description=? WHERE id=?");
        $stmt->execute([$nom, $desc, $id]);
        $succes = "Entité modifiée.";
        $stmt = $pdo->prepare("SELECT * FROM entites WHERE id=?");
        $stmt->execute([$id]);
        $entite = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier entité</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Modifier entité</h1>
    <?php
    if ($succes) echo '<ul><li style="color:green">'.$succes.'</li></ul>';
    if (!empty($erreurs)) {
        echo '<ul>';
        foreach ($erreurs as $e) echo '<li style="color:red">'.$e.'</li>';
        echo '</ul>';
    }
    ?>
    <form method="post">
        Nom : <input type="text" name="nom" value="<?= htmlspecialchars($entite['nom']) ?>" required><br>
        Description : <input type="text" name="description" value="<?= htmlspecialchars($entite['description']) ?>"><br>
        <button type="submit" class="btn">Enregistrer</button>
    </form>
    <a href="entites.php">Retour</a>
</div>
</body>
</html>
