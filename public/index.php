<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'mensagem' => 'Método não permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $rawBody = file_get_contents('php://input');
    $body = json_decode($rawBody, true);

    if (!is_array($body)) {
        $body = [];
    }

    $id = trim((string)($body['id'] ?? ''));

    if ($id === '') {
        echo json_encode([
            'ok' => false,
            'mensagem' => 'ID vazio'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $spreadsheetId = getenv('GOOGLE_SHEETS_ID');
    $credentialsJson = getenv('GOOGLE_CREDENTIALS_JSON');

    if (!$spreadsheetId) {
        throw new Exception('GOOGLE_SHEETS_ID não configurado');
    }

    if (!$credentialsJson) {
        throw new Exception('GOOGLE_CREDENTIALS_JSON não configurado');
    }

    $service = new SheetsService($credentialsJson, $spreadsheetId);

    if (!$service->idExisteNaBase($id)) {
        echo json_encode([
            'ok' => false,
            'mensagem' => 'ID não encontrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $horario = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    $horarioFormatado = $horario->format('d/m/Y H:i:s');

    $service->registrarChamada($horarioFormatado, $id);

    echo json_encode([
        'ok' => true,
        'mensagem' => 'Registrado com sucesso',
        'horario' => $horarioFormatado,
        'id' => $id
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensagem' => 'Erro interno',
        'erro' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}