<?php

$word = 'стемминга';
include_once('stemmer_ru.php'); // подключение PHP-скрипта

// для обработки только определненых символов
function clean_post_data($data){
    $data = strip_tags($data);
    $data = strtolower($data);
    $data = preg_replace('~[^a-z0-9 \x80-\xFF\-]~i', "",$data); 
    return $data;
}

$value = array();
$input_string = clean_post_data($_GET['value']);
if ($input_string) {
    $values = preg_split('/\s+/', $input_string); // разбить строку на массив
    foreach ($values as $key => $val) {
        if (empty($val)) {
            unset($values[$key]);
        }
    }
    $value = array_values($values); // "переиндексация" массива, ключи идут по порядку
}

$xml = simplexml_load_file("yandex.xml") or die("Error: Cannot create object");
$final = array();

$string_search = searchByString($xml, $value);
// если ввод подходит под id , сперва выводим id
if (count($value) === 1 && is_numeric($value[0])) {
    $id_search = searchById($xml, $value[0]);
    foreach ($id_search as $res) {
        array_push($final, $res);
    }
}

foreach ($string_search as $strres) {
    array_push($final, $strres);
}

// обрезаем масств до 5 значений
$final_cut = array();
if(count($final) > 5){
    for ($i = 0; $i < 5; $i++) {
        $final_cut[$i] = $final[$i];
    }
}
else {
    for ($i = 0; $i < count($final); $i++) {
        $final_cut[$i] = $final[$i];
    }
}

$json = json_encode($final_cut, JSON_UNESCAPED_UNICODE); 

echo $json;

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
    if($counter === 0) $minPrice = 'Нет товаров';
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
    $ru = new Stemmer_RU; // создание объекта
    foreach ($xmlstring->shop->categories->category as $cat) {
        $result = array();
        $rel = 0;
        foreach ($values as $value) {
            $value_stem = $ru->getWordBase($value);
            $pos = mb_stripos((string)$cat, (string)$value_stem);
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
            array_push($id_search_result, $result);
            
        }
        $result=null;   
    }

    foreach ($xmlstring->shop->offers->offer as $off) {
        $result=array();
        $rel = 0; // количество попаданий
        foreach ($values as $value) {
            $value_stem = $ru->getWordBase($value);
            $pos = mb_stripos((string)$off->name, (string)$value_stem);
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
            array_push($id_search_result, $result);
        }   
    }

    // сортирровка по релевантности - rel
    function cmp($a, $b) { 
        return intval($a["rel"]) < intval($b["rel"]);
    }
    usort($id_search_result, "cmp"); 

    return $id_search_result;
}
?>

