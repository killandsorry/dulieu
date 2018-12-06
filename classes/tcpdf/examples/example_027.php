<?php
//============================================================+
// File name   : example_027.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 027 for TCPDF class
//               1D Barcodes
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
 * @abstract TCPDF - Example: 1D Barcodes.
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF('p', 'mm', array('h' => 105,'w' => 22), true, 'UTF-8');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set margins
$pdf->SetMargins(1, 1, 1);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', '', 8);

/*
// print a message
$txt = "You can also export 1D barcodes in other formats (PNG, SVG, HTML). Check the examples inside the barcodes directory.\n";
$pdf->MultiCell(70, 50, $txt, 0, 'J', false, 1, 125, 30, true, 0, false, true, 0, 'T', false);
$pdf->SetY(30);
*/

// -----------------------------------------------------------------------------

$pdf->SetFont('helvetica', '', 10);

// define barcode style
$style = array(
    'position' => '',
    'align' => 'C',
    'stretch' => true,
    'fitwidth' => true,
    'cellfitalign' => '',
    'border' => false,
    'hpadding' => 'auto',
    'vpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255),
    'text' => true,
    'font' => 'helvetica',
    'fontsize' => 8,
    'stretchtext' => 0
);

// add a page ----------
$pdf->AddPage();

// CODE 128 AUTO
$pdf->Cell(0, 0, 'Tên thuốc', 0, 1);
$pdf->write1DBarcode('Zinat 125', 'C128', 1, 5, 32, 10, 0.4, $style, 'N');


//Close and output PDF document
$pdf->Output('example_027.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+