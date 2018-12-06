<?php
//============================================================+
// File name   : example_033.php
// Begin       : 2008-06-24
// Last Update : 2013-05-14
//
// Description : Example 033 for TCPDF class
//               Mixed font types
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
 * @abstract TCPDF - Example: Mixed font types
 * @author Nicola Asuni
 * @since 2008-06-24
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF('l', 'mm', array(22, 105), true, 'UTF-8', false);  
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);



// set margins
$pdf->SetMargins(0, 0, 0);

// set auto page breaks
$pdf->SetAutoPageBreak(false, 0);


// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// add a page
$pdf->AddPage();

// set default font subsetting mode
$pdf->setFontSubsetting(false);
$pdf->SetFont('dejavusans', '', 9);

$provider_name    = 'NT Hồng Nhung';
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
for($i = 0; $i < 3; $i++){
   $x = (($i * 35) );
   $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, $x, 1, true, 1);
   $pdf->write1DBarcode('Zinat 125', 'C128', $x, 6, 35, 11, 0.4, $style, 'N');
   $pdf->MultiCell(35, 5, '40.000 vnd', 0, 'C', 0, 1, $x, 16, true, 0);
}



// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('example_033.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
