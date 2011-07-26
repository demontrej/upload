<?php

////////////////////////////////////////////////
//CREA ARRAY DE PRECIOS
////////////////////////////////////////////////
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
//BUSCA PRECIO DE PRODUCTO EN ARRAY

//
function product_price($sku, &$prices_array)
{
    if (array_key_exists($sku, $prices_array)) {
        return $prices_array[$sku];
    } else {
        return 0;
    }
}
//FUNCION PARA CREAR UN ARRAY AUXILIAR DE MAGENTO
function crea_arrayaux($archivo)
{
    ////////////////////////////////////////////////
    //introduce las categorias y subcategorias dentro de un array auxiliar
    ////////////////////////////////////////////////
    $arrayaux=array();
    $sw=false;
    if (($handle = fopen($archivo, "r")) !== FALSE) { 
    while (($data = fgetcsv($handle, 2400,";")) !== FALSE) { 
        if($sw==true){
            
        $arrayaux[]= array($data[0],$data[1],$data[2],$data[7],$data[11],$data[19]);
        // $data[0]=0 es short description es sku
        // $data[1]=1 es short description
        // $data[2]=2 es description
        // $data[7]=3 es name
        // $data[11]=4 es stock
        // $data[19]=5 es nombre de la ultima categoria
                
        }
        $sw=true;        
    } 
    fclose($handle);
    unset($handle);
    }
    echo 'archivo de productos cargado...</br>';
    return $arrayaux;
}
//crea un hash de con el nombre de categorias y sus ids
function categorias_id()
{
    $collection = Mage::getModel('catalog/category');
    $tree = $collection->getTreeModel(); 
    $tree->load();
    $ids=$tree->getCollection()->getAllIds();
    $array=array();
    foreach($ids as $id)
    {
        $cat=Mage::getModel('catalog/category');
        $cat->load($id);
        $array[$cat['name']]=$cat['entity_id'];
        
    }
    unset($tree);
    return $array;
}
function category_id($cat_name,$list_cat)
{
    if (array_key_exists($cat_name, $list_cat)) {
        return $list_cat[$cat_name];
    } else {
        return $list_cat['Default Category'];
    }
}
function crea_producto($datos,$precio,$categoria)
{
        $producto_nuevo = Mage::getmodel('catalog/product');
        //$producto_nuevo->setId($datos[0]);
        $producto_nuevo->setSku($datos[0]);
        $producto_nuevo->setName($datos[1]);
        $producto_nuevo->setDescription($datos[2]);
        //$producto_nuevo->setShortDescription(utf8_encode($producto[1]));
        $producto_nuevo->setAttributeSetId(4);
        $producto_nuevo->setTypeId('simple');
        $producto_nuevo->setWeight($datos[13]);
        $producto_nuevo->setVisibility(4); // catalog, search
        $producto_nuevo->setPrice($precio);
        
        if($precio==0)
        {
            $producto_nuevo->setStatus(0); // disable
        }
        else
        {
            $producto_nuevo->setStatus(1); // enabled
        }
        
        $stockData = $producto_nuevo->getStockData();
        $stockData['qty'] = $datos[11];
        $stockData['is_in_stock'] = 1;
        $stockData['manage_stock'] = 1;
        $stockData['use_config_manage_stock'] = 0;
        //asigna la categoria 
        $producto_nuevo->setCategoryIds($categoria);
        $producto_nuevo->setStockData($stockData);
        $producto_nuevo->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        //verifica si el sku implicado posee tier prices
        try {
        $producto_nuevo->save();
        echo ' - Guardado!!</br>';
        }
            catch (Exception $ex) {
            echo '<pre>'.$ex.'</pre>';
        } 
}
function actualiza_producto($id, $datos, $precio, $id_categoria){ 
    $productomagento= Mage::getModel('catalog/product');
    $productomagento->load($id);
    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productomagento)->getQty();
    echo '</br>';
    $cambios=array();
    //$productInfoData = $productomagento->getData();
    if($productomagento['name']!=$datos[1])
        {   echo 'nombre diferente</br>';
            echo 'n: '.$datos[1].'- a: '.$productomagento['name'].'</br>';
            $productomagento->setName($datos[1]);
            //$productInfoData['name']= $datos[1];
            $cambios[]='nombre';
        }
    if($productomagento['description']!=$datos[2])
        {
            echo 'descripcion diferente';
            echo 'n: '.$datos[2].'- a: '.$productomagento['description'].'</br>';
            $productomagento->setDescription($datos[2]);
            //$productInfoData['description']= $datos[2];
            $cambios[]='descripción';
        }
    if($productomagento['price']!=str_replace(',','.',$precio)){
            echo 'precio diferente';
            echo 'n: '.$precio.'- a: '.$productomagento['price'].'</br>';
            $cambios[]='precio';
            $productomagento->setPrice($precio);
            //$productInfoData['price']= $precio;
            if($precio==0)
            {
                $productomagento->setStatus('0');
                //$productInfoData['setStatus']=0; // disable
            }
            else
            {
                $productomagento->setStatus('1');
                //$productInfoData['setStatus']=1; // enable
            }
        }
    if($stock!=$datos[11]){
            echo 'stock diferente';
            echo 'n: '.$datos[11].'- a: '.$stock.'</br>';
            $cambios[]='precio';
            $stockDatamagento = $productomagento->getStockData();
            $stockDatamagento['qty'] = $datos[11];
            $stockDatamagento['is_in_stock'] = 1;
        }
    $categoria=$productomagento->getCategoryIds();
    if($categoria[0]!=$id_categoria){
            echo 'categoria diferente';
            echo 'n: '.$id_categoria.'- a: '.$categoria[0].'</br>';
            $cambios[]='categoria';
            $productomagento->setCategoryIds($id_categoria);
            //$productomagento->setCategoryIds('2');
            echo 'hay cambios';
                              
    }
    if($cambios)
    {
        //$productomagento->setData($productInfoData);
        $productomagento->setStockData($stockDatamagento);
        try {
            $productomagento->save();
            echo  '- Actualizado! '. join(', ', $cambios).'</br>';
        }
            catch (Exception $ex) {
            echo '<pre>'.$productomagento['sku'].$ex.'</pre>';
        }
    }
    else
    {
        echo  '- sin cambios!</br>';
    }
    unset($productomagento,$stock);
}

