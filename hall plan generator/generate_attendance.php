<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

error_reporting(E_ALL);
ini_set('display_errors', 1);

function expandRollNumberRange($range) {
    $parts = explode('-', $range);
    if (count($parts) === 2) {
        $start = $parts[0];
        $end = $parts[1];
        $startNum = intval(substr($start, -2));
        $endNum = intval(substr($end, -2));

        $base = substr($start, 0, -2);
        $rollNumbers = [];

        for ($i = $startNum; $i <= $endNum; $i++) {
            $rollNumbers[] = $base . str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        return $rollNumbers;
    } else {
        return [$range];
    }
}

function extractRollNumbersFromAllocation($filePath) {
    $reader = new ReaderXlsx();
    $spreadsheet = $reader->load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    $data = $sheet->toArray();
    $hallRollNumbers = [];

    array_shift($data);

    foreach ($data as $row) {
        $hallName = isset($row[0]) ? $row[0] : ''; // Hall Name column (index 0)
        $rollNumberRanges = isset($row[2]) ? explode(', ', $row[2]) : []; // Roll No column (index 2)

        foreach ($rollNumberRanges as $range) {
            $rollNumbers = expandRollNumberRange($range);
            if (!isset($hallRollNumbers[$hallName])) {
                $hallRollNumbers[$hallName] = [];
            }
            $hallRollNumbers[$hallName] = array_merge($hallRollNumbers[$hallName], $rollNumbers);
        }
    }

    foreach ($hallRollNumbers as $hall => $rollNumbers) {
        $hallRollNumbers[$hall] = array_unique(array_filter($rollNumbers));
    }
    
    return $hallRollNumbers;
}

function generateAttendanceSheets($hallRollNumbers) {
    $spreadsheet = new Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');

    $columnMapping = [
        'A' => 'S.No',
        'B' => 'Roll No',
        'D' => 'S.No',
        'E' => 'Roll No'
    ];

    $sheetIndex = 0;
    foreach ($hallRollNumbers as $hallName => $rollNumbers) {
        $sheet = $spreadsheet->createSheet($sheetIndex++);
        $sheet->setTitle($hallName ?: 'Sheet');

        // Set top most rows
        $sheet->setCellValue('A1', 'Velammal College of Engineering and Technology, Madurai');
        $sheet->setCellValue('A2', '(Autonomous)');
        $sheet->setCellValue('A3', 'Department of Computer Science and Engineering');
        $sheet->setCellValue('A4', 'Attendance Sheet');
    
        // Merge cells for the top rows
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');

        // Set font style, alignment, and column width for the top rows
        $sheet->getStyle('A1:I4')->getFont()->setBold(true);
        $sheet->getStyle('A1:I4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(5.00);
        $sheet->getColumnDimension('B')->setWidth(18.00);
        $sheet->getColumnDimension('D')->setWidth(5.00);
        $sheet->getColumnDimension('E')->setWidth(18.00);

        // Set headers for the columns
        $sheet->setCellValue('A5', $columnMapping['A']);
        $sheet->setCellValue('B5', $columnMapping['B']);
        $sheet->setCellValue('D5', $columnMapping['D']);
        $sheet->setCellValue('E5', $columnMapping['E']);

        // Apply thin border for header and data rows
        $headerAndDataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        // Apply the style for the header row A5:E5
        $sheet->getStyle('A5:E5')->applyFromArray($headerAndDataStyle);

        // Populate roll numbers
        $row = 6;
        $halfCount = ceil(count($rollNumbers) / 2);

        // First half goes to columns A and B
        for ($i = 0; $i < $halfCount; $i++) {
            $sheet->setCellValue('A' . $row, $i + 1); // Serial number (A column)
            $sheet->setCellValue('B' . $row, $rollNumbers[$i]); // Roll number (B column)
            $row++;
        }

        // Second half goes to columns D and E
        $secondRow = 6; // Start from the same row
        for ($i = $halfCount; $i < count($rollNumbers); $i++) {
            $sheet->setCellValue('D' . $secondRow, $i - $halfCount + 1); // Serial number (D column)
            $sheet->setCellValue('E' . $secondRow, $rollNumbers[$i]); // Roll number (E column)
            $secondRow++;
        }

        // Add additional rows for Strength, Present, Absentees Roll No, and Hall Invigilators Sign
        $endRow = max($row, $secondRow);

        // Merge and set values for A:B and D:E
        $sheet->mergeCells("A$endRow:B$endRow");
        $sheet->setCellValue("A$endRow", 'Strength');
        $sheet->mergeCells("D$endRow:E$endRow");
        $sheet->setCellValue("D$endRow", 'Strength');
        $endRow++;

        $sheet->mergeCells("A$endRow:B$endRow");
        $sheet->setCellValue("A$endRow", 'Present');
        $sheet->mergeCells("D$endRow:E$endRow");
        $sheet->setCellValue("D$endRow", 'Present');
        $endRow++;

        $sheet->mergeCells("A$endRow:B$endRow");
        $sheet->setCellValue("A$endRow", 'Absentees Roll No');
        $sheet->mergeCells("D$endRow:E$endRow");
        $sheet->setCellValue("D$endRow", 'Absentees Roll No');
        $endRow++;

        $sheet->mergeCells("A$endRow:B$endRow");
        $sheet->setCellValue("A$endRow", 'Hall Invigilators Sign');
        $sheet->mergeCells("D$endRow:E$endRow");
        $sheet->setCellValue("D$endRow", 'Hall Invigilators Sign');

        // Set text alignment for roll numbers and additional rows
        $sheet->getStyle("A5:B$endRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("D5:E$endRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Apply the same border style to all data rows
        $dataRange = "A5:F$endRow";
        $sheet->getStyle($dataRange)->applyFromArray($headerAndDataStyle);
    }

    // Remove the default sheet created with the spreadsheet
    $spreadsheet->removeSheetByIndex(0);

    return $spreadsheet;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['allocationFile']) && $_FILES['allocationFile']['error'] == UPLOAD_ERR_OK) {
        $allocationFile = $_FILES['allocationFile']['tmp_name'];
        $uploadedFileName = $_FILES['allocationFile']['name'];

        // Extract the file name without extension
        $fileNameWithoutExtension = pathinfo($uploadedFileName, PATHINFO_FILENAME);

        // Remove "_Hall_Allocation" from the filename if it exists
        $cleanedFileName = str_replace('Hall_Allocation', '', $fileNameWithoutExtension);

        // Extract roll numbers from the allocation file
        $hallRollNumbers = extractRollNumbersFromAllocation($allocationFile);
        
        // Generate the attendance sheets
        $attendanceSpreadsheet = generateAttendanceSheets($hallRollNumbers);
        
        // Create the attendance file name using the cleaned file name
        $attendanceFileName = $cleanedFileName . ' - Attendance_Sheet.xlsx';

        // Specify the output directory
        $outputDirectory = 'HallPlans/';
        
        // Ensure the output directory exists
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0777, true); // Create directory if it doesn't exist
        }

        // Save the attendance sheet to the specified directory
        $outputFilePath = $outputDirectory . $attendanceFileName;
        $writer = new WriterXlsx($attendanceSpreadsheet);
        $writer->save($outputFilePath);
        
        // Flush the output buffer and send the file to the browser
        ob_end_clean();  // Clean output buffer to avoid errors in the Excel file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $attendanceFileName . '"');
        header('Cache-Control: max-age=0');
        readfile($outputFilePath);
        exit;
    } else {
        echo "Error uploading file.";
    }
}
