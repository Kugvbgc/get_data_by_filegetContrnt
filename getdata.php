<?php
$html=file_get_contents('https://www.dsebd.org/');
libxml_use_internal_errors(true);
$dom= new DOMDocument;
$dom->loadHTML($html);
libxml_clear_errors();

//print_r($dom);

$xParth= new DOMXPath($dom);
$all_hi =$xParth->query('//a[contains(@class,"abhead")]');



foreach($all_hi as $Item){
    echo $Item->textContent;
    echo '<br>';
    echo '<br>';

}
