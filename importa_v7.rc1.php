<?php

////////////////////////////////////////////////
//CREA ARRAY DE PRECIOS EN UN HASH
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
//si el producto no tiene precio arrojamos 0 pero en el sta
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
////////////////////////////////////////////////////////////////////////////////
//COMPARA DIFERENCIAS DE LOS TIER PRICE ACTULES Y NUEVOS DE UN PRODUCTO
////////////////////////////////////////////////////////////////////////////////
function comparadiferencias($tierexistentes, $tiernuevos)
{
    
    $tierauxiliar=array();
    foreach($tierexistentes as $tierexistente)
    {
        $tierexiste=false;        
        $tierpricerepetido=false;
        foreach($tiernuevos as $tiernuevo)
        {
            if($tierexistente['price_qty']==$tiernuevo['price_qty'])
            {
                if($tierexistente['price']==$tiernuevo['price'])
                {
                    $tierpricerepetido=true;                    
                }
                $tierexiste= true;
                break;
            }
        }
        if($tierpricerepetido)
        {
            
        }
        if(!$tierexiste)
        {
            $tierauxiliar[] =
            array(
            'website_id' => 0,
            'all_groups' => 0,
            'cust_group' => 1,
            'price_qty' => $tierexistente['price_qty'],
            'price' =>  $tierexistente['price']
            );
        }        
     }
     if(count($tierexistentes)==count($tierauxiliar))
     {
         return array(false,$tiernuevos);
     }
     else
     {
         return array(true, array_merge($tierauxiliar,$tiernuevos));
     }
     
}
////////////////////////////////////////////////////////////////////////////////
///////////////////////IMPORTA PRODUCTOS MAGENTO////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
require 'D:\software\xampp\htdocs\magento\app\Mage.php';
//require '../app/Mage.php';
$app = Mage::app('default');
$listado_productos= crea_arrayaux('GM_ES_C_Product20110331.txt');
$listado_precios= arrayprecio('GM_ES_C_Prices20110331.txt');
$categories = categorias_id();
// llamamos al modelo y crea un objeto
$productomagento= Mage::getModel('catalog/product');
echo '<pre>';
print_r($categories);
echo '</pre>';
foreach($listado_productos as $producto)
{
    echo 'estado de la memoria: '.memory_get_usage() . '</br>';
    echo $producto[0];
    $product_id = Mage::getModel('catalog/product')->getIdBySku($producto[0]);
    if($product_id)
    {   
        // carga el producto usando la id

        $productomagento ->load($product_id);
        //hacemops una consulta del stock
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productomagento)->getQty();
        //busca el precio en archivo de precios seg�n el sku
        $precio=product_price($producto[0],$listado_precios);
        $preciomag=number_format($productomagento['price'],2,',','');
        //echo $precio.'-'.$preciomag.'</br>';
        
        //verificamos si hay algun cambio
        $cambios=array();
        if($productomagento['name']!=  utf8_encode($producto[3])) {$cambios[]='nombre';}
        if($productomagento['description']!=  utf8_encode($producto[2])){$cambios[]='decripci�n';}
        if($productomagento['short_description']!=  utf8_encode($producto[1])){$cambios[]='decripci�n corta';}
        if($preciomag!=$precio){$cambios[]='precio';}
        if($stock!=$producto[4]){$cambios[]='stock';}
        if($producto[5]!=key($categories[utf8_encode($producto[5])])){$cambios[]='categoria';}
        //si tiene alg�n cambio actuliza
        if(count($cambios)>0){
            $productInfoData = $productomagento->getData();
            $productInfoData['name']= utf8_encode($producto[3]);
            $productInfoData['description']= utf8_encode($producto[2]);
            $productInfoData['short_description']= utf8_encode($producto[1]);
            $productInfoData['price']= $precio;
            if($precio==0)
            {
                $productInfoData['setStatus']=0; // disable
            }
            else
            {
                $productInfoData['setStatus']=1; // enable
            }
            $productomagento->setData($productInfoData);
            $stockDatamagento = $productomagento->getStockData();
            $stockDatamagento['qty'] = $producto[4];
            $stockDatamagento['is_in_stock'] = 1;
            $productomagento->setStockData($stockDatamagento);
            $productomagento->setCategoryIds(category_id($producto[5],$categories));
            try {
            //$productomagento->save();
            echo  '- Actualizado '. join(', ', $cambios).'</br>';
            }
                catch (Exception $ex) {
                echo '<pre>'.$productomagento['sku'].$ex.'</pre>';
                
            }  
            //liberamos espacio en memoria
            unset($productInfoData,$stockDatamagento,$precio,$preciomag,$haycambio);
            
        }
        else
        {
            echo '- no hay cambio</br>';
            
        }
            //unset ($product_id,$productomagento);
        

    }
    else
    {
        //crea el producto si no existe 
        $productomagentonew = Mage::getmodel('catalog/product');
        $productomagentonew->setSku($producto[0]);
        $productomagentonew->setName(utf8_encode($producto[3]));
        $productomagentonew->setDescription(utf8_encode($producto[2]));
        $productomagentonew->setShortDescription(utf8_encode($producto[1]));
        $productomagentonew->setAttributeSetId(4);
        $productomagentonew->setTypeId('simple');
        $productomagentonew->setWeight(1.0);
        $productomagentonew->setVisibility(4); // catalog, search
        $precio=product_price($producto[0],$listado_precios);
        $productomagentonew->setPrice($precio);
        if($precio==0)
        {
            $productomagentonew->setStatus(0); // disable
        }
        else
        {
            $productomagentonew->setStatus(1); // enabled
        }

        $stockData = $productomagentonew->getStockData();
        $stockData['qty'] = $producto[4];
        $stockData['is_in_stock'] = 1;
        $stockData['manage_stock'] = 1;
        $stockData['use_config_manage_stock'] = 0;
        //asigna la categoria 
        $productomagentonew->setCategoryIds(category_id(utf8_encode($producto[5]),$categories));
        $productomagentonew->setStockData($stockData);
        $productomagentonew->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        //verifica si el sku implicado posee tier prices
        try {
        $productomagentonew->save();
        echo ' - Guardado</br>';
        }
            catch (Exception $ex) {
            echo '<pre>'.$ex.'</pre>';
        } 
        
    }
    
   unset($listado_precios[$producto[0]],$producto,$stockData,$stockDatamagento,$productomagentonew,$tierprice);
    
}
?>