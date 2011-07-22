<?php
function crea_arrayauxantiguo($archivo)
{
    ////////////////////////////////////////////////
    //introduce las categorias y subcategorias dentro de un array auxiliar
    ////////////////////////////////////////////////
    $arrayaux=array();
    $sw=false;
    if (($handle = fopen($archivo, "r")) !== FALSE) { 
    while (($data = fgetcsv($handle, 2400,";")) !== FALSE) { 
        if($sw==true){
            $data[2] = iconv('latin1', 'utf-8', $data[2]);//descripción
            $data[15] = iconv('latin1', 'utf-8', $data[15]);//nivel 3 categoria
            $data[17] = iconv('latin1', 'utf-8', $data[17]);//nivel 3 categoria
            $data[19] = iconv('latin1', 'utf-8', $data[19]);//nivel 3 categoria
            $arrayaux[$data[0]]= array($data[1],$data[2],$data[11],$data[13],$data[15],$data[17],$data[19]);
            // $data[0]=key es sku
            // $data[1]=0 es name
            // $data[2]=1 es description
            // $data[11]=2 es stock
            // $data[13]=3 weight 
            // $data[15]=4 nivel 1
            // $data[17]=5 nivel 2
            // $data[19]=6 nivel 3 define la categoria a la que pertenece 
                
        }
        $sw=true;        
    } 
    fclose($handle);
    unset($handle);
    }
    echo 'archivo de productos cargado...</br>';
    return $arrayaux;
}
$archivo_nuevo="GM_ES_C_Product20110629.txt";
$archivo_antiguo="GM_ES_C_Product20110331.txt";
$arrayantiguo=crea_arrayauxantiguo($archivo_antiguo);
$nuevos=0;
$actualizar=0;
$total=0;
$sw=false;
if (($handle = fopen($archivo_nuevo, "r")) !== FALSE) { 
    while (($data = fgetcsv($handle, 2400,";")) !== FALSE) { 
        if($sw){
            
            if($arrayantiguo[$data[0]])
            {
                if($data[1]!=$data[0] or $data[2]!=$data[1] or $data[11]!=$data[2] or
                   $data[13]!=$data[3] or $data[15]!=$data[4] or $data[17]!=$data[5] or
                   $data[19]!=$data[6]){
                    // $data[0]=key es sku
                    // $data[1]=0 es name
                    // $data[2]=1 es description
                    // $data[11]=2 es stock
                    // $data[13]=3 weight 
                    // $data[15]=4 nivel 1
                    // $data[17]=5 nivel 2
                    // $data[19]=6 nivel 3 define la categoria a la que pertenece 
                    $actualizar++;
                }
                unset($arrayantiguo[$data[0]]);
            }
            else
            {
                $nuevos++;
            }
        }
        $sw=true;        
    } 
    fclose($handle);
}    
echo 'actualizaciones = '.$actualizar.'</br>';
echo 'nuevos          = '.$nuevos.'</br>';
echo 'eliminaciones   = '.count($arrayantiguo).'</br>';


?>
