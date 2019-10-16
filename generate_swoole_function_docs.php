<?php

require_once('./vendor/autoload.php');

$functions = get_defined_functions();
$functions = array_merge($functions['internal'], $functions['user']);

$swoole_functions = [];
foreach ($functions as $function) {
    if (strpos($function,'swoole') === 0) {
        $swoole_functions[] = $function;
    }
}

$output = '';

foreach ($swoole_functions as $swoole_function) {
    $RFunction = new Azonmedia\Reflection\ReflectionFunction($swoole_function);
    $output .= $RFunction->getSignature(TRUE).PHP_EOL;
}

print $output;
