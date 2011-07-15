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
//create_or_find_category('Perifericos',472);
//$category = Mage::getModel('catalog/category')->loadbyattribute('name','Toner');
//$category = Mage::getModel('catalog/category')->load(471);
//$category = Mage::getModel('catalog/category')->load(472);
//echo  '<pre>';
//print_r($category);
//echo  '</pre>';

//if($category){
//echo 'nombre de la categoria: '.$category->getName().'</br>';
//echo 'su id es: '.$category->getId().'</br>';
//echo 'su Parent_id es: '.$category->getParentId().'</br>';
//echo 'su Path es: '.$category->getPath().'</br>';
//}



              
//echo $id;
//echo '<pre>';
//print_r($category);
//echo '</pre>';

//create_category('123',NULL);


//recore las categoria las crea y busca nuevas categoria
$cat_product_count=array_categorias('GM_ES_C_Product20110629.txt');
$parent_id_level1=2;
foreach($cat_product_count as $cat_1=>$catnivel_2)
{
    // almacenamos las categorias o preguntamos y estan dentro de la base de datos
    echo 'nivel 1 :'. utf8_decode($cat_1).'</br>';
    $parent_id_level2=create_or_find_category(utf8_encode($cat_1),$parent_id_level1);
    
    foreach($catnivel_2 as $cat_2=>$catnivel_3)
    {
        //almacenamos las categorias o preguntamos y estan dentro de la base de datos
        if($cat_2!=$cat_1)
        {
            echo '--------------- nivel 2 :'. utf8_decode($cat_2).'</br>';
            $parent_id_level3=create_or_find_category(utf8_encode($cat_2),$parent_id_level2);
            
        }
        foreach($catnivel_3 as$cat_3 =>$cantidad)
        {
            if($cat_2!=$cat_3)
            {
                //almacenamos las categorias o preguntamos y estan dentro de la base de datos
                echo '---------------------- nivel 3 :'. utf8_decode($cat_3);
                echo create_or_find_category(utf8_encode($cat_3),$parent_id_level3);
            }
            echo '='.$cantidad;
            echo '</br>';
        }
    }


}
function create_or_find_category($name, $parent_id)
{
    $category = Mage::getModel('catalog/category')->loadByAttribute('name',$name);
    if($category)
    {
        echo 'ya existe la categoria su id es: '. $category->getId();
        if($category->getParentId()!=$parent_id)
        {
            create_category($name,$parent_id);
        }
        else
        {
            return $category->getId();
        }
        
    }
    else
    {
        return create_category($name,$parent_id);
    }
}
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
            $parent = $categoria_padre->getPath().'/'.$category->getId();
            echo 'si existe: '.$parent;
        }
        else
        {
            $parent = $categoria_padre->getPath();
            echo 'si no existe: '.$parent;
        }
    }
    else
    {
        if($existe){$parent = '1/2/'.$category->getId();echo 'por aca1';}
        else {$parent = '1/2';echo 'por aca 2';}
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