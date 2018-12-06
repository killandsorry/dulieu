<?
include '../classes/database.php';
include '../functions/functions.php';
include '../functions/file_functions.php';
include '../functions/date_functions.php';


$db_ex   = new db_query("SELECT * FROM datas WHERE dat_status = 0 LIMIT 50");
while($row = $db_ex->fetch()){
   
   $name=$row['dat_name'];
   $name = trim($name);
   $name = str_replace('  ', ' ', $name);
   $name_accent   = convert_utf82utf8($name);
   $name_accent   = removeAccent($name_accent);
   $name_accent   = strtolower($name_accent);
   
   $db_up   = new db_execute("INSERT IGNORE INTO data_temp(dat_name, dat_name_accent, dat_barcode, dat_unit, dat_unit_import, dat_specifi)
   VALUES('". replaceMQ($name) ."','". $name_accent ."','". $row['dat_barcode'] ."',". $row['dat_unit'] .",". $row['dat_unit_import'] .",". $row['dat_specifi'] .")");
   
   unset($db_up);
   
   $db_update  = new db_execute("UPDATE datas SET dat_status = 1 WHERE dat_id =" . $row['dat_id']);
   unset($db_update);
   echo $name . ' - ' . $name_accent . "<br>";
   
}
echo '<meta http-equiv="refresh" content="1;url=http://dulieu.com/test/read_data.php" />';