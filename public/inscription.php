<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();

$erreurs = [];

// Récupérer la liste des magasins depuis la table 'sites'
$magasins = $pdo->query("SELECT id, nom FROM sites ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $date_naissance = trim($_POST['date_naissance']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $code_barre = trim($_POST['code_barre']);
    $site_favori_id = !empty($_POST['site_favori_id']) ? (int)$_POST['site_favori_id'] : null;

    if (empty($nom)) $erreurs[] = "Le nom ne peut pas être vide.";
    if (empty($prenom)) $erreurs[] = "Le prénom ne peut pas être vide.";
    if (empty($date_naissance)) $erreurs[] = "La date de naissance est obligatoire.";
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_naissance)) $erreurs[] = "Format de date de naissance invalide.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
    if (strlen($mot_de_passe) < 6) $erreurs[] = "Mot de passe trop court (6 caractères min).";

    // Vérification unicité email
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) $erreurs[] = "Email déjà utilisé.";

    // Vérification unicité code-barre si saisi
    if (!empty($code_barre)) {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE code_barre = ?");
        $stmt->execute([$code_barre]);
        if ($stmt->fetch()) $erreurs[] = "Ce code-barres est déjà utilisé.";
    }

    // Vérification magasin préféré
    if (empty($site_favori_id)) $erreurs[] = "Veuillez sélectionner un magasin préféré.";

    if (empty($erreurs)) {
        $mdp_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO utilisateurs (nom, prenom, date_naissance, email, mot_de_passe, code_barre, site_favori_id, date_inscription)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $nom,
            $prenom,
            $date_naissance,
            $email,
            $mdp_hash,
            !empty($code_barre) ? $code_barre : null,
            $site_favori_id
        ]);
        $user_id = $pdo->lastInsertId();

        // Génération automatique du code-barre si non fourni OU si collision
        if (empty($code_barre)) {
            do {
                $gen_code = 'FID' . str_pad($user_id, 8, '0', STR_PAD_LEFT);
                // Vérifie l'unicité
                $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE code_barre = ?");
                $stmt->execute([$gen_code]);
                $existe = $stmt->fetch();
                if ($existe) {
                    // Si collision, ajoute un suffixe aléatoire
                    $gen_code = 'FID' . str_pad($user_id, 8, '0', STR_PAD_LEFT) . rand(10,99);
                }
            } while ($existe);

            $stmt = $pdo->prepare("UPDATE utilisateurs SET code_barre = ? WHERE id = ?");
            $stmt->execute([$gen_code, $user_id]);
        }
        header('Location: connexion.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Fidélité</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { background: #f5fafd; }
        .container {
            max-width: 430px;
            margin: 40px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(33,147,176,0.09);
            padding: 32px 28px 22px 28px;
        }
        h2 { color: #2193b0; margin-bottom: 18px; text-align: center; }
        label { display: block; margin-bottom: 10px; font-weight: 500; color: #176582; }
        input, select {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #b8e2f2;
            border-radius: 6px;
            margin-top: 4px;
            margin-bottom: 14px;
            font-size: 1em;
            background: #fafdff;
        }
        input[type="password"] { letter-spacing: 1px; }
        button[type="submit"] {
            background: linear-gradient(90deg,#2193b0,#6dd5ed);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px 0;
            width: 100%;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: linear-gradient(90deg,#176582,#2193b0);
        }
        .errors {
            background: #ffeaea;
            color: #c0392b;
            border-radius: 7px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 1em;
        }
        .pwd-strength {
            height: 8px;
            border-radius: 5px;
            margin-bottom: 6px;
            background: #e0e0e0;
            overflow: hidden;
        }
        .pwd-strength-bar {
            height: 100%;
            width: 0;
            border-radius: 5px;
            transition: width 0.3s, background 0.3s;
        }
        .pwd-strength-txt {
            font-size: 0.93em;
            margin-bottom: 10px;
            color: #888;
            min-height: 18px;
        }
        .link-login {
            display: block;
            margin-top: 22px;
            text-align: center;
            color: #2193b0;
            text-decoration: none;
        }
        .link-login:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>Créer un compte fidélité</h2>
    <?php
    if (!empty($erreurs)) {
        echo '<div class="errors"><ul>';
        foreach ($erreurs as $e) echo "<li>$e</li>";
        echo '</ul></div>';
    }
    ?>
    <form method="post" autocomplete="off">
        <label>Nom
            <input type="text" name="nom" required value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
        </label>
        <label>Prénom
            <input type="text" name="prenom" required value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>">
        </label>
        <label>Date de naissance
            <input type="date" name="date_naissance" required value="<?= isset($_POST['date_naissance']) ? htmlspecialchars($_POST['date_naissance']) : '' ?>">
        </label>
        <label>Email
            <input type="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        </label>
        <label>Mot de passe
            <input type="password" name="mot_de_passe" id="mot_de_passe" required autocomplete="new-password">
            <div class="pwd-strength" id="pwd-strength"><div class="pwd-strength-bar" id="pwd-strength-bar"></div></div>
            <div class="pwd-strength-txt" id="pwd-strength-txt"></div>
        </label>
        <label>Code-barres (si carte physique, sinon laisser vide)
            <input type="text" name="code_barre" value="<?= isset($_POST['code_barre']) ? htmlspecialchars($_POST['code_barre']) : '' ?>">
        </label>
        <label>Magasin préféré
            <select name="site_favori_id" required>
                <option value="">-- Choisissez --</option>
                <?php foreach ($magasins as $mag): ?>
                    <option value="<?= $mag['id'] ?>" <?= (isset($_POST['site_favori_id']) && $_POST['site_favori_id'] == $mag['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mag['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">S'inscrire</button>
    </form>
    <a class="link-login" href="connexion.php">Déjà inscrit ? Se connecter</a>
</div>
<script>
function checkPwdStrength(pwd) {
    let score = 0;
    if (pwd.length >= 6) score++;
    if (pwd.match(/[a-z]/)) score++;
    if (pwd.match(/[A-Z]/)) score++;
    if (pwd.match(/[0-9]/)) score++;
    if (pwd.match(/[^a-zA-Z0-9]/)) score++;
    return score;
}
function getPwdStrengthText(score) {
    switch(score) {
        case 0: case 1: return "Trop faible";
        case 2: return "Faible";
        case 3: return "Moyen";
        case 4: return "Bon";
        case 5: return "Excellente sécurité";
    }
}
function getPwdStrengthColor(score) {
    switch(score) {
        case 0: case 1: return "#e74c3c";
        case 2: return "#e67e22";
        case 3: return "#f1c40f";
        case 4: return "#27ae60";
        case 5: return "#2193b0";
    }
}
const pwdInput = document.getElementById('mot_de_passe');
const bar = document.getElementById('pwd-strength-bar');
const txt = document.getElementById('pwd-strength-txt');
pwdInput.addEventListener('input', function() {
    const val = pwdInput.value;
    const score = checkPwdStrength(val);
    bar.style.width = (score * 20) + '%';
    bar.style.background = getPwdStrengthColor(score);
    txt.textContent = getPwdStrengthText(score);
});
</script>
</body>
</html>
