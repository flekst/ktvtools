<?php
  //include('AddEndTimes.php');
define ('OUT_DIR', './OUT/');
define ('CH_DIR', "./CH/");

$channels = readChannesNames(CH_DIR);

function readChannesNames ($dir){
    $results = array();
    $contents = scandir ($dir);
    foreach ($contents as $k=>$v) {
        if ($v{0} === '.') {
                unset ($contents[$k]);
                continue;
        }
//        $contents[$k] = convert_cyr_string($v, 'd', 'w');
        $contents[$k] = substr($contents[$k],0,-4);
    }
    return $contents;
}



$results = array();

class chRecord {
    var $ch;
    var $day;
    var $time;
    var $endtime;
    var $title;
    function __construct($ch, $day, $line) {
        $matches = array();
      //  $data = preg_match ('#^(\d\d\.\d\d)\s(.*)\($ch\)$#', $line, $matches);
        $count = $data = preg_match ('#^(\d\d\.\d\d)\s(.*)$#', $line, $matches);
        $this->time = $matches[1];
        $this->title = $matches[2];
        $this->day = $day;
        $this->endTime = '=ПОПРАВЬ=';
        $this->ch = $ch;
    }
    function update($end_time) {

    }
}

function getChFileName($ch) {
     $fn = convert_cyr_string($ch, 'w', 'd');
    return CH_DIR.$ch.'.txt';
}

function readChannel($chName) {
    $ch = $chName;
    $fn = getChFileName($chName);
    if (!file_exists($fn)) {
            return array();

    }
    $data = file ($fn);

    if (!count ($data) || !is_array($data)) { return array(); }
    $day = 1;
    array_shift($data);


    $retval = array();

    $i = 0;

    while (count($data)) {
        $line = array_shift($data);
        $line = trim($line);
        if (!strlen($line)) continue;

        switch (mb_substr(mb_strtolower($line, 'cp1251'), 0, 5) ) {
            case 'понед':  $day = 1; break;
            case 'вторн':  $day = 2; break;
            case 'среда':  $day = 3; break;
            case 'четве':  $day = 4; break;
            case 'пятни':  $day = 5; break;
            case 'суббо':  $day = 6; break;
            case 'воскр':  $day = 7; break;
            default:
                   $retval[$i] = new chRecord($ch, $day, $line);
                   if (isset ($retval[$i-1])) {
                       $retval[$i-1]->endTime = $retval[$i]->time;
                   }
                   $i++;
                   break;

        }
    }
  return $retval;
}

function createChangeList($sArray) {
    $results = array();
    foreach ($sArray as $sline) {
        $ch         = $sline->ch;
        $day        = $sline->day;
        $time       = $sline->time;
        $endtime    = $sline->endTime;
        $title      = $sline->title;

        $string = $title." ($ch)";
        $in = "$time $string";
        $out = "$time-$endtime $string";

        switch ($day) {
            case 1: $day = 'Понедельник'; break;
            case 2: $day = 'Вторник'; break;
            case 3: $day = 'Среда'; break;
            case 4: $day = 'Четверг'; break;
            case 5: $day = 'Пятница'; break;
            case 6: $day = 'Суббота'; break;
            case 1: $day = 'Воскресенье'; break;
        }
        if (!isset($results[$day])) $results[$day] = array();
        $results[$day]["\n".$in] = "\n".$out;
    }

    $results = serialize($results);
    return $results;
}

    $data = array();
foreach ($channels as $ch) {
    $result = readChannel($ch);
    if ( is_array($result) && count($result)) {
        $data = array_merge($data, $result);
    } else {
        echo "$ch error\n";
        var_dump($results);
        die();
    }

}
$serrialized = createChangeList($data);
file_put_contents(OUT_DIR.'changeList.dat.txt', $serrialized);