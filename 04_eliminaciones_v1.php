<?php
////////////////////////////////////////////////
//crea un array de todos los sku nuevos
////////////////////////////////////////////////
function array_skuproductos($archivo)
{
    $arrayaux=array();
    $sw=false;
    if (($handle = fopen($archivo, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 2400,";")) !== FALSE) {
        if($sw==true){

        $arrayaux[]= $data[0];

        
        }
        $sw=true;
        }
    fclose($handle);
    unset($handle);
    }
    echo 'archivo de productos cargado...</br>';
    return $arrayaux;
}
function allsku_bd()
{
    $db = Mage::getModel('core/resource')->getConnection('core_write');
    //consulta para el local
    $result = $db->query("SELECT  `sku` FROM  `catalog_product_entity` ");
    //consulta para la tienda
    //$result = $db->query("SELECT  `sku` FROM  `macatalog_product_entity` ");

    if($result) {
        
        $allskus_bd = $result->fetchAll();
        $skus=array();
        foreach($allskus_bd as $sku)
        {
            $skus[]=$sku['sku'];
        }
        return $skus;
    }
    else
    {
        echo 'no hay resultados';
        return NULL;
    }


}
//require 'D:\software\xampp\htdocs\magento\app\Mage.php';
require '../app/Mage.php';
//$app = Mage::app('default');
$app = Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

//leemos el nuevo txt para verificar si hay nuevos
$products_news= array_skuproductos('GM_ES_C_Product20110331.txt');
//obtenemos todos sku actuales
$products_olds= allsku_bd();
//obtenemos los sku que no se encuentran dentro del nuevo txt
$productos_borrar= array_diff($products_olds,$products_news);
$productos_borrar_atras=array_reverse($productos_borrar);
//echo print_r($productos_borrar).' - '.count($productos_borrar) .'</br>';
//eliminar un producto por el sku
foreach($productos_borrar_atras as $sku)
{
    $productId = Mage::getModel('catalog/product')->getIdBySku($sku);
    if ($productId){
        $product = Mage::getModel('catalog/product')
        ->setStoreId(Mage::app()
        ->getStore()
        ->getId())
        ->load($productId);
        try {
            $product->delete();
        echo $sku.' - producto eliminado';
            }
                catch (Exception $ex) {
                echo '<pre>'.$sku.' - '.$ex.'</pre>';
                
            }  
    }  
    else
    {
        echo $sku.'-no existe el producto';
    }
}
?>

