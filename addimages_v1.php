<?php

require 'D:\software\xampp\htdocs\magento\app\Mage.php';
//require '../app/Mage.php';
$app = Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
//$app = Mage::app('default');
$linksimages=linksimages_array('TDES_mapping_rich.txt');
$productomagento= Mage::getModel('catalog/product');
foreach($linksimages as $sku => $link)
{
    echo 'estado de la memoria: '.memory_get_usage() . '</br>';
    $product_id = Mage::getModel('catalog/product')->getIdBySku($sku);
    if($product_id){
        if(!verificaimagenvinculada($product_id)){
            $productomagento ->load($product_id);
            echo $productomagento->getImageUrl();

            save_image($link,basename($link));
            //save_image($link,$sku.'.jpg');
            $productomagento->addImageToMediaGallery(Mage::getBaseDir() . DS . basename($link), array('image', 'thumbnail', 'small_image'), false, false);
            try {
                echo  $sku.' imagen almacenada con exito!</br>';
                $productomagento->save();
            }
            catch (Exception $ex) {
                    echo '<pre>'.$productomagento['sku'].$ex.'</pre>';
            }
            unlink(basename($link));
        }
        else
        {
            echo  $sku.' ya posee una imagen vinculada!</br>';
        }

    }
    else
    {
        echo $sku.'a�n no esta dentro de la tienda</br>';
    }
}
function verificaimagenvinculada($product_id)
{
    $db = Mage::getModel('core/resource')->getConnection('core_write');
    //consulta para el local
    $query="SELECT `value_id` FROM `catalog_product_entity_media_gallery` WHERE `entity_id` =".$product_id;
    $result = $db->query($query);
    $vinculacion = $result->fetchAll();
    if($vinculacion){
        return true;
    }
    else{
        return false;
    }
}
function save_image($inPath,$outPath)
{ //Download images from remote server
    $in=    fopen($inPath, "rb");
    $out=   fopen($outPath, "wb");
    while ($chunk = fread($in,8192))
    {
        fwrite($out, $chunk, 8192);
    }
    fclose($in);
    fclose($out);
    unset($in);
    unset($out);
}
function linksimages_array($archivo)
{
    $arrayaux=array();
    $sw=false;
    if (($handle = fopen($archivo, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 2400,"	")) !== FALSE) {
        if($sw==true){
        // almacenamos en el array los sku y los links de las imagenes
        $arrayaux[$data[0]]= $data[4];
        }
        $sw=true;
    }
    fclose($handle);
    }
    echo 'archivo de links de imagenes caragado</br>';
    return $arrayaux;
}

?>