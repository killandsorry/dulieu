<?
/**
 * 
 * Chiến:
 * 17/06/2016
 * 
 * Class generate barcode to pdf
 */

define('PAGE_SMALL', 1); // mẫu giấy chỉ có 1 ô ()
define('PAGE_SMALL_MEDIUM', 2); // mẫu giấy chỉ có 2 ô (75mm x 10mm)
define('PAGE_MEDIUM', 3); // mẫu giấy chỉ có 3 ô (105mm x 22mm)
define('PAGE_LAGER', 4); // mẫu giấy chỉ có 65 ô (202mm x 162mm)

class printpdf{
   
   
   
   function print_pdf($type = 0, $data = array(), $start_prt = 1){
      if($type <= 0 || empty($data)) return '';
      
      switch($type){
         case 1:
            return $this->print_small($data, $start_prt);
            break;
         case 2:
            return $this->print_small_medium($data, $start_prt);
            break;
         case 3:
            return $this->print_medium($data, $start_prt);
            break;
         case 4:
            return $this->print_lager($data, $start_prt);
            break;
         case 5:
            return $this->print_lager_90($data, $start_prt);
            break;
      }
   }
   
   function print_pdf_multi($type = 0, $data = array(), $start_prt = 1){
      if($type <= 0 || empty($data)) return '';
      
      switch($type){
         case 1:
            return $this->print_small($data, $start_prt);
            break;
         case 2:
            return $this->print_small_medium($data, $start_prt);
            break;
         case 3:
            return $this->print_multi_medium($data, $start_prt);
            break;
         case 4:
            return $this->print_multi_lager($data, $start_prt);
            break;
         case 5:
            return $this->print_multi_lager_90($data, $start_prt);
            break;
         case 6:
            return $this->print_multi_two($data, $start_prt);
            break;
      }
   }
   
   
   function print_small($data = array(), $start_prt = 1){
      $pdf = new TCPDF('l', 'mm', array(20, 30), true, 'UTF-8', false);  
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
      
      $provider_name = isset($data['pvname'])? $data['pvname'] : '';
      $barcode       = isset($data['barcode'])? $data['barcode'] : '';
      $unit          = isset($data['unit'])? $data['unit'] : '';
      $price         = (isset($data['price']) && $data['price'] > 0)? format_number($data['price']) . ' đ' : '';
      $price_str     = (isset($data['price_str']) && $data['price_str'] != '')? $data['price_str'] : '';
      
      if($unit != '') $price .= '/' . $unit;
      if($price_str != '') $price = $price_str;
      
      $pro_name = isset($data['name'])? $data['name'] : '';
      
      // add a page
      $pdf->AddPage();      
      // set default font subsetting mode
      $pdf->setFontSubsetting(false);
      $pdf->SetFont('dejavusans', '',6);
      $pdf->SetLineWidth(0);
      
      
      
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
          'fontsize' => 6,
          'stretchtext' => 0
      );
      
