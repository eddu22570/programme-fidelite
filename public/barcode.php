<?php
require_once __DIR__ . '/../includes/barcode39/Barcode39.php';

$code = isset($_GET['code']) ? $_GET['code'] : 'EXEMPLE123';
// Sécurité : Code 39 accepte A-Z, 0-9, -, ., $, /, +, %, espace
$code = strtoupper(preg_replace('/[^A-Z0-9\-\.\ \$\/\+\%]/', '', substr($code, 0, 30)));

$bc = new Barcode39($code);
$bc->barcode_text_size = 5;
$bc->barcode_bar_thick = 4;
$bc->barcode_bar_thin = 2;
$bc->draw();
?>