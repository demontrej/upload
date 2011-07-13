<?php
require_once('D:\software\xampp\htdocs\magento\app\Mage.php');
//require '../app/Mage.php';
$app = Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

echo("Archivos de categorias cargado\n");
if (($handle = fopen('GM_ES_C_Product20110629.txt', 'r')) !== FALSE) {
    fgets($handle);
    while (($data = fgetcsv($handle, 2400, ';')) !== FALSE) {
        for ($i = 15; $i <= 19; $i += 2) {
            $data[$i] = iconv('latin1', 'utf-8', $data[$i]);
        }
        $cat_product_count[$data[15]][$data[17]][$data[19]] += 1;
    }
    fclose($handle);
}
echo("categorias leidas");

echo '<pre>';
print_r($cat_product_count);
echo '</pre>';
foreach($cat_product_count as $cat_1=>$catnivel_2)
{
    // almacenamos las categorias o preguntamos y estan dentro de la base de datos
    echo 'nivel 1 :'. utf8_decode($cat_1).'</br>';
    foreach($catnivel_2 as $cat_2=>$catnivel_3)
    {
        //almacenamos las categorias o preguntamos y estan dentro de la base de datos
        if($cat_2!=$cat_1)
        {
            echo '--------------- nivel 2 :'. utf8_decode($cat_2).'</br>';
        }
        foreach($catnivel_3 as$cat_3 =>$cantidad)
        {
            if($cat_2!=$cat_3)
            {
                //almacenamos las categorias o preguntamos y estan dentro de la base de datos
                echo '---------------------- nivel 3 :'. utf8_decode($cat_3);
            }
            echo '='.$cantidad;
            echo '</br>';
        }
    }


}
//echo $app->getStore()->getId();
//$parent = Mage::getModel('catalog/category')->setStoreId($store_id)->loadByName('name','Default Category');
echo Mage::app()->getStore()->getRootCategoryId();
?>