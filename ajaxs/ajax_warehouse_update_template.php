<?
/**
 * update thông tin template
 */
include 'config.php';

$result  = array(
   'code'   => 500,
   'error'  => ''
);


$unit_parent      = getValue('unit_parent', 'int', 'POST', 1);
$unit_child       = getValue('unit_child', 'int', 'POST', 1);
$price_import     = getValue('price_import', 'int', 'POST', 0);
$price_export     = getValue('price_export', 'int', 'POST', 0);
$lo               = getValue('lo', 'str', 'POST', '');
$date_expires     = getValue('date_expires', 'str', 'POST', '');
$id               = getValue('id', 'int', 'POST', 0);

if($id <= 0){
   $result['error'] = 'Thông tin không có';
}else{
   
   $dataUpdate = array(
      $id => array(
         'usw_number_unit_parent'   => $unit_parent,
         'usw_number_unit_child'    => $unit_child,
         'usw_price_import'         => $price_import,
         'usw_price_export'         => $price_export,
         'usw_lo'                   => $lo,
         'usw_date_expires'         => $date_expires,
         'type_Update'              => 1,
         'usw_id'                   => $id
      )
   );
   
   
   $response   = $killWareHouse->update_template($dataUpdate);
   print_r($response);
   $result['code'] = $response['code'];
   $result['error'] = $response['error'];
   
}

echo json_encode($result);
exit();