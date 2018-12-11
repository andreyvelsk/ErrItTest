<?php

$value = $_GET['value'];
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
        }
        $result = null;
    }

    return $id_search_result;
}

function searchByString($xmlstring, $value){
    $id_search_result = array();
    foreach ($xmlstring->shop->categories->category as $cat) {
        $pos = mb_stripos((string)$cat, (string)$value);
        if ($pos !== false) {
            echo "Нашел '$value' в '$cat' в позиции $pos <br>";
        }
        $result = null;
    }

    foreach ($xmlstring->shop->offers->offer as $off) {
        $pos = mb_stripos((string)$off->name, (string)$value);
        if ($pos !== false) {
            echo "Нашел '$value' в '$off->name' в товаре $pos <br>";
        }
        $result = null;
    }
}

searchByString($xml, $value);

if (is_numeric($value)) {
    $id_search = searchById($xml, $value);
    foreach ($id_search as $res) {
        array_push($final, $res);
    }    
}

$json = json_encode($final); 

echo $json;

/*
[
{
    type: category,
    name: 'Испарители',
    offerscount: 32,
    minprice: 123,
},
{
    type: offer,
    name: 'Испаритель',
    pic: pic,
    price: 1232,
    available: true
}
]

*/
?>

