<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Récupération des sites et de leur entité associée
$stmt = $pdo->query("
    SELECT s.nom AS site_nom, s.adresse, e.nom AS entite_nom
    FROM sites s
    LEFT JOIN entites e ON s.entite_id = e.id
    ORDER BY s.nom ASC
");
$liste_sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si connecté, récupération du nom de l'utilisateur
$user_nom = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT nom FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $user_nom = $user['nom'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue sur le programme de fidélité</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .top-nav {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 24px 32px 0 0;
        }
        .top-nav .btn, .top-nav .user-info {
            margin-left: 12px;
            padding: 10px 20px;
            border-radius: 6px;
            background: #2193b0;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
            border: none;
            display: inline-block;
        }
        .top-nav .btn:hover {
            background: #176582;
        }
        .top-nav .user-info {
            background: #176582;
            cursor: default;
        }
        .top-nav .btn-deco {
            background: #c0392b;
        }
        .top-nav .btn-deco:hover {
            background: #96281B;
        }
        .main-content {
            max-width: 600px;
            margin: 60px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(33,147,176,0.10);
            padding: 40px 36px 36px 36px;
            text-align: center;
        }
        .main-content h1 {
            color: #217dbb;
            margin-bottom: 18px;
        }
        .main-content h2 {
            color: #176582;
            margin-bottom: 12px;
            font-size: 1.3em;
        }
        .main-content p {
            color: #444;
            font-size: 1.13em;
            margin-bottom: 18px;
        }
        .features-list {
            text-align: left;
            margin: 30px 0 0 0;
            padding-left: 0;
        }
        .features-list li {
            margin-bottom: 12px;
            font-size: 1.08em;
            color: #217dbb;
            list-style: none;
            position: relative;
            padding-left: 24px;
        }
        .features-list li::before {
            content: "★";
            color: #f39c12;
            position: absolute;
            left: 0;
            top: 0;
        }
        .liste-sites {
            margin: 48px auto 0 auto;
            max-width: 600px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(33,147,176,0.08);
            padding: 32px 28px 24px 28px;
        }
        .liste-sites h2 {
            color: #217dbb;
            margin-bottom: 18px;
            text-align: center;
        }
        .liste-sites ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .liste-sites li {
            margin-bottom: 14px;
            padding: 12px 14px;
            background: #f5fafd;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(33,147,176,0.07);
        }
        .liste-sites strong {
            color: #176582;
        }
        .liste-sites .entite {
            color: #217dbb;
            font-size: 1em;
        }
        .liste-sites span {
            color: #666;
            font-size: 0.97em;
        }
        .footer-legal {
            margin: 48px 0 0 0;
            text-align: center;
        }
        .footer-legal .btn-legal {
            margin: 0 10px;
            padding: 10px 24px;
            border-radius: 6px;
            background: #eaf6fb;
            color: #217dbb;
            text-decoration: none;
            font-weight: 500;
            border: 1px solid #b3e0f5;
            transition: background 0.2s, color 0.2s;
            display: inline-block;
        }
        .footer-legal .btn-legal:hover {
            background: #2193b0;
            color: #fff;
            border-color: #2193b0;
        }
        @media (max-width: 700px) {
            .main-content, .liste-sites {
                padding: 18px 6px 14px 6px;
            }
            .top-nav {
                padding: 18px 8px 0 0;
            }
            .footer-legal {
                margin: 32px 0 0 0;
            }
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <?php if ($user_nom): ?>
            <span class="user-info">👤 <?= htmlspecialchars($user_nom) ?></span>
            <a href="dashboard.php" class="btn">Mon espace</a>
            <a href="deconnexion.php" class="btn btn-deco">Déconnexion</a>
        <?php else: ?>
            <a href="connexion.php" class="btn">Connexion par email</a>
            <a href="connexion_codebarre.php" class="btn">Connexion par carte</a>
            <a href="inscription.php" class="btn" style="background:#f39c12;">Inscription</a>
        <?php endif; ?>
    </div>
    <div class="main-content">
        <h1>Programme de Fidélité</h1>
        <h2>Récompensez votre fidélité et profitez d’avantages exclusifs !</h2>
        <p>
            Découvrez notre programme de fidélité : cumulez des points à chaque achat, accédez à des offres personnalisées et échangez vos points contre des cadeaux ou des remises dans vos boutiques préférées.
        </p>
        <ul class="features-list">
            <li>1€ dépensé = 1 point fidélité</li>
            <li>Offres exclusives réservées aux membres</li>
            <li>Récompenses personnalisées et cadeaux</li>
            <li>Suivi de votre solde de points en temps réel</li>
            <li>Carte physique ou virtuelle disponible</li>
            <li>Choisissez votre magasin favori</li>
        </ul>
        <p style="margin-top:28px;">
            <strong>Rejoignez-nous dès maintenant et commencez à cumuler des points !</strong>
        </p>
    </div>
    <div class="liste-sites">
        <h2>Où profiter de votre fidélité ?</h2>
        <?php if (!empty($liste_sites)): ?>
            <ul>
                <?php foreach ($liste_sites as $site): ?>
                    <li>
                        <strong><?= htmlspecialchars($site['site_nom']) ?></strong>
                        <?php if (!empty($site['entite_nom'])): ?>
                            <br><span class="entite"><?= htmlspecialchars($site['entite_nom']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($site['adresse'])): ?>
                            <br><span><?= htmlspecialchars($site['adresse']) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div style="color:#c0392b;text-align:center;">Aucun site partenaire n’est référencé pour le moment.</div>
        <?php endif; ?>
    </div>
    <div class="footer-legal">
        <a href="mentions_legales.php" class="btn-legal">Mentions légales</a>
        <a href="cgu.php" class="btn-legal">Conditions Générales d’Utilisation</a>
    </div>
</body>
</html>
