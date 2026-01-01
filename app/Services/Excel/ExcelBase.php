<?php

declare(strict_types=1);

namespace App\Services\Excel;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract readonly class ExcelBase
{
    private const string DIRECTORY = '/tmp/excel';
    public const array COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
        'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    public const int MAX_ROWS = 2500;
    public const string MAX_COLUMNS = 'CZ';

    protected function saveFile(Spreadsheet $spreadsheet, string $filename): string
    {
        Storage::makeDirectory(self::DIRECTORY);

        $writer = IOFactory::createWriter($spreadsheet, IOFactory::READER_XLSX);
        $writer->save(Storage::path(self::DIRECTORY) . '/' . $filename);

        return self::DIRECTORY . '/' . $filename;
    }

    /**
     * Загружает из файла лист.
     * Загружаются только данные. Пустые ячейки игнорируются.
     */
    public function loadWorksheet(string $filename, int $sheetIndex = 0): Worksheet
    {
        $readerType = IOFactory::identify($filename);
        $reader = IOFactory::createReader($readerType);

        if ($reader instanceof BaseReader) {
            $reader->setReadDataOnly(true);
            $reader->setIncludeCharts(false);
        }

        $spreadsheet = $reader->load($filename);

        return $spreadsheet->getSheet($sheetIndex);
    }

    public static function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        if ($length === 0) {
            return false;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * Читает первую строку и строит карту сопоставления номеров столбцов и названий колонки и первой строки
     */
    public static function buildFirstRowHeaderMap(Worksheet $sheet, $rowNum = 1, $maxColumn = 'CW'): array
    {
        $headerRowArray = $sheet->rangeToArray('A' . $rowNum . ':' . $maxColumn . $rowNum, null, false, true, true);
        return array_filter($headerRowArray[$rowNum]);
    }

    /**
     * Заполняет первую строку и строит карту сопоставления номеров столбцов и названий колонки
     *
     * @param Worksheet $sheet
     * @param array $header title => width
     * @param int $rowNum
     * @return array 'column index' => 'header'
     * @throws Exception
     */
    public function setFirstRowHeader(Worksheet $sheet, array $header, int $rowNum = 1): array
    {
        $column = 'A';
        $dataMap = array();

        if (empty($header)) {
            throw new Exception('Header is empty');
        }

        foreach ($header as $key => $value) {
            if (is_numeric($key)) {
                $title = $value;
                $width = 0;
            } else {
                $title = $key;
                $width = $value;
            }

            $dataMap[$column] = $title;

            $sheet->setCellValueExplicit($column . $rowNum, $title, DataType::TYPE_STRING);

            if ($width) {
                $sheet->getColumnDimension($column)->setWidth($width);
            }

            $column++;
        }

        $headerRange = 'A' . $rowNum . ':' . array_key_last($dataMap) . $rowNum;

        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');

        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('f2f2f2');

        $sheet->getStyle(
            $headerRange
        )->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


        $sheet->freezePane('A2');

        return $dataMap;
    }

    public function getRowHeaderMap(Worksheet $sheet, int $headerRowNumber): array
    {
        return self::buildFirstRowHeaderMap($sheet, $headerRowNumber, self::MAX_COLUMNS);
    }

    /**
     * @param int $dataRowNumber - первая строка данных (не включая заголовок, только данные)
     * @param array $parseExcelCells - Конкретные колонки (индексы колонок), которые нужно распарсить
     */
    protected function parseExcelRows(Worksheet $sheet, array $parseExcelCells, int $dataRowNumber = 1): array
    {
        $rows = [];

        foreach ($sheet->getRowIterator($dataRowNumber) as $excelRow) {
            $row = $this->readRowData($excelRow, $parseExcelCells);

            if (is_null($row)) {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function readRowData(Row $excelRow, array $needHeader = []): ?array
    {
        $row = [];

        $rowEmpty = true;
        foreach ($needHeader as $cell => $columnName) {
            if (is_numeric($cell)) {
                $cell = self::COLUMNS[--$cell];
            }

            foreach ($excelRow->getCellIterator($cell, $cell) as $excelCell) {

                $value = trim($excelCell->getValueString());

                if ($value !== '') {
                    $rowEmpty = false;
                } else {
                    $value = null;
                }

                $row[$columnName] = $value;
            }
        }

        // ignoring empty rows in Excel
        if ($rowEmpty) {
            return null;
        }

        return $row;
    }

    #region Хелперы для чтения специфических данных из колонок с проверкой по справочникам

    /**
     * Строковое значение
     *
     * @param        $value
     * @param int $maxLength максимальная длина строки
     * @param array $errors массив с ошибками
     * @param string $cellName
     * @return null|string
     */
    protected static function readString($value, int $maxLength, array &$errors, string $cellName): ?string
    {
        if (!$value) {
            return null;
        }

        $value = (string)$value;

        $length = mb_strlen($value);
        if ($length > $maxLength) {
            $errors[] = ($cellName . "максимальная длина строки $maxLength, а в ячейке $length.");

            return null;
        }

        return $value;
    }

    /**
     * Целочисленное значение
     *
     * @param        $value
     * @param array $errors массив с ошибками
     * @param string $cellName
     *
     * @return null|int
     */
    protected static function readInt($value, array &$errors, string $cellName): ?int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (!strlen($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $errors[] = ($cellName . "неверное целочисленное значение '$value'");

            return null;
        }

        $value = (int)$value;

        return $value;
    }

    /**
     * Дата
     */
    protected static function readDate($value, array &$errors, string $cellName)//: ?string
    {
        if (!$value) {
            return null;
        }

        $date = self::dateValue($value);

        if (!$date) {
            $errors[] = $cellName . "неверное значение даты '$value'.";

            return null;
        }

        return $date;
    }

    /**
     * Читает дату из ячейки Excel, независимо от типа ячейки (дата, число или строка)
     */
    public static function dateValue($excelValue): ?Carbon
    {
        $datetime = null;

        if ($excelValue) {
            if (is_numeric($excelValue)) {
                try {
                    $datetime = Date::excelToDateTimeObject($excelValue, Carbon::getDefaultTimezone());
                } catch (Exception $ex) {
                }
            }

            if ($datetime) {
                $datetime = new Carbon($datetime);
            } else {
                try {
                    $datetime = new Carbon($excelValue);
                } catch (Exception $ex) {
                    $datetime = null;
                }
            }
        }

        return $datetime;
    }
    #endregion
}
