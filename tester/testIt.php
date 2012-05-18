<?php
/** Выдирает уникальные тв-строки из tv.txt относительно metadata/ok.txt
Строки, начинающиеся с --- считаютя уникальными всегда — названия категорий
*/

$src = file('tv.txt');
$src = array_unique($src);

$etalon = file ('metadata/ok.txt');
$etalon = array_unique($etalon);

foreach ($etalon as $key=>$val) {
    if (substr($val, 0, 3) === '---') {
            unset ($etalon[$key]);
    } else {
        $etalon[$key]=trim($val);
    }

}


file_put_contents('metadata/ok.txt', implode("\n", $etalon)."\n");

$retval ='';
foreach ($src as $sourceLine) {
    $sourceLine = trim ($sourceLine);
   if (substr($sourceLine, 0, 3) === '---') {
            $retval .="\n".$sourceLine."\n";
    } else {
	    if (in_array($sourceLine, $etalon)) continue;
	    $retval .= $sourceLine."\n";
   }
}

file_put_contents("new-unique.txt", $retval);