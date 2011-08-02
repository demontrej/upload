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
        $producto_nuevo->setTaxClassId(0);
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
        unset($producto_nuevo);
}

function getcategoria_id($id)
{
    $db = Mage::getModel('core/resource')->getConnection('core_write');
    //consulta para el servidor
    //$query="SELECT  `category_id` FROM  `macatalog_category_product` WHERE  `product_id` =".$id;
    //consulta para el local
    $query="SELECT  `category_id` FROM  `catalog_category_product` WHERE  `product_id` =".$id;
    $result = $db->query($query);

    if($result) {
        
        $allcategorias = $result->fetchAll();
        return $allcategorias[0]['category_id'];
    }
    else
    {
        echo 'no hay resultados';
        return NULL;
    }


}

////////////////////////////////////////////////////////////////////////////////
///////////////////////IMPORTA PRODUCTOS MAGENTO////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
echo 'estado de la memoria(inicio script): '.memory_get_usage() . '</br>';
require 'D:\software\xampp\htdocs\magento\app\Mage.php';
//require '../app/Mage.php';
//$app = Mage::app('default');
$app = Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
//$listado_productos= crea_arrayaux('GM_ES_C_Product20110629.txt');
//echo 'estado de la memoria(cargar los productos en array): '.memory_get_usage() . '</br>';
$listado_precios= arrayprecio('GM_ES_C_Prices20110726.txt');
echo 'estado de la memoria(cargar los precios): '.memory_get_usage() . '</br>';
$categories = categorias_id();
echo 'estado de la memoria(cargamos las categorias): '.memory_get_usage() . '</br>';
// llamamos al modelo y crea un objeto para la actulizaciÃ³n
$productomagento=Mage::getModel('catalog/product');
$archivo='GM_ES_C_Product20110726.txt';

$numerocambios=0;
$sw=false;
$j=0;
if (($handle = fopen($archivo, "r")) !== FALSE) { 
    while (($data = fgetcsv($handle, 2400,";")) !== FALSE) { 
        if($j==1000){
            break;
        }        
        if($sw==true){
        echo 'estado de la memoria(centro): '.memory_get_usage() . '</br>';
        $data[2] = iconv('latin1', 'utf-8', $data[2]);
        $data[5] = iconv('latin1', 'utf-8', $data[5]);
        $data[19] = iconv('latin1', 'utf-8', $data[19]);
        //print_r($data);
        //echo 'estado de la memoria: '.memory_get_usage() . '</br>';
        $j++;
        echo $j.' - '.$data[0];
        //$product_id = Mage::getModel('catalog/product')->getIdBySku($data[0]);
        $product_id = $productomagento->getIdBySku($data[0]);
        
        if($product_id)
        {
            
            
	     $productomagento->load($product_id);
            
            $id_categoria=$categories[$data[19]];
            $precio=$listado_precios[$data[0]];
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productomagento)->getQty();
            //echo 'stock:'.$data[11].' - '.$stock.'</br>';
            echo '</br>';
//            $cambios['nombre']=false;
//            $cambios['descripcion']=false;
//            $cambios['precio']=false;
//            $cambios['stock']=false;
//            $cambios['categoria']=false;
//           
            $cambios=array();
            //$productInfoData = $productomagento->getData();
            if($productomagento['name']!=$data[5].' '.$data[1])
                {   echo 'nombre diferente</br>';
                    echo 'n: '.$data[5].' '.$data[1].'- a: '.$productomagento['name'].'</br>';
                    //$productInfoData['name']= $data[1];
                    $cambios['nombre']='nombre';
                }
            if($productomagento['description']!=$data[2])
                {
                    echo 'descripcion diferente';
                    echo 'n: '.$data[2].'- a: '.$productomagento['description'].'</br>';
                    $cambios['description']='descripcion';
                    //$productInfoData['description']= $data[2];
                }
            if($productomagento['price']!=str_replace(',','.',$precio)){
                    echo 'precio diferente';
                    echo 'n: '.$precio.'- a: '.$productomagento['price'].'</br>';
                    $cambios['precio']='precio';
                }
            if($stock!=$data[11]){
                    echo 'stock diferente';
                    echo 'n: '.$data[11].'- a: '.$stock.'</br>';
                    $cambios['stock']='stock';
                    
                }
            //$categoria=$productomagento->getCategoryIds($product_id);
            
            $cat_tienda=getcategoria_id($product_id);
            if($cat_tienda !=$id_categoria){
                    echo 'categoria diferente';
                    echo 'n: '.$id_categoria.'- a: '.$cat_tienda.'</br>';
                    $cambios[]='categoria';
                    //$productomagento->setCategoryIds('2');
                    echo 'hay cambios';

            }
	     //$cambios= 'algo';
            if($cambios)
            {
                $numerocambios++;
                //$productomagento->setData($productInfoData);
                $productoactualizar=Mage::getModel('catalog/product')->load($product_id);
                $productoactualizar->setName($data[5].' '.$data[1]);
                $productoactualizar->setDescription($data[2]);
                $productoactualizar->setPrice($precio);
                $productoactualizar->setShortDescription($data[1]);
                $productoactualizar->setTaxClassId(0);
                $productoactualizar->setWeight($data[13]);


                //$productInfoData['price']= $precio;
                    if($precio==0)
                    {
                        $productoactualizar->setStatus('0');
                        //$productInfoData['setStatus']=0; // disable
                    }
                    else
                    {
                        $productoactualizar->setStatus('1');
                        //$productInfoData['setStatus']=1; // enable
                    }
                $stockDatamagento = $productoactualizar->getStockData();
                $stockDatamagento['qty'] = $data[11];
                $stockDatamagento['is_in_stock'] = 1;
                $productoactualizar->setStockData($stockDatamagento);
                $productoactualizar->setCategoryIds($id_categoria);
                
                try {
                    $productoactualizar->save();
                    echo  '- Actualizado! '. join(', ', $cambios).'</br>';
                    unset($productoactualizar);
                }
                    catch (Exception $ex) {
                    echo '<pre>'.$productomagento['sku'].$ex.'</pre>';
                }
            }            
            else
            {
                echo  '- sin cambios!</br>';
            }
            //unset($productomagento,$stock,$cambios,$stockDatamagento,$categoria,$id, $precio, $id_categoria);
	     unset($productoactualizar,$$stockDatamagento);
            unset($listado_precios[$data[0]],$stock);

        }
        else
        {
            crea_producto($data,$listado_precios[$data[0]],$categories[$data[19]]);
            unset($listado_precios[$data[0]]);
        }  
        
        }
        $sw=true;      
        unset($data);
    } 
    fclose($handle);
}

echo 'estado de la memoria(fin script): '.memory_get_usage() . '</br>';
echo 'cambios = '.$numerocambios;

?>