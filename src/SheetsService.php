<?php

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class SheetsService
{
    private Sheets $service;
    private string $spreadsheetId;

    public function __construct(string $json, string $spreadsheetId)
    {
        $credenciais = json_decode($json, true);

        if (!is_array($credenciais)) {
            throw new Exception('JSON das credenciais inválido');
        }

        $client = new Client();
        $client->setAuthConfig($credenciais);
        $client->setScopes([Sheets::SPREADSHEETS]);

        $this->service = new Sheets($client);
        $this->spreadsheetId = $spreadsheetId;
    }

    public function idExisteNaBase(string $id): bool
    {
        $res = $this->service->spreadsheets_values->get(
            $this->spreadsheetId,
            'BASE!A2:A'
        );

        $values = $res->getValues();

        if (!is_array($values)) {
            return false;
        }

        foreach ($values as $row) {
            $idBase = trim((string)($row[0] ?? ''));
            if ($idBase === $id) {
                return true;
            }
        }

        return false;
    }

    public function registrarChamada(string $horario, string $id): void
    {
        $body = new ValueRange([
            'values' => [[$horario, $id]]
        ]);

        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            'CHAMADA!A:B',
            $body,
            ['valueInputOption' => 'USER_ENTERED']
        );
    }
}