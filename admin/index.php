<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Logger la visite du dashboard (optionnel, à commenter si tu ne veux pas)
ajouter_log($pdo, 'admin', $_SESSION['admin_id'], 'dashboard', null, 'Consultation du dashboard admin');

// KPIs et graphiques du dashboard
$debut = date('Y-m-01');
$fin = date('Y-m-d');
$total_clients = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$nouveaux_clients = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE date_inscription BETWEEN ? AND ?");
$nouveaux_clients->execute([$debut, $fin]);
$nouveaux_clients = $nouveaux_clients->fetchColumn();
$total_vendeurs = $pdo->query("SELECT COUNT(*) FROM vendeurs")->fetchColumn();
$total_sites = $pdo->query("SELECT COUNT(*) FROM sites")->fetchColumn();
$total_entites = $pdo->query("SELECT COUNT(*) FROM entites")->fetchColumn();

// Récupérer les 10 derniers logs (tous types confondus)
$stmt_logs = $pdo->prepare("
    SELECT l.*, 
        CASE l.user_type
            WHEN 'admin' THEN (SELECT email FROM admins WHERE id = l.user_id)
            WHEN 'vendeur' THEN (SELECT email FROM vendeurs WHERE id = l.user_id)
            WHEN 'client' THEN (SELECT email FROM utilisateurs WHERE id = l.user_id)
            ELSE NULL
        END AS user_email
    FROM logs l
    ORDER BY l.date_log DESC
    LIMIT 10
");
$stmt_logs->execute();
$logs = $stmt_logs->fetchAll();

$sites_clients = $pdo->query("
    SELECT s.nom, COUNT(u.id) AS nb_clients
    FROM sites s
    LEFT JOIN utilisateurs u ON u.site_id = s.id
    GROUP BY s.id
    ORDER BY nb_clients DESC
    LIMIT 5
")->fetchAll();

$sites_labels = [];
$sites_values = [];
foreach ($sites_clients as $row) {
    $sites_labels[] = $row['nom'];
    $sites_values[] = (int)$row['nb_clients'];
}

$evolution_clients = $pdo->query("
    SELECT DATE_FORMAT(date_inscription, '%Y-%m') as mois, COUNT(*) as nb
    FROM utilisateurs
    WHERE date_inscription >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mois
    ORDER BY mois
")->fetchAll();

$labels = [];
$values = [];
foreach ($evolution_clients as $row) {
    $labels[] = $row['mois'];
    $values[] = (int)$row['nb'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil Administrateur</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .kpi { display:inline-block; margin:10px 30px 10px 0; padding:10px 20px; background:#f5f5f5; border-radius:8px; }
        .kpi strong { font-size:2em; display:block; }
        .admin-actions { margin-bottom: 30px; }
        .admin-btn {
            display: inline-block;
            background: #337ab7;
            color: #fff;
            padding: 10px 18px;
            margin-right: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }
        .admin-btn:hover {
            background: #23527c;
            color: #fff;
        }
        #evolutionChart, #clientsBySiteChart { background: #fff; border: 1px solid #eee; border-radius: 8px; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fafdff;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px 8px;
            border: 1px solid #e5e5e5;
            text-align: left;
            font-size: 0.98em;
        }
        th {
            background: #eaf6fb;
            color: #2193b0;
            font-weight: 600;
        }
        tr:nth-child(even) { background: #f5fafd; }
        tr:hover { background: #eaf6fb; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur l’espace administrateur</h1>
        <div class="admin-actions">
            <a href="clients.php" class="admin-btn">Gestion des clients</a>
            <a href="vendeurs.php" class="admin-btn">Gestion des vendeurs</a>
            <a href="admins.php" class="admin-btn">Gérer les admins</a>
            <a href="sites.php" class="admin-btn">Gestion des sites</a>
            <a href="entites.php" class="admin-btn">Gestion des entités</a>
            <a href="offres.php" class="btn">Gérer les offres</a>
            <a href="index.php" class="admin-btn">Tableau de bord</a>
            <a href="deconnexion.php" class="admin-btn" style="background: #d9534f; color: #fff;">Déconnexion</a>
        </div>

        <div>
            <div class="kpi">
                <strong><?= $total_clients ?></strong>
                Clients inscrits
            </div>
            <div class="kpi">
                <strong><?= $nouveaux_clients ?></strong>
                Nouveaux clients ce mois
            </div>
            <div class="kpi">
                <strong><?= $total_vendeurs ?></strong>
                Vendeurs
            </div>
            <div class="kpi">
                <strong><?= $total_sites ?></strong>
                Sites
            </div>
            <div class="kpi">
                <strong><?= $total_entites ?></strong>
                Entités
            </div>
        </div>

        <h2>Top 5 sites par nombre de clients (graphique)</h2>
        <canvas id="clientsBySiteChart" width="600" height="300"></canvas>

        <h2>Évolution des inscriptions clients (6 derniers mois)</h2>
        <canvas id="evolutionChart" width="600" height="300"></canvas>

        <h2>Dernières actions (logs)</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Cible</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['date_log']) ?></td>
                    <td><?= htmlspecialchars($log['user_type']) ?></td>
                    <td><?= htmlspecialchars($log['user_email'] ?? $log['user_id']) ?></td>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><?= htmlspecialchars($log['cible']) ?></td>
                    <td><?= nl2br(htmlspecialchars($log['details'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    const ctxBar = document.getElementById('clientsBySiteChart').getContext('2d');
    const clientsBySiteChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?= json_encode($sites_labels) ?>,
            datasets: [{
                label: 'Nombre de clients',
                data: <?= json_encode($sites_values) ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    const ctxLine = document.getElementById('evolutionChart').getContext('2d');
    const evolutionChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Nouveaux clients',
                data: <?= json_encode($values) ?>,
                fill: true,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    </script>
</body>
</html>
