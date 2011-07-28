<?php
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
function array_categorias($archivo)
{
echo("Archivo de categorias cargado\n");
if (($handle = fopen($archivo, 'r')) !== FALSE) {
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

function elimina_categoria_hijos($id)
{
    $category = Mage::getModel('catalog/category')->load($id);
    if($category->haschildren()) {
        $hijos=explode(",",$category->getChildren());
        //print_r($hijos); 
        foreach($hijos as $id_hijo);
        {
            $category = Mage::getModel('catalog/category')->load($id_hijo);
            elimina_categoria_hijos($category->getId());
            try{
                $category->delete();
                echo $name.' ha sido eliminado con exito!</br>';
            }
            catch (Exception $e){
                echo $e->getMessage();
            }
        }
    }
    else{
        echo $id." no tiene hijos</br>";
        return true;
    }
}
function elimina_categoria($name)
{
    $category = Mage::getModel('catalog/category')->loadByAttribute('name',$name);
    if ($category){
        $name= $category->getName();
        elimina_categoria_hijos($category->getId());
        try{
            $category->delete();
            echo $name.' ha sido eliminado con exito!</br>';
        }
        catch (Exception $e){
            echo $e->getMessage();
        }
    }
    else
    {
        echo $name.' no existe</br>';
    }
}  
// crear categorias con un nombre y parent_id a donde pertenecer
function crea_categoria($name, $parent_id)
{
    $category= Mage::getModel('catalog/category');
    $categoria_padre = Mage::getModel('catalog/category')->load($parent_id);
    $parent = $categoria_padre->getPath();
    $subcategory['name']= $name;
    $subcategory['descriptión']= $name;
    $subcategory['path']= $parent;
    $subcategory['is_active'] = 1;
    $category->addData($subcategory);
    try {
          $category->save();
          echo "La categoria ".$category->getName()." ha sido creada con exito! su id. es".$category->getId()."</br>";
          $categoria_nueva = Mage::getModel('catalog/category')->load($category->getId());          
          return $category->getId();
    }
    catch (Exception $e){
    echo $e->getMessage();
    }
}
function busca_categoria($name)// adicionar el parent id
{
     $category = Mage::getModel('catalog/category')->loadByAttribute('name',$name);
     
     if($category) 
     {
         echo "La categoria ".$category->getName()." existe! su id. es ".$category->getId()."</br>";
         return $category->getId();
     }
     else
     {
         return NULL;
     }
}
function busca_o_crea_categoria($name,$parent_id) 
{
    //verificamos si existe si existe devoldemos el id y por el contrario creamos la categor
    
    $id=busca_categoria($name);    
    if($id)
    {
        return $id;
    }
    else
    {
        return crea_categoria($name, $parent_id);       
    }
}
require_once('D:\software\xampp\htdocs\magento\app\Mage.php');
// para el servidor
//require '../app/Mage.php';
//$app = Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$app = Mage::app('default');
//recore las categoria las crea y busca nuevas categoria
$cat_product_count=array_categorias('GM_ES_C_Product20110726.txt');

echo '<pre>';
print_r($cat_product_count);
echo '</pre>';
$padreid_nivel1=2;
foreach($cat_product_count as $cat_1=>$catnivel_2)
{
    // almacenamos las categorias o preguntamos y estan dentro de la base de datos
    echo 'nivel 1 :'.$cat_1.'</br>';
    //$parent_id_level2=create_or_find_category(utf8_encode($cat_1),$parent_id_level1);
    $padreid_nivel2=busca_o_crea_categoria($cat_1,$padreid_nivel1); 
    
    foreach($catnivel_2 as $cat_2=>$catnivel_3)
    {
        //almacenamos las categorias o preguntamos y estan dentro de la base de datos
        //if($cat_2!=$cat_1)
        //{
            echo '--------------- nivel 2 :'.$cat_2.'</br>';
            //$parent_id_level3=create_or_find_category(utf8_encode($cat_2),$parent_id_level2);
            $padreid_nivel3=busca_o_crea_categoria($cat_2,$padreid_nivel2); 
            
        //}
        
        foreach($catnivel_3 as$cat_3 =>$cantidad)
        {
//            if($cat_2!=$cat_3)
//            {
                //almacenamos las categorias o preguntamos y estan dentro de la base de datos
                echo '---------------------- nivel 3 :'.$cat_3;
               // echo create_or_find_category(utf8_encode($cat_3),$parent_id_level3);
                busca_o_crea_categoria($cat_3,$padreid_nivel3); 
            //}
            echo '='.$cantidad;
            echo '</br>';
        }
    }


}


?>