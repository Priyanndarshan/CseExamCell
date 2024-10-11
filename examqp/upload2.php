<?php
require 'vendor/autoload.php';


use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['wordFile'])) {
    $file = $_FILES['wordFile'];
   
    $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
    if ($fileType != 'docx') {
        die('Please upload a DOCX file.');
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $filePath = $uploadDir . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        echo "File has been uploaded successfully.<br>";

        $phpWord = IOFactory::load($filePath);

        // Extract tables from the uploaded Word document
        $sections = $phpWord->getSections();
        $tables = [];
        foreach ($sections as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if (get_class($element) === 'PhpOffice\PhpWord\Element\Table') {
                    $tables[] = $element;
                }
            }
        }

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 50
        ];
        
        $newPhpWord = new PhpWord();
        // Set the default font to Times New Roman
        $newPhpWord->setDefaultFontName('Times New Roman');
        // Set the default font size
        $newPhpWord->setDefaultFontSize(11);
        $newSection = $newPhpWord->addSection();

        $firstTable = $tables[0];
        $newPhpWord->addTableStyle('Original Table 1', $tableStyle);
        $newTable1 = $newSection->addTable('Original Table 1');
        
        foreach ($firstTable->getRows() as $row) {
            $newRow = $newTable1->addRow();
            foreach ($row->getCells() as $cell) {
                $newCell = $newRow->addCell($cell->getWidth(), ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                foreach ($cell->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $newCell->addText(
                            $element->getText(),
                            ['bold' => true],
                            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]
                        );
                    }
                }
            }
        }
        

        // Copy the second table with no space after
        $secondTable = $tables[1];
        $newPhpWord->addTableStyle('Original Table 2', $tableStyle);
        $newTable2 = $newSection->addTable('Original Table 2');
        
        foreach ($secondTable->getRows() as $row) {
            $newRow = $newTable2->addRow();
            foreach ($row->getCells() as $cell) {
                $newCell = $newRow->addCell($cell->getWidth());
                copyCellContent($cell, $newCell);
            }
        }
        $value = ''; // Initialize a variable to store the value

if (isset($tables[1])) { // Ensure the second table exists
    $secondTable = $tables[1]; // Access the second table

    // Ensure the table has at least 3 rows and the third row has at least 2 columns
    if (count($secondTable->getRows()) >= 3) {
        $thirdRow = $secondTable->getRows()[2]; // Access the third row
        
        if (count($thirdRow->getCells()) >= 2) {
            $secondColumn = $thirdRow->getCells()[1]; // Access the second column
            
            // Extract the text from the cell and store it in the variable
            foreach ($secondColumn->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $value = $element->getText(); // Store the cell text in the variable
                    break; // Break after the first text element is found
                }
            }
        }
    }
}



        // Process the third table and generate questions with no space after
// Process the third table and generate questions with no space after
$thirdTable = $tables[2];
$rows = $thirdTable->getRows();

$headings = [];
$columnWidths = [];
$headingRow = $rows[0];

// Extract column headings and widths
foreach ($headingRow->getCells() as $cell) {
    $headings[] = $cell->getElements()[0]->getText();
    $columnWidths[] = $cell->getWidth();
}

$questions = [];
for ($i = 1; $i < count($rows); $i++) {
    $row = $rows[$i];
    $cells = $row->getCells();
    if (count($cells) >= 2) {
        $questionNumber = trim($cells[0]->getElements()[0]->getText());
        $question = [];
        foreach ($cells as $cell) {
            $question[] = $cell; // Store the cell itself to copy its contents later
        }
        if (!isset($questions[$questionNumber])) {
            $questions[$questionNumber] = [];
        }
        $questions[$questionNumber][] = $question;
    }
}

$newPhpWord->addTableStyle('Fancy Table 3', $tableStyle);
$newTable3 = $newSection->addTable('Fancy Table 3');

// Add the specified text in the first row spanning across all columns with no space after
$newTable3->addRow();
$newTable3->addCell(array_sum($columnWidths), ['gridSpan' => count($columnWidths)])
          ->addText(
              'PART A                              (Answer All Questions)                             (9 X 2 = 18)', 
              ['bold' => true],
              ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]
          );

