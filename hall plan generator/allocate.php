<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;

function splitSections($data) {
    $sections = [];
    $current_section = [];
    
    foreach ($data as $row) {
        $current_section[] = $row;
    }
    if (!empty($current_section)) {
        $sections[] = $current_section;
    }
    return $sections;
}

function extractColumnB($data) {
    $columnBData = [];
    foreach ($data as $row) {
        if (isset($row[1])) { // Column B is the second column (index 1)
            $columnBData[] = $row[1];
        }
    }
    return $columnBData;
}

function extractColumnD($data) {
    $columnDData = [];
    foreach ($data as $row) {
        if (isset($row[3])) { // Column D is the fourth column (index 3)
            $columnDData[] = $row[3];
        }
    }
    return $columnDData;
}

function formatRollNumbersByGroup($rollNumbers, $groupIdentifiers) {
    $groupedRollNumbers = [];
    $currentGroup = [];
    
    foreach ($rollNumbers as $index => $rollNumber) {
        $groupIdentifier = $groupIdentifiers[$index];
        if (empty($currentGroup) || $currentGroup['group'] === $groupIdentifier) {
            $currentGroup['values'][] = $rollNumber;
            $currentGroup['group'] = $groupIdentifier;
        } else {
            $groupedRollNumbers[] = formatRollNumbers($currentGroup['values']);
            $currentGroup = [
                'group' => $groupIdentifier,
                'values' => [$rollNumber]
            ];
        }
    }
    
    if (!empty($currentGroup['values'])) {
        $groupedRollNumbers[] = formatRollNumbers($currentGroup['values']);
    }
    
    return implode(', ', $groupedRollNumbers);
}

function formatRollNumbers($rollNumbers) {
    $formatted = [];
    $start = $rollNumbers[0];
    $end = $rollNumbers[0];
    
    for ($i = 1; $i < count($rollNumbers); $i++) {
        if (isInRange($rollNumbers[$i], $end)) {
            $end = $rollNumbers[$i];
        } else {
            $formatted[] = $start === $end ? $start : $start . '-' . $end;
            $start = $rollNumbers[$i];
            $end = $rollNumbers[$i];
        }
    }
    
    $formatted[] = $start === $end ? $start : $start . '-' . $end;
    return implode(', ', $formatted);
}

function isInRange($current, $last) {
    // Ensure both inputs are strings
    $current = (string) $current;
    $last = (string) $last;
    
    // Extract the last two characters of each string and compare
    return intval(substr($current, -2)) === intval(substr($last, -2)) + 1;
}

