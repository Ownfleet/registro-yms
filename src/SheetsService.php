<?php

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class SheetsService
{
    private $service;
    private $spreadsheetId;

    public function __construct($json, $spreadsheetId)
    {
        $client = new Client();
        $client->setAuthConfig(json_decode($json, true));
        $client->setScopes([Sheets::SPREADSHEETS]);

        $this->service = new Sheets($client);
        $this->spreadsheetId = $spreadsheetId;
    }

    public function idExisteNaBase($id)
    {
        $res = $this->service->spreadsheets_values->get(
            $this->spreadsheetId,
            'BASE!A2:A'
        );

        foreach ($res->getValues() as $row) {
            if (trim($row[0]) == $id) return true;
        }

        return false;
    }

    public function registrarChamada($horario, $id)
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