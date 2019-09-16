<?php

use Azonmedia\Reflection\ReflectionClassGenerator;

require_once('./vendor/autoload.php');


$path_prefix = './classes/';

//first require the existing swoole docs
//in order to obtain their current doc blocks
include_all_doc_classes($path_prefix);

generate_swoole_docs($path_prefix);



function generate_swoole_docs(string $path_prefix) {

    $classes = get_declared_classes();

    if (!file_exists($path_prefix)) {
        mkdir($path_prefix);
    } elseif (!is_dir($path_prefix)) {
        exit('The specified path to dump the classes is not a directory.');
    }

    foreach ($classes as $class) {
        if (stripos($class, 'swoole\\') !== 0) {
            continue;
        }
        if (strpos($class,'_') !== FALSE) {
            //this is an alias - these can be added in a separate file
            continue;
        }
        $file_path = $path_prefix . str_replace('\\', '/', $class) . '.php';
        if (!file_exists(dirname($file_path))) {
            mkdir(dirname($file_path), 0777, TRUE);
        }

        $RClass = new ReflectionClassGenerator($class);
        $class_name = $RClass->getName();

        $RClass->setClassName('SwooleDocs'.substr($class_name, strpos($class_name, '\\') ));
        try {
            $ROldClass = new \Azonmedia\Reflection\ReflectionClass($RClass->getName());
            $existing_doc_comment = $ROldClass->getDocComment();
            $RClass->setDocComment($existing_doc_comment);
        } catch (\ReflectionException $Exception) {
            //this is a new class
        }


        $file_content = $RClass->getClassStructure();
        file_put_contents($file_path, $file_content);
    }
}


function include_all_doc_classes(string $path) {
    $Directory = new \RecursiveDirectoryIterator($path);
    $Iterator = new \RecursiveIteratorIterator($Directory);
    $Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RegexIterator::GET_MATCH);
    foreach ($Regex as $path=>$match) {
        require_once($path);
    }
}


