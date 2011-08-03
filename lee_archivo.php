<?php
function leer_archivos()
{
//Open images directory
$dir = @ dir("D:\\software\\xampp\\htdocs\\magento\\");
//List files in images directory
$zip=array();
while (($file = $dir->read()) !== false)
  {
    if(preg_match('/.zip/', $file))
    {
        $zip[]= $file;
    }
  //echo "filename: " . $file . "<br />";
  }

$dir->close();
rsort($zip);
$zipo=str_replace('_','/',$zip[0]);
$zipdate=basename($zipo,".zip");
return(array($zip[0],$zipdate));
}

$zip=leer_archivos();
echo $zip[0].'</br>';
echo $zip[1].'</br>';
leerfile();

//decomprimir zip
//exec('unzip TD_ES625074_20110331.zip');

//basename($link);


//
//leer txt, escribir txt
//if( txt fecha de zip es mas reciente que fecha txt)
//actualizamos las categorias
//actualizamos fecha de categorias en txt
//else
//no hace nada
//---------------------
//leer archivo zip (conseguir la fecha)
//comparar si
function leerfile(){
    // archivo txt
    // iniciamos contador y la fila a cero
    if (($handle = fopen('actualizaciones.txt', "r")) !== FALSE) {
        $sw=false;
        while (($data = fgetcsv($handle, 2400,"	")) !== FALSE) {
            if($sw)
            {
                echo 'Categorias:    '.$data[0].'<br/>';
                echo 'Productos:     '.$data[1].'<br/>';
                echo 'Tierprice:     '.$data[2].'<br/>';
                echo 'Eliminaciones: '.$data[3].'<br/>';
                echo 'Imagenes:      '.$data[4].'<br/>';
            }
            $sw=true;
            
        }
    fclose($handle);
    }
}
function escribirfile()
{
    if (($handle = fopen('actualizaciones.txt', "r")) !== FALSE) {
        
        while (($data = fgetcsv($handle, 2400,"	")) !== FALSE) {
            echo 'Categorias:    '.$data[0].'<br/>';
            echo 'Productos:     '.$data[1].'<br/>';
            echo 'Tierprice:     '.$data[2].'<br/>';
            echo 'Eliminaciones: '.$data[3].'<br/>';
            echo 'Imagenes:      '.$data[4].'<br/>';
        }
    fclose($handle);
    }
}

?>