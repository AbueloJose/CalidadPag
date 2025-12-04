<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

$email = $_GET['email'] ?? '';
$pdo = (new Database())->connect();
$stmt = $pdo->prepare("SELECT foto_biometria FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && $user['foto_biometria']) {
    echo json_encode(['success' => true, 'foto_biometria' => $user['foto_biometria']]);
} else {
    echo json_encode(['success' => false]);
}
?>