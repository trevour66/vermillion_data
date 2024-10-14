<?php

namespace App\Http\Controllers;

use App\Models\Company_Database;
use DateTime;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DuplicateChecker extends Controller
{

    public function download_file(Request $request)
    {
        try {

            $file_path = $request->input('file_path');

            if (!$file_path) {
                return;
            }

            return Storage::download("public/" . $file_path);
        } catch (\Throwable $th) {
            logger($th->getMessage());
            return response()->json([
                'error' => true,
            ]);
        }
    }

    public function check_for_duplicate(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:text/csv,text/plain,application/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/xlsx',
        ]);

        $seed = uniqid('', true);
        $seed = hash('md2', $seed);

        $date = new DateTime();
        $date_string = $date->format('Y_M_d_H_i_s');

        $extension = $request->file('file')->extension();
        $csv_temp_name = $date_string . '_' . $seed . '_CSV_' . $request->file('file')->getClientOriginalName();

        $request->file('file')->storeAs('public', $csv_temp_name);

        logger($csv_temp_name);
        logger($extension);

        $process_file_response = $this->process_file($csv_temp_name, $extension);

        return response()->json([
            'download_url' => ($process_file_response) ? Storage::url($csv_temp_name) : false,
        ]);
    }

    private function process_file($fileLocation, $extension)
    {
        try {
            $path = storage_path('app/public') . '/' . $fileLocation;
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);

            $sheetCount = $spreadsheet->getSheetCount();
            if ($sheetCount != 1) {
                throw new Error("TOO MANY SHEETS");
            }

            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $lastColumn = $spreadsheet->getActiveSheet()->getHighestColumn();

            $header = $spreadsheet->getActiveSheet()->rangeToArray(
                'A1:' . $lastColumn . '1',     // The worksheet range that we want to retrieve
                null,        // Value that should be returned for empty cells
                true,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
                true,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
                true         // Should the array be indexed by cell row and cell column
            );

            // logger($header);

            $websiteColumn = '';
            foreach ($header[1] as $key => $value) {
                $header_normalized = strtolower($value ?? '');

                if ($header_normalized  == 'website') {
                    $websiteColumn = $key;
                }
            }

            if ($websiteColumn == '') {
                throw new Error("invalid format");
            }

            $lastColumn++; // Add new column
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue($lastColumn . 1, 'Result');

            // logger($sheetData);
            for ($i = 2 /*skip header*/; $i <= count($sheetData); ++$i) {
                $website = $sheetData[$i][$websiteColumn] ?? false;

                if (!$website) {
                    continue;
                }

                $website = $this->formatUrl($website);

                $website_isFound = Company_Database::where("website", "=", $website)->first() ?? false;
                if ($website_isFound) {
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setCellValue($lastColumn . $i, 'Website already in database');
                } else {
                    // logger(print_r("not found", true));
                    // logger(print_r($lastColumn, true));


                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setCellValue($lastColumn . $i, 'Not in database');

                    // logger($sheet->getCell($lastColumn . $i)->getValue());
                }
            }

            if ($extension == "xlsx") {
                $writer =  new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save($path);
            }
            if ($extension == "csv") {
                $writer =  new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);

                // logger(print_r($spreadsheet, true));
                // logger(print_r($writer, true));

                $writer->save($path);
            }

            logger('done');

            return true;
        } catch (\Throwable $th) {
            logger('Error : process_file : ' . $th->getMessage());

            return false;
        }
    }

    private function formatUrl($url)
    {
        try {
            $url = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $url);

            if (strpos($url, 'www.') !== 0) {
                $url = 'www.' . $url;
            }

            return strtok($url, '/');  // Strips everything after the domain

        } catch (\Throwable $th) {
            throw new Error('Error | formatUrl');
            return false;
        }
    }
}
