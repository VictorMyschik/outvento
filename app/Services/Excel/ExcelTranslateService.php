<?php

declare(strict_types=1);

namespace App\Services\Excel;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final readonly class ExcelTranslateService extends ExcelBase
{
    public const array HEADER = [
        'ID'     => 10,
        'Code'   => 20,
        'RU'     => 30,
        'EN'     => 30,
        'PL'     => 30,
        'Groups' => 30,
    ];

    public const string FILE_NAME = 'translates.xlsx';

    public function exportTranslateByFilter(array $list): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Translates');

        $this->setFirstRowHeader($sheet, self::HEADER);

        $rowNum = 2;
        foreach ($list as $row) {
            $cell = 0;

            foreach (self::HEADER as $columnTitle => $columnWidth) {
                $cellCoordinates = self::COLUMNS[$cell] . $rowNum;

                $columnTitle = mb_strtolower($columnTitle);

                switch ($columnTitle) {
                    case 'ID':
                        $sheet->setCellValueExplicit($cellCoordinates, $row['id'], DataType::TYPE_NUMERIC);
                        break;

                    default:
                        $sheet->setCellValueExplicit($cellCoordinates, $row[strtolower($columnTitle)], DataType::TYPE_STRING);
                        break;
                }

                $cell++;
            }

            $rowNum++;
        }

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A2:{$highestColumn}{$highestRow}")
            ->getAlignment()
            ->setWrapText(true);

        return $this->saveFile($spreadsheet, time() . '.xlsx');
    }

    public function parseTranslateExcel(UploadedFile $file, int $headerRowNumber = 1): array
    {
        $sheet = $this->loadWorksheet($file->getRealPath());
        $map = $this->getRowHeaderMap($sheet, $headerRowNumber);

        return $this->parseExcelRows($sheet, $map, ++$headerRowNumber);
    }
}
