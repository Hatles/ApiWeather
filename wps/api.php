<?php

try
{

    $lat = 43.72000122;
    $lon = 4.94999981;
    $timestamp = 1508497638;
    $maxWps = 15;

    if (isset($_GET['lat'])) {
        $lat = (floatval($_GET['lat']));
    }
    if (isset($_GET['lon'])) {
        $lon = (floatval($_GET['lon']));
    }
    if (isset($_GET['datetime'])) {
        $timestamp = (intval($_GET['datetime']));
    }

    $datetime = new DateTime('@'.$timestamp);
    $datetime->setTimezone(new DateTimeZone('Europe/Paris'));

    $yr = $datetime->format('Y');
    $mo = $datetime->format('m');
    $da = $datetime->format('d');

    $wunderwpsstring = sprintf('https://api-ak.wunderground.com/api/d8585d80376a429e/geolookup/alerts/lang:EN/units:english/bestfct:1/v:2.0/q/%s,%s.json', $lat, $lon);
    //$wunderwpsstring = sprintf('https://stationdata.wunderground.com/cgi-bin/stationdata?v=2.0&type=ICAO%%2CPWS&units=english&format=json&maxage=1800&maxstations=15&minstations=1&centerLat=%s&centerLon=%s&height=400&width=400&iconsize=2', $lat, $lon);
    //$wunderwpsstring = str_replace("&", "&amp;", $wunderwpsstring);
    //echo $wunderwpsstring;
    $json = file_get_contents($wunderwpsstring);
    //echo($json);
    $jsonObj = json_decode($json);
    $geolookup = $jsonObj->geolookup;
    $location = $geolookup->location;
    $nearby_weather_stations = $location->nearby_weather_stations->pws->station;
    //var_dump($jsonObj);
    //$nearby_weather_stations = $jsonObj->stations;
    $good = false;
    $wpsIndex = 0;

    while (!$good && $wpsIndex < sizeof($nearby_weather_stations) && $wpsIndex < $maxWps) {
        $wps = $nearby_weather_stations[$wpsIndex];
        var_dump($wps);
        $wpsid = $wps->id;
        $data = getWeatherData($wpsid, $yr, $mo, $da);
        if(dataHasWind($data))
        {
            $timeData = getTimeData($data, $timestamp);
            echo json_encode($timeData);
            $good = true;
        }

        $wpsIndex++;
    }

    if(!$good)
    {
        if(sizeof($nearby_weather_stations) > 0)
        {
            $wps = $nearby_weather_stations[0];
            $wpsid = $wps->id;
            $data = getWeatherData($wpsid, $yr, $mo, $da);
            $timeData = getTimeData($data, $timestamp);
            echo json_encode($timeData);
        }
        else
        {
            echo json_encode(array("error" => "no data found"));
        }
    }

} catch (Exception $e) {
    echo json_encode(array("error" => var_dump($e->getMessage())));
}   


//
//
function dataHasWind($data)
{
    return $data[1][5] >= 0;
}

function getTimeData($data, $timestamp)
{
    $i = 1;
    $done = false;
    $lastData = $data[$i];
    $lastDatetime = strtotime($weatherData[14]);
    
    while (!$done && $i+1 < sizeof($data)) {
        $i++;

        $weatherData = $data[$i];

        $weatherDatetime = strtotime($weatherData[14]);
        if($weatherDatetime > $timestamp)
        {
            $done = true;

            if($weatherDatetime - $timestamp < $timestamp - $lastDatetime)
            {
                $lastData = $weatherData;
            }
        }
        else
        {
            $lastData = $weatherData;
            $lastDatetime = $weatherDatetime;
        }
    }

    $timeData = array();
    $headers = $data[0];

    for($i = 0; $i < sizeof($lastData); $i++) {
        $timeData[$headers[$i]] = $lastData[$i];
    }

    return $timeData;
}

function getWeatherData($wpsid, $yr, $mo, $da)
{
    $WUdatastr = "http://www.wunderground.com/weatherstation/WXDailyHistory.asp";
    $wunderstring = $WUdatastr . "?ID=" . $wpsid . "&month=" . $mo . "&day=" . $da . "&year=" . $yr . "&format=1&graphspan=day";    // Day
    $csvraw = getcsvWithoutHanging($wunderstring);
    $csvdata = array_pure($csvraw);
    return $csvdata;
}

function array_pure ($input) {  // Bad hack to pick out the lines w/o "\r\n" by picking known content
    $i = 0;
    while($i < count($input)) {
        if(  $input[$i][0][0] == "2" || $input[$i][0][0] == "T") {
            $return[] = $input[$i];
        }
    $i++;
    }
return $return;
}

function getcsvWithoutHanging($url)   {

    $numberOfSeconds=4;
    error_reporting(0);
    $url = str_replace("http://","",$url);
    $urlComponents = explode("/",$url);
    $domain = $urlComponents[0];
    $resourcePath = str_replace($domain,"",$url);
//  $socketConnection = fsockopen($domain, 80, $errno, $errstr, $numberOfSeconds);
    $socketConnection = fsockopen('ssl://'.$domain, 443, $errno, $errstr, $numberOfSeconds);    
        $cols = '';
        fputs($socketConnection, "GET $resourcePath HTTP/1.0\r\nHost: $domain\r\nUser-agent: $UA\r\nConnection: close\r\n\r\n");
        $rows = 0;
        while (!feof($socketConnection)) {
//          $line = ereg_replace("<br>", "", fgets($socketConnection, 4096));  //One of these gets left in there somehow
            $line = str_replace("<br>", "", fgets($socketConnection, 4096));  //One of these gets left in there somehow 
            $cols[] = explode(",", $line);
        }
    fclose ($socketConnection);
    for ($i = 0; $i<=11;$i++) {  // Remove the header info that came with download
        array_shift($cols);
    }

    return($cols);
}

function get_web_page($url) {

    $ch = curl_init(); 

    // set url 
    curl_setopt($ch, CURLOPT_URL, $url); 

    //return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    // $output contains the output string 
    $output = curl_exec($ch); 

    // close curl resource to free up system resources 
    curl_close($ch);  


    /**$options = array(
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER         => false,  // don't return headers
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
        CURLOPT_ENCODING       => "",     // handle compressed
        CURLOPT_USERAGENT      => "test", // name of client
        CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT        => 120,    // time-out on response
    ); 

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $content  = curl_exec($ch);

    curl_close($ch);

    return $content;*/

    return $output;
}
