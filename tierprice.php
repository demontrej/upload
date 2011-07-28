<?php
////////////////////////////////////////////////
//introduce introduce los tier price en un array
////////////////////////////////////////////////
function tierprice_array($archivo)
{
    
    $arrayaux=array();
    $sw=false;
    if (($handle = fopen($archivo, "r")) !== FALSE) { 
    while (($data = fgetcsv($handle, 2400,";")) !== FALSE) { 
        if($sw==true){
                $arrayaux[$data[0]][$data[2]]= array(
                'website_id' => 0,
                'all_groups' => 0,
                'cust_group' => 1,
                'price_qty' => $data[2],
                'price' => $data[7]
                );
        }
        $sw=true;
    } 
    fclose($handle);
    }
    return $arrayaux;
}
function comparadiferencias($tiers_olds, $tiers_news)
{
    foreach($tiers_olds as $tier_actual)
    {
      //comparamos si existe la cantidad del producto con el mismo precio
      if($tiers_news[(int)$tier_actual['price_qty']] and str_replace(',','.',$tiers_news[(int)$tier_actual['price_qty']]['price'])==$tier_actual['price'])
      {
          // si existe borramos el tier price dentro del nuevo array
          unset($tiers_news[(int)$tier_actual['price_qty']]);
      }
    }
    return $tiers_news;
}

//require 'D:\software\xampp\htdocs\magento\app\Mage.php';
require '../app/Mage.php';
//$app = Mage::app('default');
$app = Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$tierpricesnews=tierprice_array('GM_ES_C_prices_QB20110726.txt');
$product=Mage::getModel('catalog/product');
foreach($tierpricesnews as $sku=>$tierpricenew)
{
    echo 'estado de la memoria: '.memory_get_usage() . '</br>';
    $productid=Mage::getModel('catalog/product')->getIdBySku($sku);
    if($productid){
    $product->load($productid);
    $tierpricesolds=$product->tier_price;
    $tiersadd=comparadiferencias($tierpricesolds, $tierpricenew);
    if($tiersadd)
    {
        $tierprice_new= array_merge_recursive($tierpricesolds,$tiersadd);
        $product->setTierPrice($tierprice_new);


        try {
        $product->save();
        echo $sku.' - precio multiple actualizado</br>';
        }
        catch (Exception $ex) {
            echo '<pre>'.$ex.'</pre>';
        }
    }
    else
    {
        echo $sku.' - no existe ningun cambio</br>';
    }
    }
    else
    {
        echo $sku.' - aún no existe el producto</br>';
    }
}


 
?>