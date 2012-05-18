<?php
define ('CAT_DIR','./CAT/');
define ('OUT_DIR', './OUT/');

$cat_files = readCatFiles(CAT_DIR);

function readCatFiles ($dir){
    $results = array();
    $contents = scandir ($dir);
    foreach ($contents as $k=>$v) {
        if ($v{0} === '.') {
                unset ($contents[$k]);
                continue;
        }
        // $contents[$k] = convert_cyr_string($v, 'd', 'w');
    }
    return $contents;
}

function repackTransArray(){
    $transArray = file_get_contents(OUT_DIR.'changeList.dat.txt');
    if (!strlen($transArray)) die('changelist not prepared');
    $transArray = unserialize($transArray);
    $t = Array();
    $i = 1;
    foreach ($transArray as $subarray) {
        $t[$i++] = $subarray;
    }
    return $t;
}

$tr = repackTransArray();

foreach ($cat_files as $cat) {
        $data = file_get_contents( CAT_DIR.$cat );
        $data = preg_split('#\r\n\r\n#', $data);

        foreach ($data as $index=>$subdata) {
            $hinter = substr($subdata, 0, 4);
            $hinter = strtolower($hinter);
            $replace_index=null;
            switch($hinter) {
                case "поне": $replace_index = 1; break;
                case "втор": $replace_index = 2; break;
                case "сред": $replace_index = 3; break;
                case "четв": $replace_index = 4; break;
                case "пятн": $replace_index = 5; break;
                case "субб": $replace_index = 6; break;
                case "воск": $replace_index = 7; break;
            }
            if ($replace_index !==null) {
                 $t = strtr($subdata, $tr[$replace_index]);
                 if (is_array($t));
                 $data[$index] = $t;
                 $subdata = $t;
       /** Второй проход для захвата переползших на следующий день программ */
                 if ($replace_index !== 1) {
                     $t = strtr($subdata, $tr[$replace_index-1]);
                     if (is_array($t));
                     $data[$index] = $t;
                     $subdata = $t;
                 }
                 if ($replace_index !== 7) {
                     $t = strtr($subdata, $tr[$replace_index+1]);
                     if (is_array($t));
                     $data[$index] = $t;
                 }

            }


    }





        $data = implode("\r\n\r\n", $data);
        file_put_contents(OUT_DIR.$cat, $data);

}

exit();
