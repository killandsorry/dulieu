<?php
//============================================================+
// File name   : example_028.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 028 for TCPDF class
//               Changing page formats
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: changing page formats
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(1, 1, 1);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

$pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');

// set font
$pdf->SetFont('times', '', 8);
$style = array(
    'position' => '',
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => true,
    'cellfitalign' => '',
    'border' => true,
    'hpadding' => 'auto',
    'vpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255),
    'text' => true,
    'font' => 'helvetica',
    'fontsize' => 8,
    'stretchtext' => 4
);

$pdf->Cell(0, 0, 'CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9', 1, 1);
$pdf->write1DBarcode('CODE 39', 'C39', 0, 0, 30, 6, 0.4, $style, 'N');
//$pdf->AddPage('l', array(22,105));
//$pdf->Cell(30, 10, 'A4 PORTRAIT', 0, 0, 'C');
/*
$pdf->setPage(1, true);
$pdf->SetY(50);
$pdf->Cell(0, 0, 'A4 test', 1, 1, 'C');
*/
//
//$pdf->AddPage('L', 'A4');
//$pdf->Cell(0, 0, 'A4 LANDSCAPE', 1, 1, 'C');
//
//$pdf->AddPage('P', 'A5');
//$pdf->Cell(0, 0, 'A5 PORTRAIT', 1, 1, 'C');
//
//$pdf->AddPage('L', 'A5');
//$pdf->Cell(0, 0, 'A5 LANDSCAPE', 1, 1, 'C');
//
//$pdf->AddPage('P', 'A6');
//$pdf->Cell(0, 0, 'A6 PORTRAIT', 1, 1, 'C');
//
//$pdf->AddPage('L', 'A6');
//$pdf->Cell(0, 0, 'A6 LANDSCAPE', 1, 1, 'C');
//
//$pdf->AddPage('P', 'A7');
//$pdf->Cell(0, 0, 'A7 PORTRAIT', 1, 1, 'C');
//
//$pdf->AddPage('L', 'A7');
//$pdf->Cell(0, 0, 'A7 LANDSCAPE', 1, 1, 'C');
//
//
// --- test backward editing ---



//
//$pdf->setPage(2, true);
//$pdf->SetY(50);
//$pdf->Cell(0, 0, 'A4 test', 1, 1, 'C');
//
//$pdf->setPage(3, true);
//$pdf->SetY(50);
//$pdf->Cell(0, 0, 'A5 test', 1, 1, 'C');
//
//$pdf->setPage(4, true);
//$pdf->SetY(50);
//$pdf->Cell(0, 0, 'A5 test', 1, 1, 'C');
//
//$pdf->setPage(5, true);
//$pdf->SetY(50);
//$pdf->Cell(0, 0, 'A6 test', 1, 1, 'C');
//
//$pdf->setPage(6, true);
//$pdf->SetY(50);
//$pdf->Cell(0, 0, 'A6 test', 1, 1, 'C');
//
//$pdf->setPage(7, true);
//$pdf->SetY(40);
//$pdf->Cell(0, 0, 'A7 test', 1, 1, 'C');
//
//$pdf->setPage(8, true);
//$pdf->SetY(40);
//$pdf->Cell(0, 0, 'A7 test', 1, 1, 'C');
//

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_028.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