////////////////////////////////////////////////////////////////////////////////
///////////////////////IMPORTA PRODUCTOS MAGENTO////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
echo 'estado de la memoria(inicio script): '.memory_get_usage() . '</br>';
//require 'D:\software\xampp\htdocs\magento\app\Mage.php';
require '../app/Mage.php';
//$app = Mage::app('default');
$app = Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
//$listado_productos= crea_arrayaux('GM_ES_C_Product20110629.txt');
//echo 'estado de la memoria(cargar los productos en array): '.memory_get_usage() . '</br>';
$listado_precios= arrayprecio('GM_ES_C_Prices20110629.txt');
echo 'estado de la memoria(cargar los precios): '.memory_get_usage() . '</br>';
$categories = categorias_id();
echo 'estado de la memoria(cargamos las categorias): '.memory_get_usage() . '</br>';

// llamamos al modelo y crea un objeto para la actulización
//$modelo_producto=Mage::getModel('catalog/product');
//echo '<pre>';
//print_r($categories);
//echo '</pre>';
$archivo='GM_ES_C_Product20110629.txt';
$sw=false;
$j=0;
if (($handle = fopen($archivo, "r")) !== FALSE) { 
    while (($data = fgetcsv($handle, 2400,";")) !== FALSE) { 
//        if($j==100){
//            break;
//        }        
        if($sw==true){
            
        $data[2] = iconv('latin1', 'utf-8', $data[2]);
        $data[19] = iconv('latin1', 'utf-8', $data[19]);
        //print_r($data);
        //echo 'estado de la memoria: '.memory_get_usage() . '</br>';
        $j++;
        echo $j.' - '.$data[0];
        $product_id = Mage::getModel('catalog/product')->getIdBySku($data[0]);
        if($product_id)
        {
            //echo "existe!!</br>";
            //compara diferencias
            
            //actualiza_producto($modelo_producto,$product_id,$data,$listado_precios[$data[0]],$categories[$data[19]]);
            actualiza_producto($product_id,$data,$listado_precios[$data[0]],$categories[$data[19]]);

        }
        else
        {
            //crear producto
            //$j++;
            //echo "no existe</br>";
            crea_producto($data,$listado_precios[$data[0]],$categories[$data[19]]);
        }  
        
        }
        $sw=true;      
    } 
    fclose($handle);
}

echo 'estado de la memoria(fin script): '.memory_get_usage() . '</br>';

?>