<?php
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['user_id'])) header('Location: connexion.php');

// Récupération des infos utilisateur
$stmt = $pdo->prepare("SELECT nom, prenom, points, code_barre, site_favori_id FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit;
}

// Récupération du site favori (nom uniquement)
$site_favori = null;
$site_favori_id = null;
if ($user['site_favori_id']) {
    $stmt = $pdo->prepare("SELECT id, nom FROM sites WHERE id = ?");
    $stmt->execute([$user['site_favori_id']]);
    $site_favori = $stmt->fetch(PDO::FETCH_ASSOC);
    $site_favori_id = $site_favori['id'];
}

// Chemin relatif de l'icône magasin
$chemin_icone_magasin = '../public/images/icone-magasin.png';

// ----------- FILTRE OFFRES -----------
$filtre = $_GET['filtre'] ?? 'magasin';
$today = date('Y-m-d');

if ($filtre === 'all') {
    $sql = "
        SELECT o.*
        FROM offres o
        WHERE o.actif = 1
          AND (o.date_debut IS NULL OR o.date_debut <= :today)
          AND (o.date_fin IS NULL OR o.date_fin >= :today)
        ORDER BY o.date_debut DESC, o.id DESC
    ";
    $params = ['today' => $today];
} else {
    $sql = "
        SELECT o.*
        FROM offres o
        LEFT JOIN offre_sites os ON o.id = os.offre_id
        WHERE o.actif = 1
          AND (o.date_debut IS NULL OR o.date_debut <= :today)
          AND (o.date_fin IS NULL OR o.date_fin >= :today)
          AND (
                os.site_id IS NULL
                ".($site_favori_id ? "OR os.site_id = :site_favori_id" : "")."
              )
        GROUP BY o.id
        ORDER BY o.date_debut DESC, o.id DESC
    ";
    $params = ['today' => $today];
    if ($site_favori_id) {
        $params['site_favori_id'] = $site_favori_id;
    }
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque offre, récupérer les sites concernés
foreach ($offres as &$offre) {
    $stmt2 = $pdo->prepare("SELECT s.nom FROM offre_sites os JOIN sites s ON os.site_id = s.id WHERE os.offre_id = ?");
    $stmt2->execute([$offre['id']]);
    $offre['sites_noms'] = $stmt2->fetchAll(PDO::FETCH_COLUMN);
}
unset($offre);

// ----------- HISTORIQUE DES POINTS -----------
$stmt = $pdo->prepare("
    SELECT type, points, date, recompense_id
    FROM transactions
    WHERE utilisateur_id = ?
    ORDER BY date DESC
    LIMIT 30
");
$stmt->execute([$_SESSION['user_id']]);
$historique_points = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Optionnel : Récupérer les noms de récompense pour les échanges
$recompense_noms = [];
if (!empty($historique_points)) {
    $ids = array_filter(array_column($historique_points, 'recompense_id'));
    if (!empty($ids)) {
        $in = implode(',', array_map('intval', $ids));
        $stmt = $pdo->query("SELECT id, titre FROM recompenses WHERE id IN ($in)");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $recompense_noms[$row['id']] = $row['titre'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon tableau de bord fidélité</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <div class="nav-main">
        <div class="nav-links">
            <a href="modifier_infos.php" class="btn">Modifier mes informations</a>
            <a href="mon_site_favori.php" class="btn">Mon site favori</a>
            <a href="deconnexion.php" class="btn">Se déconnecter</a>
        </div>
    </div>
    <h1>Bonjour, <?= htmlspecialchars($user['prenom']) ?> !</h1>

    <!-- Bloc affichage site favori -->
    <?php if ($site_favori && $site_favori['nom']): ?>
        <div class="site-favori-display">
            <img src="<?= $chemin_icone_magasin ?>" alt="Icône magasin">
            <span>Site favori : <strong><?= htmlspecialchars($site_favori['nom']) ?></strong></span>
        </div>
    <?php else: ?>
        <div style="margin: 20px 0; font-style: italic; color: #666;">
            Vous n’avez pas encore sélectionné de site favori.
        </div>
    <?php endif; ?>

    <div class="user-points" style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:1.5em;">⭐</span>
        <strong>Points de fidélité :</strong> <?= (int)$user['points'] ?>
    </div>

    <div class="card-barcode">
        <h3 style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:1.3em;">💳</span> Ma carte fidélité
        </h3>
        <img src="barcode.php?code=<?= urlencode($user['code_barre']) ?>" alt="Code-barres utilisateur" /><br>
        <span><?= htmlspecialchars($user['code_barre']) ?></span>
    </div>

    <!-- BOUTONS DE FILTRE -->
    <div class="filtre-offres-btns" style="margin-bottom:20px;">
        <a href="?filtre=magasin" class="btn<?= ($filtre === 'magasin' ? ' active' : '') ?>">Offres de mon magasin</a>
        <a href="?filtre=all" class="btn<?= ($filtre === 'all' ? ' active' : '') ?>">Toutes les offres</a>
    </div>

    <!-- Offres du moment -->
    <div class="offres-client" style="margin:36px 0 0 0;">
        <h2 style="display:flex;align-items:center;gap:10px;"><span style="font-size:1.3em;">🎁</span> Offres du moment</h2>
        <?php if (!empty($offres)): ?>
            <?php foreach ($offres as $offre): ?>
                <div>
                    <h3><?= htmlspecialchars($offre['titre']) ?></h3>
                    <?php if ($offre['description']): ?>
                        <p><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
                    <?php endif; ?>
                    <?php if ($offre['date_debut'] || $offre['date_fin']): ?>
                        <p style="color:#666;font-size:0.95em;">
                            <?php if ($offre['date_debut']): ?>Du <?= htmlspecialchars($offre['date_debut']) ?><?php endif; ?>
                            <?php if ($offre['date_debut'] && $offre['date_fin']): ?> au <?php endif; ?>
                            <?php if ($offre['date_fin']): ?><?= htmlspecialchars($offre['date_fin']) ?><?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <p style="font-size:0.96em;color:#217dbb;">
                        <strong>Sites concernés :</strong>
                        <?php if (!empty($offre['sites_noms'])): ?>
                            <?= implode(', ', array_map('htmlspecialchars', $offre['sites_noms'])) ?>
                        <?php else: ?>
                            Tous les sites
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="margin:24px 0; color:#c0392b;">Aucune offre disponible pour ce filtre actuellement.</div>
        <?php endif; ?>
    </div>

    <!-- HISTORIQUE DES POINTS -->
    <div class="historique-points">
        <h2 style="display:flex;align-items:center;gap:10px;"><span style="font-size:1.2em;">📜</span> Historique de mes points</h2>
        <?php if (!empty($historique_points)): ?>
            <table class="table-historique-points">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th style="text-align:right;">Points</th>
                        <th>Détail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historique_points as $ligne): ?>
                        <tr>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($ligne['date']))) ?></td>
                            <td>
                                <?php
                                    switch ($ligne['type']) {
                                        case 'achat': echo 'Achat'; break;
                                        case 'echange': echo 'Échange'; break;
                                        case 'ajout': echo '<span style="color:green;">Ajout</span>'; break;
                                        case 'retrait': echo '<span style="color:#c0392b;">Retrait</span>'; break;
                                        default: echo htmlspecialchars($ligne['type']);
                                    }
                                ?>
                            </td>
                            <td style="text-align:right;">
                                <?php
                                    if ($ligne['type'] === 'ajout') {
                                        echo '+' . abs((int)$ligne['points']);
                                    } elseif ($ligne['type'] === 'retrait') {
                                        echo '-' . abs((int)$ligne['points']);
                                    } else {
                                        echo (int)$ligne['points'];
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    if ($ligne['type'] === 'echange' && $ligne['recompense_id']) {
                                        echo isset($recompense_noms[$ligne['recompense_id']])
                                            ? 'Échange de : ' . htmlspecialchars($recompense_noms[$ligne['recompense_id']])
                                            : 'Échange de récompense #' . (int)$ligne['recompense_id'];
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="color:#666;font-style:italic;">Aucun mouvement de points pour l’instant.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
