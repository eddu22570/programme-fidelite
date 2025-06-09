<?php
// public/cgu.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Conditions Générales d'Utilisation</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .legal-container {
            max-width: 700px;
            margin: 48px auto 32px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(33,147,176,0.10);
            padding: 36px 32px 32px 32px;
        }
        .legal-container h1 {
            color: #217dbb;
            margin-bottom: 18px;
            text-align: center;
        }
        .legal-section {
            margin-bottom: 28px;
            padding: 18px 20px;
            background: #fafdff;
            border-radius: 12px;
            box-shadow: 0 1px 6px rgba(33,147,176,0.06);
        }
        .legal-section h2 {
            color: #176582;
            margin-bottom: 10px;
            font-size: 1.16em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .legal-section p {
            color: #444;
            font-size: 1.09em;
            margin: 0;
        }
        .btn-retour {
            margin: 36px auto 0 auto;
            display: block;
            width: fit-content;
            background: #2193b0;
            color: #fff;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 1.07em;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.18s;
            box-shadow: 0 2px 8px rgba(33,147,176,0.07);
        }
        .btn-retour:hover {
            background: #176582;
        }
        @media (max-width: 800px) {
            .legal-container {
                padding: 12px 6px 18px 6px;
            }
        }
    </style>
</head>
<body>
<div class="legal-container">
    <h1>Conditions Générales d’Utilisation</h1>

    <div class="legal-section">
        <h2><span>💻</span> Logiciel Open Source</h2>
        <p>
            Ce site fonctionne grâce à un logiciel <strong>open source</strong> mis à disposition de la communauté.<br>
            Le code source a été fourni grâce à <a href="https://github.com/eddu22570" target="_blank" style="color:#217dbb;">eddu22570 sur GitHub</a>.<br>
            Le code source est librement accessible, modifiable et réutilisable dans le respect de la licence associée.
        </p>
    </div>

    <div class="legal-section">
        <h2><span>📄</span> 1. Objet</h2>
        <p>Les présentes conditions générales d’utilisation (CGU) ont pour objet de définir les modalités d’accès et d’utilisation du site et du programme de fidélité proposés par EXEMPLE SAS.</p>
    </div>
    <div class="legal-section">
        <h2><span>🔑</span> 2. Accès au service</h2>
        <p>L’accès au service est réservé aux clients disposant d’un compte personnel. L’utilisateur s’engage à fournir des informations exactes lors de son inscription.</p>
    </div>
    <div class="legal-section">
        <h2><span>🎁</span> 3. Fonctionnement du programme de fidélité</h2>
        <p>Le programme permet de cumuler des points lors d’achats ou d’actions spécifiques, échangeables contre des récompenses. Les modalités d’attribution et d’utilisation des points sont détaillées sur le site.</p>
    </div>
    <div class="legal-section">
        <h2><span>⚖️</span> 4. Responsabilités</h2>
        <p>EXEMPLE SAS s’efforce d’assurer l’exactitude des informations diffusées sur le site mais ne saurait être tenue responsable des erreurs ou omissions.</p>
    </div>
    <div class="legal-section">
        <h2><span>🔒</span> 5. Données personnelles</h2>
        <p>Les données collectées sont utilisées pour la gestion du programme de fidélité et ne sont pas transmises à des tiers sans consentement. Vous disposez d’un droit d’accès, de rectification et de suppression.</p>
    </div>
    <div class="legal-section">
        <h2><span>🔄</span> 6. Modification des CGU</h2>
        <p>EXEMPLE SAS se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés de toute modification sur le site.</p>
    </div>
    <div class="legal-section">
        <h2><span>⚖️</span> 7. Loi applicable</h2>
        <p>Les présentes CGU sont soumises à la loi française. Tout litige sera de la compétence exclusive des tribunaux français.</p>
    </div>

    <a href="index.php" class="btn-retour">Retour à l’accueil</a>
</div>
</body>
</html>
