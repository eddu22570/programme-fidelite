<?php
function ajouter_log($pdo, $user_type, $user_id, $action, $cible = null, $details = null) {
    $stmt = $pdo->prepare("INSERT INTO logs (user_type, user_id, action, cible, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_type, $user_id, $action, $cible, $details]);
}
