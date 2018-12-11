<?php

function clean_post_data($data){
    $data = strip_tags($data);
    $data = strtolower($data);
    $data = preg_replace('~[^a-z0-9 \x80-\xFF]~i', "",$data); 
    return $data;
}

$input_string = clean_post_data($_GET['value']);
$value = explode(" ", $input_string);
$xml=simplexml_load_file("yandex.xml") or die("Error: Cannot create object");
$final=array();

function countOffersInCat($xmlstring, $catid) {
    $counter = 0;
    $minPrice = PHP_INT_MAX;
    foreach ($xmlstring->shop->offers->offer as $off) {
        if(intval($off->categoryId) === intval($catid)){
            $counter++;
            if (intval($off->price) < $minPrice) {
                $minPrice = intval($off->price);
            }
        }
    }
    if($counter === 0) $minPrice = '';
    $result['count'] = $counter;
    $result['minprice'] = $minPrice;
    return $result;
}

function searchById($xmlstring, $value) {
    $id_search_result = array();
    foreach ($xmlstring->shop->categories->category as $cat) {
        if(intval($cat['id']) === intval($value)){
            $result['type'] = 'category';
            $result['name'] = (string)$cat;
            $cat_addition = countOffersInCat($xmlstring, intval($cat['id']));
            $result['count'] = $cat_addition['count'];
            $result['minprice'] = $cat_addition['minprice'];

            array_push($id_search_result, $result);
            break;
        }
        $result = null;
    }
    foreach ($xmlstring->shop->offers->offer as $off) {
        if(intval($off['id']) === intval($value)){
            $result['type'] = 'offer';
            $result['name'] = (string)$off->name;
            $result['pic'] = (string)$off->picture;
            $result['price'] = (string)$off->price;
            $result['available'] = (string)$off['available'];
            array_push($id_search_result, $result);

            break;
        }
        $result = null;
    }

    return $id_search_result;
}

function searchByString($xmlstring, $values){
    $id_search_result = array();
    foreach ($xmlstring->shop->categories->category as $cat) {
        $result=array();
        $rel = 0;
        foreach ($values as $value) {
            $pos = mb_stripos((string)$cat, (string)$value);
            if ($pos !== false) {
                $rel++;
            }
        }
        if ($rel > 0){
            $result['type'] = 'category';
            $result['name'] = (string)$cat;
            $result['catid'] = (string)$cat["id"];
            $cat_addition = countOffersInCat($xmlstring, intval($cat['id']));
            $result['count'] = $cat_addition['count'];
            $result['minprice'] = $cat_addition['minprice'];
            $result['rel'] = $rel;
            echo json_encode($result)."<br>"; 
        }     
        $result=null;   
    }

    foreach ($xmlstring->shop->offers->offer as $off) {
        $result=array();
        $rel = 0;
        foreach ($values as $value) {
            $pos = mb_stripos((string)$off->name, (string)$value);
            if ($pos !== false) {
                $rel++;
            }
        }
        if ($rel > 0){
            $result['type'] = 'offer';
            $result['name'] = (string)$off->name;
            $result['pic'] = (string)$off->picture;
            $result['price'] = (string)$off->price;
            $result['available'] = (string)$off['available'];
            $result['rel'] = $rel;
            echo json_encode($result)."<br>"; 
        }   
    }
}

searchByString($xml, $value);
echo count($value);
if (count($value) === 1 && is_numeric($value[0])) {
    $id_search = searchById($xml, $value[0]);
    foreach ($id_search as $res) {
        array_push($final, $res);
    }
}

$json = json_encode($final); 

echo $json;
?>

