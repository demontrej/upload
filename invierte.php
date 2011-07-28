<?php
$path='D:\\software\\xampp\\htdocs\\magento\\';
$directorio=dir($path);
echo "Directorio ".$path.":<br><br>";
$array=array();
while ($archivo = $directorio->read())
{
    $array[] = $archivo."<br>";
}
$directorio->close();
sort($array);
print_r($array);

?>