      $pdf->MultiCell(30, 1, $provider_name, 0, 'C', false, 1, 0, 0, true, 1);
      $pdf->write1DBarcode($barcode, 'C128', 0, 2, 30, 10, 0.4, $style, 'N');
      if($pro_name != ''){
         $height_proname   = 10.5;
         $height_price     = 15;
         
         if(mb_strlen($pro_name,'UTF-8') <=22){
            $height_proname = 11; 
            $height_price     = 14;
         } else {
            if(mb_strlen($price) <= 22){
               $height_price     = 17;
            }else{
               $height_price     = 16;
            }
         }
         $pdf->MultiCell(30, 2, $pro_name, 0, 'C', false, 0, 0, $height_proname, true, 1);
         if($price != ''){
            $pdf->MultiCell(30, 5, $price, 0, 'C', 0, 1, 0, $height_price, true, 0);
         }
      }else{
         if($price != ''){
            $pdf->MultiCell(30, 5, $price, 0, 'C', 0, 1, 0, 13, true, 0);
         }
      }
      
      
      //Close and output PDF document
      return $pdf->Output('barcode.pdf', 'I');
   }
   
   
   function print_medium($data = array(), $start_prt = 1){
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
      
      $provider_name = isset($data['pvname'])? $data['pvname'] : '';
      $barcode       = isset($data['barcode'])? $data['barcode'] : '';
      $unit          = isset($data['unit'])? $data['unit'] : '';
      $price         = (isset($data['price']) && $data['price'] > 0)? format_number($data['price']) . ' đ' : '';
      $price_str     = (isset($data['price_str']) && $data['price_str'] != '')? $data['price_str'] : '';
      $pro_name = isset($data['name'])? $data['name'] : '';
      
      if($price_str != '') $price = $price_str;
      if($unit != '') $price .= '/' . $unit;
      
      // add a page
      $pdf->AddPage();      
      // set default font subsetting mode
      $pdf->setFontSubsetting(false);
      $pdf->SetFont('dejavusans', '', 6);
      
      
      
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
          'fontsize' => 6,
          'stretchtext' => 0
      );
      
      /*
      $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, 1, 1, true, 1);
      $pdf->write1DBarcode($barcode, 'C128', 1, 6, 35, 11, 0.4, $style, 'N');
      $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, 1, 16, true, 0);
      */
      for($i = 0; $i < 3; $i++){
         $x = (($i * 35) );
         $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, $x, 1.8, true, 1);
         $pdf->write1DBarcode($barcode, 'C128', $x, 3.8, 35, 10, 0.4, $style, 'N');
         if($pro_name != ''){
            
            $height_proname   = 12;
            $height_price     = 18;
            
            if(mb_strlen($pro_name,'UTF-8') <=22){
               $height_proname = 12.5; 
               $height_price     = 16;
            } else {
               if(mb_strlen($price) <= 22){
                  $height_price     = 17;
               }
            }
            
            $pdf->MultiCell(35, 5, $pro_name, 0, 'C', false, 1, $x, $height_proname, true, 1);
            if($price != ''){
               $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, $x, $height_price, true, 0);
            }
         }else{
            if($price != ''){
               $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, $x, 14, true, 0);
            }
         }
         
      }
      
      
      //Close and output PDF document
      return $pdf->Output('barcode.pdf', 'I');
   }
   
   function print_lager($data = array(), $start_prt = 1){
      $pdf = new TCPDF('p', 'mm', array(297,210), true, 'UTF-8', false);  
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
      // add a page
      $pdf->AddPage();      
      // set default font subsetting mode
      $pdf->setFontSubsetting(false);
      $pdf->SetFont('dejavusans', '', 6);
      
      $provider_name = isset($data['pvname'])? $data['pvname'] : '';
      $barcode       = isset($data['barcode'])? $data['barcode'] : '';
      $unit          = isset($data['unit'])? $data['unit'] : '';
      $price         = (isset($data['price']) && $data['price'] > 0)? format_number($data['price']) . ' đ' : '';
      $price_str     = (isset($data['price_str']) && $data['price_str'] != '')? $data['price_str'] : '';
      $pro_name = isset($data['name'])? $data['name'] : '';
      
      if($price_str != '') $price = $price_str;
      if($unit != '') $price .= '/' . $unit;
      
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
          'fontsize' => 6,
          'stretchtext' => 0
      );
      
      /*
      $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, 1, 1, true, 1);
      $pdf->write1DBarcode($barcode, 'C128', 1, 6, 35, 11, 0.4, $style, 'N');
      $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, 1, 16, true, 0);
      */
      for($i = 0; $i < 13; $i++){
         $y = ($i * 21) + 15;         
         for($j = 0; $j<5; $j++){
            $x = ($j * 38) + 10;
            $pdf->MultiCell(38, 1, $provider_name, 0, 'C', false, 1, $x, $y, true, 1);
            $pdf->write1DBarcode($barcode, 'C128', $x, $y + 2, 38, 10, 0.4, $style, 'N');
            if($pro_name != ''){
               $height_proname   = 12;
               $height_price     = 18;
               
               if(mb_strlen($pro_name,'UTF-8') <=22){
                  $height_proname = 12.5; 
                  $height_price     = 16;
               } else {
                  if(mb_strlen($price) <= 22){
                     $height_price     = 17;
                  }
               }
               
               $pdf->MultiCell(35, 5, $pro_name, 0, 'L', false, 1, $x + 1, $y + 10, true, 1);
               if($price != ''){
                  $pdf->MultiCell(38, 5, $price, 0, 'R', 0, 1, $x, $y + 15, true, 0);
               }
            }else{
               if($price != ''){
                  $pdf->MultiCell(38, 5, $price, 0, 'C', 0, 1, $x, $y + 11, true, 0);
               }
               
            }
            
         }
      }
      
      
      //Close and output PDF document
      return $pdf->Output('barcode.pdf', 'I');
   }
   
   function print_lager_90($data = array(), $start_prt = 1){
      $pdf = new TCPDF('p', 'mm', array(297,210), true, 'UTF-8', false);  
      $pdf->setPrintHeader(false);
      $pdf->setPrintFooter(false);
      // set margins
      $pdf->SetMargins(0,0,0);
      // set auto page breaks
      $pdf->SetAutoPageBreak(false, 0);
      // set some language-dependent strings (optional)
      if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
      	require_once(dirname(__FILE__).'/lang/eng.php');
      	$pdf->setLanguageArray($l);
      }
      // add a page
      $pdf->AddPage();      
      // set default font subsetting mode
      $pdf->setFontSubsetting(false);
      $pdf->SetFont('dejavusans', '', 6);
      
      $provider_name = isset($data['pvname'])? $data['pvname'] : '';
      $barcode       = isset($data['barcode'])? $data['barcode'] : '';
      $unit          = isset($data['unit'])? $data['unit'] : '';
      $price         = (isset($data['price']) && $data['price'] > 0)? format_number($data['price']) . ' đ' : '';
      $price_str     = (isset($data['price_str']) && $data['price_str'] != '')? $data['price_str'] : '';
      $pro_name = isset($data['name'])? $data['name'] : '';
      
      if($price_str != '') $price = $price_str;
      if($unit != '') $price .= '/' . $unit;
      
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
          'fontsize' => 5,
          'stretchtext' => 0
      );
      
      /*
      $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, 1, 1, true, 1);
      $pdf->write1DBarcode($barcode, 'C128', 1, 6, 35, 11, 0.4, $style, 'N');
      $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, 1, 16, true, 0);
      */
      for($i = 0; $i < 15; $i++){
         $y = ($i * 19) + 6;         
         for($j = 0; $j<6; $j++){
            $x = ($j * 33) + 6;
            $pdf->MultiCell(33, 1, $provider_name, 0, 'C', false, 1, $x, $y, true, 1);
            $pdf->write1DBarcode($barcode, 'C128', $x, $y + 1, 33, 10, 0.4, $style, 'N');
            if($pro_name != ''){
               $height_proname   = 12;
               $height_price     = 18;
               
               if(mb_strlen($pro_name,'UTF-8') <=22){
                  $height_proname = 12.5; 
                  $height_price     = 16;
               } else {
                  if(mb_strlen($price) <= 22){
                     $height_price     = 18;
                  }
               }
               
               $pdf->MultiCell(33, 5, $pro_name, 0, 'L', false, 1, $x + 1, $y + 10, true, 1);
               if($price != ''){
                  $pdf->MultiCell(33, 5, $price, 0, 'R', 0, 1, $x, $y + 14.4, true, 1);
               }
            }else{
               if($price != ''){
                  $pdf->MultiCell(33, 5, $price, 0, 'C', 0, 1, $x, $y + 11, true, 0);
               }
               
            }
            
         }
      }
      
      
      //Close and output PDF document
      return $pdf->Output('barcode.pdf', 'I');
   }
   
   
   function print_multi_two($data = array(), $start_prt = 1){
      $pdf = new TCPDF('l', 'mm', array(22, 70), true, 'UTF-8', false);  
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
      
      $provider_name = isset($data['pvname'])? $data['pvname'] : '';
      $barcode       = isset($data['barcode'])? $data['barcode'] : '';
      $unit          = isset($data['unit'])? $data['unit'] : '';
      $price         = (isset($data['price']) && $data['price'] > 0)? format_number($data['price']) . ' đ' : '';
      $price_str     = (isset($data['price_str']) && $data['price_str'] != '')? $data['price_str'] : '';
      $pro_name = isset($data['name'])? $data['name'] : '';
      if($unit != '') $price .= '/' . $unit;
      if($price_str != '') $price = $price_str;
          
      // set default font subsetting mode
      $pdf->setFontSubsetting(false);
      $pdf->SetFont('dejavusans', '', 6);
      
      
      
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
          'fontsize' => 6,
          'stretchtext' => 0
      );
      
      
      $count_page = 1;
      $tottal_product   = count($data) - 1;
      $count_page       = ceil($tottal_product / 2);
      
      /*
      $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, 1, 1, true, 1);
      $pdf->write1DBarcode($barcode, 'C128', 1, 6, 35, 11, 0.4, $style, 'N');
      $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, 1, 16, true, 0);
      */
      $position   = 0;
      for($page  = 0; $page < $count_page; $page++){
         // add a page
         $pdf->AddPage();
         for($i = 0; $i < 2; $i++){
            $x = (($i * 35) );
            
            $position++;
               
            if(isset($data[$position]) && !empty($data[$position])){
               
               $provider_name = isset($data[$position]['pvname'])? $data[$position]['pvname'] : '';
               $barcode       = isset($data[$position]['barcode'])? $data[$position]['barcode'] : '';
               $unit          = isset($data[$position]['unit'])? $data[$position]['unit'] : '';
               $price         = (isset($data[$position]['price']) && $data[$position]['price'] > 0)? format_number($data[$position]['price']) . ' đ' : '';
               $price_str     = (isset($data[$position]['price_str']) && $data[$position]['price_str'] != '')? $data[$position]['price_str'] : '';
               $pro_name = isset($data[$position]['name'])? $data[$position]['name'] : '';
               
               if($price_str != '') $price = $price_str;
               if($unit != '') $price .= '/' . $unit;
         
               $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, $x, 1.8, true, 1);
               $pdf->write1DBarcode($barcode, 'C128', $x, 3.8, 35, 10, 0.4, $style, 'N');
               if($pro_name != ''){
                  
                  $height_proname   = 12;
                  $height_price     = 18;
                  
                  if(mb_strlen($pro_name,'UTF-8') <=22){
                     $height_proname = 12.5; 
                     $height_price     = 16;
                  } else {
                     if(mb_strlen($price) <= 22){
                        $height_price     = 17;
                     }
                  }
                  
                  $pdf->MultiCell(35, 5, $pro_name, 0, 'C', false, 1, $x, $height_proname, true, 1);
                  if($price != ''){
                     $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, $x, $height_price, true, 0);
                  }
               }else{
                  if($price != ''){
                     $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, $x, 14, true, 0);
                  }
               }
            }
            
         }
      }
      
      
      //Close and output PDF document
      return $pdf->Output('barcode.pdf', 'I');
   }
   
   
   
   
   function print_multi_medium($data = array(), $start_prt = 1){
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
      
      $provider_name = isset($data['pvname'])? $data['pvname'] : '';
      $barcode       = isset($data['barcode'])? $data['barcode'] : '';
      $unit          = isset($data['unit'])? $data['unit'] : '';
      $price         = (isset($data['price']) && $data['price'] > 0)? format_number($data['price']) . ' đ' : '';
      $price_str     = (isset($data['price_str']) && $data['price_str'] != '')? $data['price_str'] : '';
      $pro_name = isset($data['name'])? $data['name'] : '';
      if($unit != '') $price .= '/' . $unit;
      if($price_str != '') $price = $price_str;
          
      // set default font subsetting mode
      $pdf->setFontSubsetting(false);
      $pdf->SetFont('dejavusans', '', 6);
      
      
      
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
          'fontsize' => 6,
          'stretchtext' => 0
      );
      
      
      $count_page = 1;
      $tottal_product   = count($data) - 1;
      $count_page       = ceil($tottal_product / 3);
      
      /*
      $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, 1, 1, true, 1);
      $pdf->write1DBarcode($barcode, 'C128', 1, 6, 35, 11, 0.4, $style, 'N');
      $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, 1, 16, true, 0);
      */
      $position   = 0;
      for($page  = 0; $page < $count_page; $page++){
         // add a page
         $pdf->AddPage();
         for($i = 0; $i < 3; $i++){
            $x = (($i * 35) );
            
            $position++;
               
            if(isset($data[$position]) && !empty($data[$position])){
               
               $provider_name = isset($data[$position]['pvname'])? $data[$position]['pvname'] : '';
               $barcode       = isset($data[$position]['barcode'])? $data[$position]['barcode'] : '';
               $unit          = isset($data[$position]['unit'])? $data[$position]['unit'] : '';
               $price         = (isset($data[$position]['price']) && $data[$position]['price'] > 0)? format_number($data[$position]['price']) . ' đ' : '';
               $price_str     = (isset($data[$position]['price_str']) && $data[$position]['price_str'] != '')? $data[$position]['price_str'] : '';
               $pro_name = isset($data[$position]['name'])? $data[$position]['name'] : '';
               
               if($price_str != '') $price = $price_str;
               if($unit != '') $price .= '/' . $unit;
         
               $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, $x, 1.8, true, 1);
               $pdf->write1DBarcode($barcode, 'C128', $x, 3.8, 35, 10, 0.4, $style, 'N');
               if($pro_name != ''){
                  
                  $height_proname   = 12;
                  $height_price     = 18;
                  
                  if(mb_strlen($pro_name,'UTF-8') <=22){
                     $height_proname = 12.5; 
                     $height_price     = 16;
                  } else {
                     if(mb_strlen($price) <= 22){
                        $height_price     = 17;
                     }
                  }
                  
                  $pdf->MultiCell(35, 5, $pro_name, 0, 'C', false, 1, $x, $height_proname, true, 1);
                  if($price != ''){
                     $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, $x, $height_price, true, 0);
                  }
               }else{
                  if($price != ''){
                     $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, $x, 14, true, 0);
                  }
               }
            }
            
         }
      }
      
      
      //Close and output PDF document
      return $pdf->Output('barcode.pdf', 'I');
   }
   
   function print_multi_lager($data = array(), $start_prt = 1){
      $pdf = new TCPDF('p', 'mm', array(297,210), true, 'UTF-8', false);  
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
      // add a page
      //$pdf->AddPage();      
      // set default font subsetting mode
      $pdf->setFontSubsetting(false);
      $pdf->SetFont('dejavusans', '', 6);
      
      $provider_name = isset($data['pvname'])? $data['pvname'] : '';
      $barcode       = isset($data['barcode'])? $data['barcode'] : '';
      $unit          = isset($data['unit'])? $data['unit'] : '';
      $price         = (isset($data['price']) && $data['price'] > 0)? format_number($data['price']) . ' đ' : '';
      $price_str     = (isset($data['price_str']) && $data['price_str'] != '')? $data['price_str'] : '';
      $pro_name = isset($data['name'])? $data['name'] : '';
      if($unit != '') $price .= '/' . $unit;
      if($price_str != '') $price = $price_str;
      
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
          'fontsize' => 6,
          'stretchtext' => 0
      );
      
      
      $count_page = 1;
      $tottal_product   = count($data) - 1;
      $count_page       = ceil($tottal_product / 65);
      
      /*
      $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, 1, 1, true, 1);
      $pdf->write1DBarcode($barcode, 'C128', 1, 6, 35, 11, 0.4, $style, 'N');
      $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, 1, 16, true, 0);
      */
      $position   = 0;
      for($page  = 0; $page < $count_page; $page++){
         // add a page
         $pdf->AddPage();
         for($i = 0; $i < 13; $i++){
            $y = ($i * 21) + 15;         
            for($j = 0; $j<5; $j++){
               $x = ($j * 38) + 10;
               
               $position++;
               
               if(isset($data[$position]) && !empty($data[$position])){
                  
                  $provider_name = isset($data[$position]['pvname'])? $data[$position]['pvname'] : '';
                  $barcode       = isset($data[$position]['barcode'])? $data[$position]['barcode'] : '';
                  $unit          = isset($data[$position]['unit'])? $data[$position]['unit'] : '';
                  $price         = (isset($data[$position]['price']) && $data[$position]['price'] > 0)? format_number($data[$position]['price']) . ' đ' : '';
                  $price_str     = (isset($data[$position]['price_str']) && $data[$position]['price_str'] != '')? $data[$position]['price_str'] : '';
                  $pro_name = isset($data[$position]['name'])? $data[$position]['name'] : '';
                  
                  if($price_str != '') $price = $price_str;
                  if($unit != '') $price .= '/' . $unit;
                  
                  $pdf->MultiCell(38, 1, $provider_name, 0, 'C', false, 1, $x, $y, true, 1);
                  $pdf->write1DBarcode($barcode, 'C128', $x, $y + 2, 38, 10, 0.4, $style, 'N');
                  if($pro_name != ''){
                     $height_proname   = 12;
                     $height_price     = 18;
                     
                     if(mb_strlen($pro_name,'UTF-8') <=22){
                        $height_proname = 12.5; 
                        $height_price     = 16;
                     } else {
                        if(mb_strlen($price) <= 22){
                           $height_price     = 17;
                        }
                     }
                     
                     $pdf->MultiCell(35, 5, $pro_name, 0, 'L', false, 1, $x + 1, $y + 10, true, 1);
                     if($price != ''){
                        $pdf->MultiCell(38, 5, $price, 0, 'R', 0, 1, $x, $y + 15, true, 0);
                     }
                  }else{
                     if($price != ''){
                        $pdf->MultiCell(38, 5, $price, 0, 'C', 0, 1, $x, $y + 11, true, 0);
                     }
                     
                  }
               }
               
            }
         }
      }
      
      
      //Close and output PDF document
      return $pdf->Output('barcode.pdf', 'I');
   }
   
   function print_multi_lager_90($data = array(), $start_prt = 1){
      $pdf = new TCPDF('p', 'mm', array(297,210), true, 'UTF-8', false);  
      $pdf->setPrintHeader(false);
      $pdf->setPrintFooter(false);
      // set margins
      $pdf->SetMargins(0,0,0);
      // set auto page breaks
      $pdf->SetAutoPageBreak(false, 0);
      // set some language-dependent strings (optional)
      if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
      	require_once(dirname(__FILE__).'/lang/eng.php');
      	$pdf->setLanguageArray($l);
      }
      // add a page
      //$pdf->AddPage();      
      // set default font subsetting mode
      $pdf->setFontSubsetting(false);
      $pdf->SetFont('dejavusans', '', 6);
      
      $provider_name = isset($data['pvname'])? $data['pvname'] : '';
      $barcode       = isset($data['barcode'])? $data['barcode'] : '';
      $unit          = isset($data['unit'])? $data['unit'] : '';
      $price         = (isset($data['price']) && $data['price'] > 0)? format_number($data['price']) . ' đ' : '';
      $price_str     = (isset($data['price_str']) && $data['price_str'] != '')? $data['price_str'] : '';
      $pro_name = isset($data['name'])? $data['name'] : '';
      if($unit != '') $price .= '/' . $unit;
      if($price_str != '') $price = $price_str;
      if($unit != '') $price = '/' . $unit;
      
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
          'fontsize' => 5,
          'stretchtext' => 0
      );
      
      $count_page = 1;
      $tottal_product   = count($data) - 1;
      $count_page       = ceil($tottal_product / 90);
      
      /*
      $pdf->MultiCell(35, 1, $provider_name, 0, 'C', false, 1, 1, 1, true, 1);
      $pdf->write1DBarcode($barcode, 'C128', 1, 6, 35, 11, 0.4, $style, 'N');
      $pdf->MultiCell(35, 5, $price, 0, 'C', 0, 1, 1, 16, true, 0);
      */
      $position   = 0;
      for($page  = 0; $page < $count_page; $page++){
         // add a page
         $pdf->AddPage();
         for($i = 0; $i < 15; $i++){
            $y = ($i * 19) + 6;         
            for($j = 0; $j<6; $j++){
               $x = ($j * 33) + 6;
               $position++;
               
               if(isset($data[$position]) && !empty($data[$position])){
                  
                  $provider_name = isset($data[$position]['pvname'])? $data[$position]['pvname'] : '';
                  $barcode       = isset($data[$position]['barcode'])? $data[$position]['barcode'] : '';
                  $unit          = isset($data[$position]['unit'])? $data[$position]['unit'] : '';
                  $price         = (isset($data[$position]['price']) && $data[$position]['price'] > 0)? format_number($data[$position]['price']) . ' đ' : '';
                  $price_str     = (isset($data[$position]['price_str']) && $data[$position]['price_str'] != '')? $data[$position]['price_str'] : '';
                  $pro_name = isset($data[$position]['name'])? $data[$position]['name'] : '';
                  
                  if($price_str != '') $price = $price_str;
                  if($unit != '') $price .= '/' . $unit;
                  
                  $pdf->MultiCell(33, 1, $provider_name, 0, 'C', false, 1, $x, $y, true, 1);
                  $pdf->write1DBarcode($barcode, 'C128', $x, $y + 1, 33, 10, 0.4, $style, 'N');
                  if($pro_name != ''){
                     $height_proname   = 12;
                     $height_price     = 18;
                     
                     if(mb_strlen($pro_name,'UTF-8') <=22){
                        $height_proname = 12.5; 
                        $height_price     = 16;
                     } else {
                        if(mb_strlen($price) <= 22){
                           $height_price     = 18;
                        }
                     }
                     
                     $pdf->MultiCell(33, 5, $pro_name, 0, 'L', false, 1, $x + 1, $y + 10, true, 1);
                     if($price != ''){
                        $pdf->MultiCell(33, 5, $price, 0, 'R', 0, 1, $x, $y + 14.4, true, 1);
                     }
                  }else{
                     if($price != ''){
                        $pdf->MultiCell(33, 5, $price, 0, 'C', 0, 1, $x, $y + 11, true, 0);
                     }
                     
                  }
               
               }
               
            }
         }           
         
      }
      
      
      //Close and output PDF document
      return $pdf->Output('barcode.pdf', 'I');
   }
   
   
}