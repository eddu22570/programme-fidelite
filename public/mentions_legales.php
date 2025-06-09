<?php
// public/mentions_legales.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mentions légales</title>
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
    <h1>Mentions légales</h1>

    <div class="legal-section">
        <h2><span>🏢</span> Éditeur du site</h2>
        <p>
            Société EXEMPLE SAS<br>
            123 rue de la République<br>
            75000 Paris, France<br>
            Tél : 01 23 45 67 89<br>
            Email : contact@exemple.com<br>
            SIRET : 123 456 789 00012<br>
            Directeur de la publication : M. Jean Dupont
        </p>
    </div>

    <div class="legal-section">
        <h2><span>🖥️</span> Hébergement</h2>
        <p>
            OVH SAS<br>
            2 rue Kellermann<br>
            59100 Roubaix, France<br>
            Tél : 09 72 10 10 07<br>
            www.ovh.com
        </p>
    </div>

    <div class="legal-section">
        <h2><span>💻</span> Logiciel Open Source</h2>
        <p>
            Ce site fonctionne grâce à un logiciel <strong>open source</strong> mis à disposition de la communauté.<br>
            Le code source a été fourni grâce à <a href="https://github.com/eddu22570" target="_blank" style="color:#217dbb;">eddu22570 sur GitHub</a>.<br>
            Le code source est librement accessible, modifiable et réutilisable dans le respect de la licence associée.<br>
            Pour plus d’informations ou pour accéder au code, contactez l’éditeur du site ou consultez le dépôt officiel.
        </p>
    </div>

    <div class="legal-section">
        <h2><span>©️</span> Propriété intellectuelle</h2>
        <p>
            Le contenu de ce site (textes, images, graphismes, logo, etc.) est protégé par le droit d’auteur et reste la propriété exclusive de la société EXEMPLE SAS, sauf indication contraire pour les éléments open source.
        </p>
    </div>

    <div class="legal-section">
        <h2><span>🔒</span> Protection des données personnelles</h2>
        <p>
            Conformément à la loi « Informatique et Libertés » et au RGPD, vous disposez d’un droit d’accès, de rectification et de suppression de vos données. Pour exercer ce droit, contactez-nous à l’adresse ci-dessus.
        </p>
    </div>

    <a href="index.php" class="btn-retour">Retour à l’accueil</a>
</div>
</body>
</html>
