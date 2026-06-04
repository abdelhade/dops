<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SpreadsheetExporter
{
    /**
     * @param  list<string>  $headers
     * @param  list<list<mixed>>  $rows
     */
    public function downloadXlsx(string $basename, array $headers, array $rows): StreamedResponse
    {
        $filename = preg_replace('/[^\w\-]+/', '_', $basename) . '.xlsx';

        return response()->streamDownload(function () use ($headers, $rows) {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([$headers], null, 'A1');

            if ($rows !== []) {
                $sheet->fromArray($rows, null, 'A2');
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<mixed>|null  $sampleRow
     */
    public function downloadTemplate(string $basename, array $headers, ?array $sampleRow = null): StreamedResponse
    {
        $rows = $sampleRow !== null ? [$sampleRow] : [];

        return $this->downloadXlsx($basename . '-template', $headers, $rows);
    }

    /**
     * @return list<list<string>>
     */
    public function readDataRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        if (count($data) <= 1) {
            return [];
        }

        $rows = [];

        for ($i = 1, $count = count($data); $i < $count; $i++) {
            $row = $data[$i];
            $cells = [];

            foreach ($row as $cell) {
                $cells[] = trim((string) ($cell ?? ''));
            }

            if ($this->rowIsEmpty($cells)) {
                continue;
            }

            $rows[] = $cells;
        }

        return $rows;
    }

    /**
     * @param  list<string>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== '') {
                return false;
            }
        }

        return true;
    }
}
