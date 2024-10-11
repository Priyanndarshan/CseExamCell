<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file1']) && isset($_FILES['file2'])) {
        // Load the first Excel file
        $reader = new ReaderXlsx();
        $spreadsheet1 = $reader->load($_FILES['file1']['tmp_name']);
        $sheet1 = $spreadsheet1->getActiveSheet();
        
        // Load the second Excel file
        $spreadsheet2 = $reader->load($_FILES['file2']['tmp_name']);
        $sheet2 = $spreadsheet2->getActiveSheet();

        // Create a new Spreadsheet for the merged content
        $mergedSpreadsheet = new Spreadsheet();
        $mergedSheet = $mergedSpreadsheet->getActiveSheet();

        // Set column widths
        $columnWidths = [12, 12, 40, 12, 12];
        foreach ($columnWidths as $columnIndex => $width) {
            $mergedSheet->getColumnDimensionByColumn($columnIndex + 1)->setWidth($width);
        }

        // Get the highest column count to know how many columns to merge
        $highestColumn = $sheet1->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        // Copy the first four rows from the first input file to the merged file
        for ($row = 1; $row <= 4; $row++) {
            // Set cell values for the first four rows
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $cellValue = $sheet1->getCell(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row)->getValue();
                $mergedSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row, $cellValue);

                // Enable text wrapping for the cell
                $mergedSheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row)->getAlignment()->setWrapText(true);
            }
            // Merge cells in the current row
            $mergedSheet->mergeCells("A$row:{$highestColumn}$row");
            // Center align the merged cells
            $mergedSheet->getStyle("A$row:{$highestColumn}$row")->getAlignment()->setHorizontal('center');

            // Apply thin border to the merged row
            $mergedSheet->getStyle("A$row:{$highestColumn}$row")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ],
                ],
            ]);
            
            // Set the font to bold for the first four rows
            $mergedSheet->getStyle("A$row:{$highestColumn}$row")->getFont()->setBold(true);
        }

        // Copy the fifth row as is
        $fifthRow = 5;
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellValue = $sheet1->getCell(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $fifthRow)->getValue();
            $mergedSheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $fifthRow, $cellValue);

            // Enable text wrapping for the cell
            $mergedSheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $fifthRow)->getAlignment()->setWrapText(true);
        }

        // Center align the fifth row
        $mergedSheet->getStyle("A$fifthRow:{$highestColumn}$fifthRow")->getAlignment()->setHorizontal('center');
        
        // Set the font to bold for the fifth row
        $mergedSheet->getStyle("A$fifthRow:{$highestColumn}$fifthRow")->getFont()->setBold(true);

        // Apply thin border to the fifth row
        $mergedSheet->getStyle("A$fifthRow:{$highestColumn}$fifthRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ],
            ],
        ]);

        // Get the highest row from both sheets
        $rowCount1 = $sheet1->getHighestRow();
        $rowCount2 = $sheet2->getHighestRow();

        // Copy data from the first file, starting from row 6
        for ($row = 6; $row <= $rowCount1; $row++) {
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $sheet1->getCell("$col$row")->getValue();
                $mergedSheet->setCellValue("$col$row", $cellValue);

                // Enable text wrapping for the cell
                $mergedSheet->getStyle("$col$row")->getAlignment()->setWrapText(true);
            }
        }

        // Copy data from the second file, starting after the last row of the first file
        for ($row = 6; $row <= $rowCount2; $row++) {
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $sheet2->getCell("$col$row")->getValue();
                $mergedSheet->setCellValue("$col" . ($row + $rowCount1 - 5), $cellValue);

                // Enable text wrapping for the cell
                $mergedSheet->getStyle("$col" . ($row + $rowCount1 - 5))->getAlignment()->setWrapText(true);
            }
        }

        // Apply thin borders to all rows and columns (except the first five rows)
        $highestRow = $mergedSheet->getHighestRow();
        
        for ($row = 6; $row <= $highestRow; $row++) {
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $mergedSheet->getStyle("$col$row")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                        ],
                    ],
                ]);
            }
        }

        // Extract the fourth row value for naming the output file
        $fourthRowValue = $sheet1->getCell("A4")->getValue(); // Assuming the desired value is in cell A4
        preg_match('/\((.*?)\)/', $fourthRowValue, $matches); // Extract the date inside parentheses

        // Format the extracted date
        $dateString = isset($matches[1]) ? date('d.m.y', strtotime($matches[1])) : 'default'; // Fallback to 'default' if date extraction fails

        // Define the directory for storing final hall plans
        $directory = 'finalHallPlan/';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true); // Create the directory if it doesn't exist
        }

        // Define the output file name with full path in the finalHallPlan directory
        $outputFileName = $directory . "{$dateString} Hall Plan.xlsx";


        // Save the merged file in the specified directory
        $writer = new Xlsx($mergedSpreadsheet);
        $writer->save($outputFileName);

        // Set headers to force download
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($outputFileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($outputFileName));

        // Read the file and output it to the browser
        readfile($outputFileName);

        // Terminate the script to prevent further output
        exit;

    } else {
        echo "Please upload both files.";
    }
}
?>