function allocateStudentsToHalls($hallNames, $hallCapacities, $secondYearSections, $thirdYearSections) {
    $hallAllocations = [];
    $secondYearQueue = array_merge(...$secondYearSections);
    $thirdYearQueue = array_merge(...$thirdYearSections);

    // Get all halls and shuffle them
    $availableHalls = range(0, count($hallCapacities) - 1); // Indexing starts from 0
    shuffle($availableHalls);

    foreach ($availableHalls as $i) {
        $capacity = $hallCapacities[$i];
        $halfCapacity = floor($capacity / 2);
        $hall = $hallNames[$i];

        // Allocate Second Year Students
        $allocatedSecondYear = array_splice($secondYearQueue, 0, $halfCapacity);

        // Allocate Third Year Students
        $allocatedThirdYear = array_splice($thirdYearQueue, 0, $halfCapacity);

        $hallAllocations[] = [
            'Hall' => $hall,
            'Second Year' => $allocatedSecondYear,
            'Third Year' => $allocatedThirdYear,
        ];

        // Stop if we've allocated to all halls
        if (count($hallAllocations) === count($hallCapacities)) {
            break;
        }
    }

    return [$hallAllocations, $secondYearQueue, $thirdYearQueue];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstSetYearLabel = $_POST['secondYearLabel'];
    $secondSetYearLabel = $_POST['thirdYearLabel'];
    $firstSetYearFile = $_FILES['secondYearFile']['tmp_name'];
    $secondSetYearFile = $_FILES['thirdYearFile']['tmp_name'];
    
 // Extract data for second set
 
 $hallNames = array_map('trim', explode(',', $_POST['hallNames'])); // Extract hall names
    $hallCapacities = array_map('intval', explode(',', $_POST['hallCapacities']));
    

    $examTitle = $_POST['examTitle']; // Get the exam title from POST
    $examDate = $_POST['examDate']; // Get the exam date from POST
    $dateTime = new DateTime($examDate);
    $formattedDate = $dateTime->format('d-m-Y');

    $examInfo = "$examTitle Hall Allocation ($formattedDate)"; // Format the exam info

    // Extract the filenames of uploaded files without the extension
    $firstSetFileName = pathinfo($_FILES['secondYearFile']['name'], PATHINFO_FILENAME);
    $secondSetFileName = pathinfo($_FILES['thirdYearFile']['name'], PATHINFO_FILENAME);
    
       // Optional: Handle filenames for the second set
    // Create the dynamic output file name
    $outputFileName = "{$firstSetFileName} & {$secondSetFileName} Hall_Allocation.xlsx";
    $reader = new ReaderXlsx();

    // Load Second Year Data
    $spreadsheet = $reader->load($firstSetYearFile);
    $secondYearData = $spreadsheet->getActiveSheet()->toArray();
    $secondYearSections = splitSections($secondYearData);

    // Load Third Year Data
    $spreadsheet = $reader->load($secondSetYearFile);
    $thirdYearData = $spreadsheet->getActiveSheet()->toArray();
    $thirdYearSections = splitSections($thirdYearData);
    // Optional: Load second set of data
 

    list($hallAllocations, $remainingSecondYearQueue, $remainingThirdYearQueue) = allocateStudentsToHalls(
        $hallNames,
        $hallCapacities,
        $secondYearSections,
        $thirdYearSections
    );
    

    // Create a new spreadsheet for output
    $outputSpreadsheet = new Spreadsheet();
    $sheet = $outputSpreadsheet->getActiveSheet();
    $sheet->setTitle('Hall Allocations');
    $sheet->removeRow(1, 4); // Removes rows 1 to 4 (inclusive)

    // After removing the rows, the data will shift up and you can continue
    $row = 1; // Reset row index for new data start (or 5 if you still have headers)
  
    // Set headers
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'VELAMMAL COLLEGE OF ENGINEERING AND TECHNOLOGY, MADURAI');
    
    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', '(Autonomous)');
    
    $sheet->mergeCells('A3:E3');
    $sheet->setCellValue('A3', 'DEPARTMENT OF COMPUTER SCIENCE AND ENGINEERING');
    
    $sheet->mergeCells('A4:E4');
    $sheet->setCellValue('A4', $examInfo); // Set exam info in the fourth row

    // Set font to Times New Roman for all cells
    $sheet->getStyle('A1:E' . $sheet->getHighestRow())->getFont()->setName('Times New Roman');
    
    // Set header styling
    $sheet->getStyle('A1:E4')->getFont()->setBold(true);
    $sheet->getStyle('A1:E4')->getAlignment()->setHorizontal('center');

    // Apply border styling to header rows
    $sheet->getStyle('A1:E4')->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ],
        ],
    ]);

    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(12);
    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(40);
    $sheet->getColumnDimension('D')->setWidth(12);
    $sheet->getColumnDimension('E')->setWidth(12);

    // Wrap text in column C
    $sheet->getStyle('C')->getAlignment()->setWrapText(true);

    // Set table headers
    $headers = ['Hall No', 'Year', 'Roll NO(From)', 'Strength', 'Total'];
    $sheet->fromArray($headers, NULL, 'A5');
    
    // Apply border styling to table headers
    $sheet->getStyle('A5:E5')->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ],
        ],
    ]);

    // Set header row styling
    $sheet->getStyle('A5:E5')->getFont()->setBold(true);
    $sheet->getStyle('A5:E5')->getAlignment()->setHorizontal('center');

    $row = 6;
    foreach ($hallAllocations as $allocation) {
        // Extract and group roll numbers for Second Year and Third Year
        $secondYearRollNumbers = extractColumnB($allocation['Second Year']);
        $secondYearGroups = extractColumnD($allocation['Second Year']);
        $thirdYearRollNumbers = extractColumnB($allocation['Third Year']);
        $thirdYearGroups = extractColumnD($allocation['Third Year']);

        // Format the roll numbers
        $secondYearFormatted = formatRollNumbersByGroup($secondYearRollNumbers, $secondYearGroups);
        $thirdYearFormatted = formatRollNumbersByGroup($thirdYearRollNumbers, $thirdYearGroups);

        $secondYearCount = count($secondYearRollNumbers);
        $thirdYearCount = count($thirdYearRollNumbers);
        $total = $secondYearCount + $thirdYearCount;

        $sheet->setCellValue('A' . $row, $allocation['Hall']);
        $sheet->setCellValue('B' . $row, $firstSetYearLabel);
        $sheet->setCellValue('C' . $row, $secondYearFormatted);
        $sheet->setCellValue('D' . $row, $secondYearCount);
        $sheet->setCellValue('E' . $row, $total);
        $row++;

        $sheet->setCellValue('A' . $row, $allocation['Hall']);
        $sheet->setCellValue('B' . $row, $secondSetYearLabel);
        $sheet->setCellValue('C' . $row, $thirdYearFormatted);
        $sheet->setCellValue('D' . $row, $thirdYearCount);
        $sheet->setCellValue('E' . $row, $total);
        $row++;
    }

    // Add remaining students after allocations
    if (!empty($remainingSecondYearQueue) || !empty($remainingThirdYearQueue)) {
        if (!empty($remainingSecondYearQueue)) {
            $remainingSecondYearRollNumbers = extractColumnB($remainingSecondYearQueue);
            $remainingSecondYearGroups = extractColumnD($remainingSecondYearQueue);
            $formattedRemainingSecondYearRollNumbers = formatRollNumbersByGroup($remainingSecondYearRollNumbers, $remainingSecondYearGroups);

            $sheet->setCellValue("A{$row}", "Remaining ".$firstSetFileName);
            $sheet->setCellValue("B{$row}", $firstSetYearLabel);
            $sheet->setCellValue("C{$row}", $formattedRemainingSecondYearRollNumbers);
            $sheet->setCellValue("D{$row}", count($remainingSecondYearQueue));
            $sheet->setCellValue("E{$row}", count($remainingSecondYearQueue));
            
            // Apply border styling to remaining students rows
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ],
                ],
            ]);

            $row++;
        }

        if (!empty($remainingThirdYearQueue)) {
            $remainingThirdYearRollNumbers = extractColumnB($remainingThirdYearQueue);
            $remainingThirdYearGroups = extractColumnD($remainingThirdYearQueue);
            $formattedRemainingThirdYearRollNumbers = formatRollNumbersByGroup($remainingThirdYearRollNumbers, $remainingThirdYearGroups);

            $sheet->setCellValue("A{$row}", "Remaining ".$secondSetFileName);
            $sheet->setCellValue("B{$row}", $secondSetYearLabel);
            $sheet->setCellValue("C{$row}", $formattedRemainingThirdYearRollNumbers);
            $sheet->setCellValue("D{$row}", count($remainingThirdYearQueue));
            $sheet->setCellValue("E{$row}", count($remainingThirdYearQueue));
            
            // Apply border styling to remaining students rows
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ],
                ],
            ]);

            $row++;
        }
    }
    // Set font to Times New Roman for all cells in the worksheet
    $sheet->getStyle('A1:E' . $sheet->getHighestRow())->getFont()->setName('Times New Roman');

    // Remove the empty row if added by mistake
    if ($sheet->getCell('A' . $row)->getValue() === null) {
        $row--;
    }
    // Calculate number of rows with content
    $contentRowCount = $row - 5; // Subtract the header rows

    // Apply thin borders to every row with content
    $sheet->getStyle('A6:E' . $row)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            ],
        ],
    ]);
    
    $sheet->getStyle('D6:D' . $row)->getAlignment()->setHorizontal('left');
    $sheet->getStyle('E6:E' . $row)->getAlignment()->setHorizontal('left');

    // Define the directory for storing hall plans
$directory = 'hallPlans/';
if (!is_dir($directory)) {
    mkdir($directory, 0777, true); // Create the directory if it doesn't exist
}
 

// Define the output file name with full path in the hallPlans directory
$outputFileName = $directory . "{$firstSetFileName}_{$secondSetFileName}_Hall_Allocation.xlsx";

// Save the output file in the specified directory
$writer = new Xlsx($outputSpreadsheet);
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

}
?>
