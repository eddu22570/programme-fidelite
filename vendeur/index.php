<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';

session_start();
if (!isset($_SESSION['vendeur_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer le site du vendeur connecté
$stmt = $pdo->prepare("SELECT site_id, nom FROM vendeurs WHERE id = ?");
$stmt->execute([$_SESSION['vendeur_id']]);
$vendeur = $stmt->fetch();
if (!$vendeur) {
    die("Vendeur introuvable.");
}
$vendeur_site_id = (int)$vendeur['site_id'];

// Statistiques rapides
$nb_clients = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$visites_today = $pdo->prepare("SELECT COUNT(*) FROM client_visites WHERE site_id = ? AND DATE(date_visite) = CURDATE()");
$visites_today->execute([$vendeur_site_id]);
$visites_today = $visites_today->fetchColumn();

$client = null;
$message_points = '';
$nb_visites = 0;
$nb_visites_site = 0;
$magasin_prefere_nom = null;

// Recherche client
if (!empty($_POST['recherche_client'])) {
    $recherche = trim($_POST['recherche_client']);
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE code_barre = ? OR email = ?");
    $stmt->execute([$recherche, $recherche]);
    $client = $stmt->fetch();

    if ($client) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_visites WHERE client_id = ?");
        $stmt->execute([$client['id']]);
        $nb_visites = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_visites WHERE client_id = ? AND site_id = ?");
        $stmt->execute([$client['id'], $vendeur_site_id]);
        $nb_visites_site = $stmt->fetchColumn();

        // Récupérer le nom du magasin préféré si renseigné
        if (!empty($client['site_favori_id'])) {
            $stmt = $pdo->prepare("SELECT nom FROM entites WHERE id = ?");
            $stmt->execute([$client['site_favori_id']]);
            $magasin_prefere_nom = $stmt->fetchColumn();
        }
    }
}

// Ajout/retrait de points
if (!empty($_POST['action_points']) && !empty($_POST['client_id'])) {
    $client_id = (int)$_POST['client_id'];
    if ($_POST['action_points'] === 'ajouter' && isset($_POST['points_ajout'])) {
        $points = (int)$_POST['points_ajout'];
        if ($points > 0) {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET points = points + ? WHERE id = ?");
            $stmt->execute([$points, $client_id]);

            // Enregistrer la visite
            $stmt = $pdo->prepare("INSERT INTO client_visites (client_id, site_id, date_visite) VALUES (?, ?, NOW())");
            $stmt->execute([$client_id, $vendeur_site_id]);

            // Log
            ajouter_log($pdo, 'vendeur', $_SESSION['vendeur_id'], 'ajout_points', $client_id, "$points points ajoutés");

            $message_points = "<span style='color:green;'>$points points ajoutés.</span>";
        } else {
            $message_points = "<span style='color:red;'>Le nombre de points à ajouter doit être strictement positif.</span>";
        }
    }
    if ($_POST['action_points'] === 'retirer' && isset($_POST['points_retrait'])) {
        $points = (int)$_POST['points_retrait'];
        if ($points > 0) {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET points = GREATEST(points - ?, 0) WHERE id = ?");
            $stmt->execute([$points, $client_id]);

            // Enregistrer la visite
            $stmt = $pdo->prepare("INSERT INTO client_visites (client_id, site_id, date_visite) VALUES (?, ?, NOW())");
            $stmt->execute([$client_id, $vendeur_site_id]);

            // Log
            ajouter_log($pdo, 'vendeur', $_SESSION['vendeur_id'], 'retrait_points', $client_id, "$points points retirés");

            $message_points = "<span style='color:orange;'>$points points retirés.</span>";
        } else {
            $message_points = "<span style='color:red;'>Le nombre de points à retirer doit être strictement positif.</span>";
        }
    }

    // Recharger les infos client après modification
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();

    if ($client) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_visites WHERE client_id = ?");
        $stmt->execute([$client['id']]);
        $nb_visites = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_visites WHERE client_id = ? AND site_id = ?");
        $stmt->execute([$client['id'], $vendeur_site_id]);
        $nb_visites_site = $stmt->fetchColumn();

        // Récupérer le nom du magasin préféré si renseigné
        if (!empty($client['site_favori_id'])) {
            $stmt = $pdo->prepare("SELECT nom FROM entites WHERE id = ?");
            $stmt->execute([$client['site_favori_id']]);
            $magasin_prefere_nom = $stmt->fetchColumn();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Vendeur</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .nav-main {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .nav-main .btn {
            white-space: nowrap;
            padding: 10px 22px;
            font-size: 1em;
            margin-top: 0;
        }
        .btn-deco {
            background: #c0392b !important;
        }
        .btn-deco:hover {
            background: #96281B !important;
        }
        .dashboard-cards {
            display: flex;
            gap: 18px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        .dashboard-card {
            flex: 1 1 180px;
            background: #f5fafd;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(33,147,176,0.09);
            padding: 22px 18px 18px 18px;
            text-align: center;
            min-width: 160px;
            margin-bottom: 12px;
        }
        .dashboard-card h3 {
            margin: 0 0 8px 0;
            color: #2193b0;
            font-size: 1.1em;
            font-weight: 600;
        }
        .dashboard-card .stat {
            font-size: 2.1em;
            font-weight: bold;
            color: #176582;
            margin-bottom: 4px;
        }
        .dashboard-card .icon {
            font-size: 1.7em;
            margin-bottom: 8px;
            display: block;
        }
        .client-result {
            background: #f5fafd;
            padding: 22px 18px;
            border-radius: 12px;
            margin-top: 24px;
            box-shadow: 0 2px 8px rgba(33,147,176,0.08);
        }
        .client-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .client-header .icon {
            font-size: 1.5em;
        }
        .client-badges {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .badge {
            background: #e0f7e9;
            color: #2193b0;
            padding: 4px 14px;
            border-radius: 12px;
            font-size: 0.97em;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .badge.orange {
            background: #fff2e0;
            color: #e67e22;
        }
        .badge.blue {
            background: #eaf6fb;
            color: #217dbb;
        }
        .points-section {
            margin-top: 18px;
            padding: 14px 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .points-section.ajout {
            background: #e0f7e9;
        }
        .points-section.retrait {
            background: #fbeaea;
        }
        .points-section h4 {
            margin: 0 0 10px 0;
            font-size: 1.08em;
            color: #2193b0;
        }
        .message-points {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        @media (max-width: 900px) {
            .dashboard-cards {
                flex-direction: column;
                gap: 12px;
            }
        }
        @media (max-width: 700px) {
            .nav-main {
                flex-direction: column;
                align-items: stretch;
                gap: 0;
            }
            .nav-main .btn {
                width: 100%;
                margin-bottom: 8px;
            }
            .dashboard-cards {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-main">
        <span style="font-weight:bold; color:#2193b0; margin-right:16px;">
            <?= htmlspecialchars($vendeur['nom']) ?> (vendeur)
        </span>
        <a href="deconnexion.php" class="btn btn-deco">Se déconnecter</a>
    </div>

    <h1 style="margin-bottom:24px;">Tableau de bord du magasin</h1>

    <div class="dashboard-cards">
        <div class="dashboard-card">
            <span class="icon">🏬</span>
            <h3>Site</h3>
            <div class="stat"><?= $vendeur_site_id ?></div>
        </div>
        <div class="dashboard-card">
            <span class="icon">👥</span>
            <h3>Clients inscrits</h3>
            <div class="stat"><?= (int)$nb_clients ?></div>
        </div>
        <div class="dashboard-card">
            <span class="icon">🚶</span>
            <h3>Visites aujourd’hui</h3>
            <div class="stat"><?= (int)$visites_today ?></div>
        </div>
    </div>

    <!-- Formulaire de recherche SANS scan -->
    <form method="post" style="margin-bottom:24px; display:flex; align-items:center; gap:10px;">
        <label for="recherche_client"><strong>🔎 Rechercher un client (code-barres ou email) :</strong></label>
        <input type="text" name="recherche_client" id="recherche_client" placeholder="Code-barres ou email" required style="width:220px; padding:8px;">
        <button type="submit" style="padding:8px 16px;">Rechercher</button>
    </form>

    <?php if ($client): ?>
        <div class="client-result">
            <div class="client-header">
                <span class="icon">👤</span>
                <span style="font-size:1.15em;font-weight:bold;">
                    <?= htmlspecialchars(trim($client['prenom'] . ' ' . $client['nom'])) ?>
                </span>
            </div>
            <div class="client-badges">
                <span class="badge blue">🆔 Code-barres : <?= htmlspecialchars($client['code_barre']) ?></span>
            </div>
            <div style="margin:14px 0;">
                <img src="../public/barcode.php?code=<?= urlencode($client['code_barre']) ?>" alt="Code-barres client" style="background:#fff; padding:8px 16px; border-radius:6px; box-shadow:0 1px 6px rgba(33,147,176,0.07); display:block; margin:auto;">
            </div>
            <div class="client-badges">
                <span class="badge">⭐ Points : <?= (int)$client['points'] ?></span>
                <span class="badge">👣 Visites (total) : <?= (int)$nb_visites ?></span>
                <span class="badge">🏬 Visites ici : <?= (int)$nb_visites_site ?></span>
            </div>
            <?php if ($magasin_prefere_nom): ?>
                <div class="client-badges">
                    <span class="badge orange">🏪 Magasin préféré : <?= htmlspecialchars($magasin_prefere_nom) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($message_points): ?>
                <span class="message-points"><?= $message_points ?></span>
            <?php endif; ?>

            <div class="points-section ajout">
                <h4>➕ Ajouter des points</h4>
                <form method="post" style="display:flex;align-items:center;gap:10px;">
                    <input type="hidden" name="client_id" value="<?= (int)$client['id'] ?>">
                    <input type="number" name="points_ajout" min="1" value="1" required style="width:80px;">
                    <button type="submit" name="action_points" value="ajouter">Ajouter</button>
                </form>
            </div>

            <div class="points-section retrait">
                <h4>➖ Retirer des points</h4>
                <form method="post" style="display:flex;align-items:center;gap:10px;">
                    <input type="hidden" name="client_id" value="<?= (int)$client['id'] ?>">
                    <input type="number" name="points_retrait" min="1" value="1" required style="width:80px;">
                    <button type="submit" name="action_points" value="retirer">Retirer</button>
                </form>
            </div>
        </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div style="color:#c0392b; margin-top:20px;font-weight:bold;">Aucun client trouvé avec ce code-barres ou email.</div>
    <?php endif; ?>
</div>
</body>
</html>
