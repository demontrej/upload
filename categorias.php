<?php
function array_categorias($archivo)
{
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
return $cat_product_count;
}

require_once('D:\software\xampp\htdocs\magento\app\Mage.php');

// para el servidor
//require '../app/Mage.php';
$app = Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
//echo("categorias leidas");
//echo 'la categoria por defecto'. Mage::app()->getStore()->getRootCategoryId();

//$category = Mage::getModel('catalog/category')->load(641);
//echo $category->getId();
//echo $category->getName();
//echo $category->getParentId();
//
create_category('jodas2',471);
create_category('jodas3',471);
create_category('jodas4',471);
//$category = Mage::getModel('catalog/category')->loadByAttribute('name', );
//if($category){
//echo 'ruta de la categoria: '.$category->getPath().'</br>';
//echo 'ruta de la categoria: '.$category->getParentId().'</br>';}
//else
//echo 'no encontrado';


              
//echo $id;
//echo '<pre>';
//print_r($category);
//echo '</pre>';

//create_category('123',NULL);


//recore las categoria las crea y busca nuevas categoria
//foreach($cat_product_count as $cat_1=>$catnivel_2)
//{
//    // almacenamos las categorias o preguntamos y estan dentro de la base de datos
//    echo 'nivel 1 :'. utf8_decode($cat_1).'</br>';
//    foreach($catnivel_2 as $cat_2=>$catnivel_3)
//    {
//        //almacenamos las categorias o preguntamos y estan dentro de la base de datos
//        if($cat_2!=$cat_1)
//        {
//            echo '--------------- nivel 2 :'. utf8_decode($cat_2).'</br>';
//        }
//        foreach($catnivel_3 as$cat_3 =>$cantidad)
//        {
//            if($cat_2!=$cat_3)
//            {
//                //almacenamos las categorias o preguntamos y estan dentro de la base de datos
//                echo '---------------------- nivel 3 :'. utf8_decode($cat_3);
//            }
//            echo '='.$cantidad;
//            echo '</br>';
//        }
//    }
//
//
//}
//function busca_categoria($nombrecat,$parentid)
//{
//    
//}

//CREA LA CATEGORIA INTEGRADA AL PARENT_ID
function create_category($name, $parent_id)
{
    echo $name.' - '.$parent_id;
    $category = Mage::getModel('catalog/category')->loadByAttribute('name',$name);
    //$category->setStoreId(0);
    $existe=false;
    if(!$category)
    {
        $category= Mage::getModel('catalog/category');
        
    }
    else
    {
        $existe=true;
    }
    
    if($parent_id)
    {
        $parent = '/'.$parent_id;
        $categoria_padre = Mage::getModel('catalog/category')->load($parent_id);
        if($existe)
        {
            $parent = $parent = $categoria_padre->getPath().'/'.$category->getId();
        
        }
        else
        {
            $parent = $categoria_padre->getPath();
        }
        
    }
    else
    {
        if($existe){$parent = '1/2/'.$category->getId();}
        else $parent = '1/2';
    }
    echo $parent.'<br>';
    $subcategory['name']= $name;
    $subcategory['path']= $parent;
    $subcategory['is_active'] = 1;

    $category->addData($subcategory);
    try {
          $category->save();
          echo "<p><strong>".$category->getName()." (".$category->getId().")</strong> - Category Added</p>";
          $categoria_nueva = Mage::getModel('catalog/category')->load($category->getId());
          echo 'La nuevacategoria es: '.$categoria_nueva->getName().'</br>';
          echo 'su ruta es: '.$categoria_nueva->getPath().'</br>';
    }
    catch (Exception $e){
    echo $e->getMessage();
    }
}

?>