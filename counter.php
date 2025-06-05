<?php

ob_start();

$mysqli = new mysqli('localhost', 'username', 'password', 'database');

$mysqli->query("CREATE TABLE IF NOT EXISTS `hit_counter` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `referer` TEXT NOT NULL UNIQUE , `hits` INT NOT NULL DEFAULT 0 );");

$referer = 'unknown';

if ( !empty($_SERVER['HTTP_REFERER']) ) {
        $parsed_url = parse_url($_SERVER['HTTP_REFERER']);

        if ( $parsed_url !== false && !empty($parsed_url['host']) ) {
                $referer = $parsed_url['host'];
                if ( !empty($parsed_url['path']) ) {
                        $referer.= $parsed_url['path'];
                }
                $referer = rtrim($referer, '/');
        }
}

$cookie_name = "hit_counted_".md5($referer);

$result = $mysqli->query("SELECT `hits` FROM `hit_counter` WHERE `referer`='$referer' LIMIT 1;");
if ( $result && $result->num_rows == 1 ) {
        $hits = $result->fetch_object()->hits;
        if ( !isset($_COOKIE[$cookie_name]) ) {
                $hits++;
                $mysqli->query("UPDATE `hit_counter` SET `hits`=$hits WHERE `referer`='$referer';");
                setcookie($cookie_name, '1', ['expires'=>time()+86400, 'path'=>'/', 'samesite'=>'Lax']);
        }
} else {
        $hits = 1;
        $mysqli->query("INSERT INTO `hit_counter` ( `referer`, `hits` ) VALUES ( '$referer', $hits );");
        if ( !isset($_COOKIE[$cookie_name]) ) {
                setcookie($cookie_name, '1', ['expires'=>time()+86400, 'path'=>'/', 'samesite'=>'Lax']);
        }
}

$digits = strlen($hits) > 6 ? strlen($hits) : 6;

$hits = str_pad($hits, $digits, 0, STR_PAD_LEFT);

$border = 3;

$x1 = $border;
$y1 = $border;
$w1 = 32;
$h1 = $w1 * 1.4;

$width = ($digits*$w1)+(2*$border);
$height = $h1+(2*$border);

echo <<<SVG
<svg width="{$width}px" height="{$height}px" viewBox="0 0 {$width} {$height}" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg">
        <defs>
                <style type="text/css"><![CDATA[
                        @import url('https://fonts.googleapis.com/css2?family=Kode+Mono:wght@400..700&display=swap');
                ]]></style>
                <linearGradient id="linearGradient410">
                        <stop style="stop-color:#000000;stop-opacity:1;" offset="0" id="stop406" />
                        <stop style="stop-color:#4b4b4b;stop-opacity:1;" offset="0.5" id="stop1055" />
                        <stop style="stop-color:#000000;stop-opacity:1;" offset="1" id="stop408" />
                </linearGradient>
                <linearGradient xlink:href="#linearGradient410" id="linearGradient412" x1="100.46775" y1="119.36781" x2="100.46646" y2="121.88057" gradientUnits="userSpaceOnUse" gradientTransform="matrix(1.8761717,0,0,1.8761717,-186.6239,-223.63978)" />
                <linearGradient xlink:href="#linearGradient410" id="linearGradient3082" gradientUnits="userSpaceOnUse" gradientTransform="matrix(1.8761717,0,0,1.8761717,-186.6239,-223.63978)" x1="195.49971" y1="120.8285" x2="195.60861" y2="144.79439" />
        </defs>
        <rect style="fill:#cccccc;fill-opacity:1;stroke-width:0" width="{$width}px" height="{$height}px" x="0px" y="0px" />
SVG;

for ( $i=0;$i<$digits;$i++ ) {
        $num = substr($hits, $i, 1);

        $offsetX = $x1 + ( $w1 * $i );
        $offsetY = $y1;
        $textX = $offsetX + ( $w1 * 0.2 );
        $textY = $offsetY + ( $h1 * 0.8 );
        $textH = $h1;
        echo <<<SVG
        <rect style="fill:url(#linearGradient3082);fill-opacity:1;stroke-width:1;stroke-color:#cccccc;" width="{$w1}" height="{$h1}" x="{$offsetX}px" y="{$offsetY}px" />
        <text xml:space="preserve" style="font-optical-sizing: auto;font-family:'Kode Mono',monospace;font-size:{$textH}px;fill:#ffffff;fill-opacity:1;stroke-width:0" x="{$textX}px" y="{$textY}px">$num</text>
        SVG;
}

echo "</svg>";

header("Content-Type: image/svg+xml");
header("Content-Length: ".ob_get_length());
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

ob_end_flush();

?>
