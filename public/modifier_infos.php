<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: connexion.php');

// Récup infos actuelles
$stmt = $pdo->prepare("SELECT nom, email, code_barre FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $code_barre = trim($_POST['code_barre']);

    if (empty($nom)) $erreurs[] = "Le nom ne peut pas être vide.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";

    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) $erreurs[] = "Cet email est déjà utilisé par un autre compte.";

    if (!empty($code_barre)) {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE code_barre = ? AND id != ?");
        $stmt->execute([$code_barre, $_SESSION['user_id']]);
        if ($stmt->fetch()) $erreurs[] = "Ce code-barres est déjà utilisé.";
    } else {
        $code_barre = $user['code_barre'];
        if (empty($code_barre)) {
            $code_barre = 'FID' . str_pad($_SESSION['user_id'], 8, '0', STR_PAD_LEFT);
        }
    }
    if (!empty($mot_de_passe) && strlen($mot_de_passe) < 6) $erreurs[] = "Mot de passe trop court (6 caractères min).";

    if (empty($erreurs)) {
        if (!empty($mot_de_passe)) {
            $mdp_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ?, mot_de_passe = ?, code_barre = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $mdp_hash, $code_barre, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ?, code_barre = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $code_barre, $_SESSION['user_id']]);
        }
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mes informations - Fidélité</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>Modifier mes informations</h2>
    <?php
    if (!empty($erreurs)) {
        echo '<ul>';
        foreach ($erreurs as $e) echo "<li>$e</li>";
        echo '</ul>';
    }
    ?>
    <form method="post">
        Nom : <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required><br>
        Email : <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
        Nouveau mot de passe : <input type="password" name="mot_de_passe" placeholder="Laisser vide pour ne pas changer"><br>
        Code-barres (si carte physique, sinon laisser vide) : <input type="text" name="code_barre" value="<?= htmlspecialchars($user['code_barre']) ?>"><br>
        <button type="submit">Enregistrer</button>
    </form>
    <a href="dashboard.php">Retour</a>
</div>
</body>
</html>
