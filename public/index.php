<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/SheetsService.php';

header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$id = trim((string)($body['id'] ?? ''));

if ($id === '') {
    echo json_encode(['ok' => false, 'mensagem' => 'ID vazio']);
    exit;
}

$spreadsheetId = getenv('GOOGLE_SHEETS_ID');
$credentialsJson = getenv('GOOGLE_CREDENTIALS_JSON');

$service = new SheetsService($credentialsJson, $spreadsheetId);

if (!$service->idExisteNaBase($id)) {
    echo json_encode(['ok' => false, 'mensagem' => 'ID não encontrado']);
    exit;
}

$horario = date('d/m/Y H:i:s');

$service->registrarChamada($horario, $id);

echo json_encode([
    'ok' => true,
    'mensagem' => 'Registrado com sucesso',
    'horario' => $horario
]);