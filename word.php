<?
function dump($what){
    echo '<pre>'; 
        print_r($what); 
    echo '</pre>';
};

function get_data($url){
    return json_decode(file_get_contents($url), true);
};
$url = "datas/uz.json";
// $url = "datas/uz.json";
// $url = "datas/uz.json";
dump(get_data($url));