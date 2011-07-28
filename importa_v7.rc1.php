<?php

$archivo = file_get_contents("GM_ES_C_Prices20110331.txt"); // Se obtiene el contenido de "archivo.BMP".
$voltear=strrev($archivo); // Se hace uso de la funcion strrev para invertir el archivo.
echo $voltear; // Se imprime el contenido del archivo inverso, esta linea se puede omitir.
$archivo = fopen ("invertido.txt", "w+"); // Se abre el archivo tal.bmp.
fwrite($archivo, $voltear); // Se escribe en el archivo tal.bmp el contenido invertido.
fclose($archivo); // Se cierra el archivo.
?>