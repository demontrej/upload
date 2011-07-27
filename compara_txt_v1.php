<?php
function arrayprecio($archivo)
{
    $arrayaux=array();
    if (($handle = fopen($archivo, "r")) !== FALSE) { 
    while (($data = fgetcsv($handle, 2400,";")) !== FALSE) { 
        $arrayaux[$data[0]] = $data[5];
    } 
    fclose($handle);
    unset($handle);
    }
    echo 'archivo de precios cargado...</br>';
    return $arrayaux;
}
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
$archivo_nuevo="GM_ES_C_Product20110723.txt";
$archivo_antiguo="GM_ES_C_Product20110629.txt";
$arrayantiguo=crea_arrayauxantiguo($archivo_antiguo);
$nuevos=0;
$actualizar=0;
$total=0;
$sw=false;
$total_antiguo=count($arrayantiguo);
$prices_antiguo=arrayprecio('GM_ES_C_Prices20110629.txt');
$prices_nuevo=arrayprecio('GM_ES_C_Prices20110723.txt');
$total_nuevo=0;
echo 'estado de la memoria(inicio): '.memory_get_usage() . '</br>';
if (($handle = fopen($archivo_nuevo, "r")) !== FALSE) { 
    while (($data = fgetcsv($handle, 2400,";")) !== FALSE) { 
        if($sw){
            
            $total_nuevo++;
            $data[2] = iconv('latin1', 'utf-8', $data[2]);//descripción
            $data[15] = iconv('latin1', 'utf-8', $data[15]);//nivel 3 categoria
            $data[17] = iconv('latin1', 'utf-8', $data[17]);//nivel 3 categoria
            $data[19] = iconv('latin1', 'utf-8', $data[19]);//nivel 3 categoria
            //if($arrayantiguo[$data[0]])
            if(isset($arrayantiguo[$data[0]]))
            {
                $cambios=array();
                if($data[1]!=$arrayantiguo[$data[0]][0])
                {
                    $cambios[]='nombre';
                    echo 'n: '.$data[1].'- a: '.$arrayantiguo[$data[0]][0].'</br>';
                }
                if($data[2]!=$arrayantiguo[$data[0]][1])
                {
                    $cambios[]='descripción';
                    echo 'n: '.$data[2].'- a: '.$arrayantiguo[$data[0]][1].'</br>';
                }
                if($data[11]!=$arrayantiguo[$data[0]][2])
                {
                    $cambios[]='stock';
                    echo 'n: '.$data[11].'- a: '.$arrayantiguo[$data[0]][2].'</br>';
                }
                if($data[13]!=$arrayantiguo[$data[0]][3])
                {
                    $cambios[]='peso';
                    echo 'n: '.$data[13].'- a: '.$arrayantiguo[$data[0]][3].'</br>';
                }
                if($data[15]!=$arrayantiguo[$data[0]][4])
                {
                    $cambios[]='nivel 1';
                    echo 'n: '.$data[15].'- a: '.$arrayantiguo[$data[0]][4].'</br>';
                }
                if($data[17]!=$arrayantiguo[$data[0]][5])
                {
                    $cambios[]='nivel 2';
                    echo 'n: '.$data[17].'- a: '.$arrayantiguo[$data[0]][5].'</br>';
                }
                if($data[19]!=$arrayantiguo[$data[0]][6])
                {
                    $cambios[]='nivel 3';
                    echo 'n: '.$data[19].'- a: '.$arrayantiguo[$data[0]][6].'</br>';
                }
                if(isset($prices_nuevo[$data[0]])) {$precionuevo=$prices_nuevo[$data[0]];}
                else {$precionuevo=0;}
                if(isset($prices_antiguo[$data[0]])) {$precioantiguo=$prices_antiguo[$data[0]];}
                else {$precioantiguo=0;}
                
                if($precionuevo!=$precioantiguo)
                {
                    $cambios[]='precio';
                    echo 'n: '.$precionuevo.'- a: '.$precioantiguo.'</br>';
                    
                }
                unset($prices_antiguo[$data[0]]);
                if($cambios){
                    // $data[0]=key es sku
                    // $data[1]=0 es name
                    // $data[2]=1 es description
                    // $data[11]=2 es stock
                    // $data[13]=3 weight 
                    // $data[15]=4 nivel 1
                    // $data[17]=5 nivel 2
                    // $data[19]=6 nivel 3 define la categoria a la que pertenece 
                    $actualizar++;
                    echo $data[0].' '.join(', ', $cambios).' </br>';
                    echo 'estado de la memoria(centro): '.memory_get_usage() . '</br>';
                }
//                else
//                {
//                    echo 'sin cambios </br>';
//                }
                unset($arrayantiguo[$data[0]]);
            }
            else
            {
                $nuevos++;
                
                //echo 'nuevos</br>';
            }
        }
        $sw=true;        
    } 
    fclose($handle);
}    
echo 'actualizaciones = '.$actualizar.'</br>';
echo 'nuevos          = '.$nuevos.'</br>';
echo 'eliminaciones   = '.count($arrayantiguo).'</br>';
echo 'total archivo antiguo = '.$total_antiguo.'</br>';
echo 'total archivo nuevo = '.$total_nuevo.'</br>';
echo 'estado de la memoria(final): '.memory_get_usage() . '</br>';

?>
