<?php


/**
 * Debug function
 * d($var);
 */
function d($param1, $param2 = null)
{

    if ($param2 === null) {
        $title = $param2;
        $value = $param1;
    } else {
        $title = $param1;
        $value = $param2;
    }

    if (PHP_SAPI === 'cli') {
        echo !empty($title) ? "\n=== $title ===\n" : '';
        echo print_r($value, true) . "\n";
        return;
    }

    $title = htmlspecialchars($title ?: 'Debug', ENT_QUOTES, 'UTF-8');
    $value = htmlspecialchars(print_r($value, true), ENT_QUOTES, 'UTF-8');

    echo "<fieldset style='
        margin:15px 0; padding:10px; border:1px solid #ccc; 
        background:#f9f9f9; font-family:monospace; font-size:13px;
    '>
        <legend style='padding:0 8px; font-weight:bold; color:#d35400;'>$title</legend>
        <pre style='margin:0; white-space:pre-wrap; word-break:break-all;'>$value</pre>
    </fieldset>";
}

/**
 * Debug function with die() after
 * dd($var);
 */
function dd($param1, $param2 = null)
{
    if (empty($param2)) {
        $title = $param2;
        $value = $param1;
    } else {
        $title = $param1;
        $value = $param2;
    }
    d($title, $value);
    die();
}




function ddc($param1, $param2 = null)
{
    if (empty($param2)) {
        $title = $param2;
        $value = $param1;
    } else {
        $title = $param1;
        $value = $param2;
    }
    dc($title, $value);
    die();
}



function dc($param1, $param2 = null)
{
    // Определяем title и value
    if ($param2 === null) {
        $title = null;
        $value = $param1;
    } else {
        $title = $param1;
        $value = $param2;
    }

    // Цвета ANSI
    $colorTitle = "\033[1;36m";   // ярко-голубой
    $colorValue = "\033[0;37m";   // светло-серый
    $colorReset = "\033[0m";

    echo "\n";

    if ($title !== null) {
    echo $colorTitle . "......................................................." . $colorReset . "\n";
        echo $colorTitle . "$title" . $colorReset . "\n";
    }

    echo $colorValue . print_r($value, true) . $colorReset . "\n";
    echo $colorTitle . "......................................................." . $colorReset . "\n\n";
    return;
}


/**
 * Генерирует UUID v4
 * @return string
 */
function generateUUID()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