// Add the heading row to the table
$newTable3->addRow();
foreach ($headings as $index => $heading) {
    $cell = $newTable3->addCell($columnWidths[$index]);
    $cell->addText($heading, ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
}

// Add the questions
foreach ($questions as $questionNumber => $questionArray) {
    $randomQuestion = $questionArray[array_rand($questionArray)];
    $tableRow = $newTable3->addRow();
    foreach ($randomQuestion as $index => $cell) {
        $newCell = $tableRow->addCell($columnWidths[$index]);
        copyCellContent($cell, $newCell); // Copy cell contents using the function
    }
}
       // Process the fourth table (PART B) with no space after
        $fourthTable = $tables[3];
        $fourthTableRows = $fourthTable->getRows();
        
        $range1 = [1, 2, 3, 4];
        $range2 = [5, 6, 7, 8];
        
        $selectedRowsRange1 = array_rand(array_flip($range1), 2);
        $selectedRowsRange2 = array_rand(array_flip($range2), 2);
        
        $newPhpWord->addTableStyle('Fancy Table 4', $tableStyle);
        $newTable4 = $newSection->addTable('Fancy Table 4');
        
        // Add the specified text in the first row spanning across all columns with no space after
        $newTable4->addRow();
        $newTable4->addCell(array_sum($columnWidths), ['gridSpan' => count($columnWidths)])
                  ->addText(
                      'PART B                              (Answer All Questions)                             (16 X 2 = 32)', 
                      ['bold' => true],
                      ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]
                  );
        
        // Add the heading row to the table
        $newTable4->addRow();
        foreach ($headings as $index => $heading) {
            $cell = $newTable4->addCell($columnWidths[$index]);
            $cell->addText($heading, ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        }
        
        $selectedRows = array_merge($selectedRowsRange1, $selectedRowsRange2);
        $counter = 0;
        foreach ($selectedRows as $rowIndex) {
            if (isset($fourthTableRows[$rowIndex])) {
                $row = $fourthTableRows[$rowIndex];
                $tableRow = $newTable4->addRow();
                foreach ($row->getCells() as $index => $cell) {
                    $newCell = $tableRow->addCell($columnWidths[$index]);
                    copyCellContent($cell, $newCell);
                }
            }
        
            $counter++;
            if ($counter == 1 || $counter == 3) {
                $orRow = $newTable4->addRow();
                $orCell = $orRow->addCell(array_sum($columnWidths), ['gridSpan' => count($columnWidths)]);
                $orCell->addText('OR', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
            }
        }
             // Save the new Word document
             $newFilePath = $uploadDir . $value .'.docx';
             $objWriter = IOFactory::createWriter($newPhpWord, 'Word2007');
             $objWriter->save($newFilePath);
     
             echo "The document has been created successfully.<br>";
             echo "<a href='$newFilePath'>Download the new DOCX file</a>";
         } else {
             echo "File upload failed.";
         }
     }
     function copyCellContent($sourceCell, $targetCell) {
         foreach ($sourceCell->getElements() as $element) {
             $elementClass = get_class($element);
     
             // Determine paragraph style and alignment
             $paragraphStyleObj = method_exists($element, 'getParagraphStyle') ? $element->getParagraphStyle() : null;
             $alignment = $paragraphStyleObj ? $paragraphStyleObj->getAlignment() : \PhpOffice\PhpWord\SimpleType\Jc::LEFT;
             $paragraphStyle = ['spaceAfter' => 0, 'alignment' => $alignment];
     
             switch ($elementClass) {
                 case 'PhpOffice\PhpWord\Element\TextRun':
                     $textRun = $targetCell->addTextRun($paragraphStyle);
                     foreach ($element->getElements() as $subElement) {
                         $subElementClass = get_class($subElement);
                         switch ($subElementClass) {
                             case 'PhpOffice\PhpWord\Element\Text':
                                 $textRun->addText($subElement->getText(), $subElement->getFontStyle());
                                 break;
                             case 'PhpOffice\PhpWord\Element\Image':
                                 $imageStyle = $subElement->getStyle();
                                 $textRun->addImage($subElement->getSource(), [
                                     'width' => $imageStyle->getWidth(),
                                     'height' => $imageStyle->getHeight(),
                                     'alignment' => $imageStyle->getAlignment(),
                                     'spaceAfter' => 0,
                                 ]);
                                 break;
                             case 'PhpOffice\PhpWord\Element\Equation':
                                 $textRun->addText($subElement->getText(), $subElement->getFontStyle()); // Handle equations as text for now
                                 break;
                             case 'PhpOffice\PhpWord\Element\ListItem':
                                 $listStyle = $subElement->getParagraphStyle();
                                 $textRun->addText($subElement->getText(), $subElement->getFontStyle(), $listStyle);
                                 break;
                             // Add other element types as needed
                         }
                     }
                     break;
     
                 case 'PhpOffice\PhpWord\Element\Text':
                     $targetCell->addText($element->getText(), $element->getFontStyle(), $paragraphStyle);
                     break;
     
                 case 'PhpOffice\PhpWord\Element\Image':
                     $imageStyle = $element->getStyle();
                     $targetCell->addImage($element->getSource(), [
                         'width' => $imageStyle->getWidth(),
                         'height' => $imageStyle->getHeight(),
                         'alignment' => $alignment,
                         'spaceAfter' => 0,
                     ]);
                     break;
     
                 case 'PhpOffice\PhpWord\Element\Equation':
                     $targetCell->addText($element->getText(), $element->getFontStyle(), $paragraphStyle); // Handle equations as text for now
                     break;
     
                 case 'PhpOffice\PhpWord\Element\ListItem':
                     $listStyle = $element->getParagraphStyle();
                     $targetCell->addText($element->getText(), $element->getFontStyle(), $listStyle);
                     break;
     
                 case 'PhpOffice\PhpWord\Element\Table':
                     // Copy the table
                     $tableStyle = $element->getStyle();
                     $targetTable = $targetCell->addTable($tableStyle);
                     foreach ($element->getRows() as $row) {
                         $targetRow = $targetTable->addRow();
                         foreach ($row->getCells() as $sourceTableCell) {
                             $targetTableCell = $targetRow->addCell();
                             copyCellContent($sourceTableCell, $targetTableCell); // Recursively copy cell contents
                         }
                     }
                     break;
     
                 default:
                     // For any other elements, attempt a generic copy
                     if (method_exists($element, 'getText')) {
                         $targetCell->addText($element->getText(), null, $paragraphStyle);
                     }
                     break;
             }
         }
     }
     ?>
     