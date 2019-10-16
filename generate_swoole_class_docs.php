<?php

use Azonmedia\Reflection\ReflectionClassGenerator;

require_once('./vendor/autoload.php');


$path_prefix = './classes/';

//first require the existing swoole docs
//in order to obtain their current doc blocks
//include_all_doc_classes($path_prefix);

generate_swoole_docs($path_prefix);

generate_readme_index($path_prefix.'Swoole/', 'Swoole');
//generate_readme_index($path_prefix, $path_prefix);

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


        $file_content = $RClass->getClassStructure(TRUE);
        //file_put_contents($file_path, $file_content);//do not generate a separate class

        $RClass->setClassName('Swoole'.substr($class_name, strpos($class_name, '\\') ));
        $class_api = $RClass->getClassStructure(TRUE);
        $class_readme_content = <<<README
# $class_name

## Introduction

## API

```php
$class_api

```

## Examples


README;
        $readme_file = dirname($file_path).'/'.$RClass->getShortName().'.md';
        if (!file_exists($readme_file)) {
            file_put_contents($readme_file, $class_readme_content);
        }

    }
}

function generate_readme_index($file_path, $ns_prefix) {


    //print $file_path.' '.$path_prefix.PHP_EOL;

    $ns_list_str = '';
    $dirs = glob($file_path.'*', GLOB_ONLYDIR);
    foreach ($dirs as $dir) {
        $ns_list_str .= '* ['.$ns_prefix.'\\'.str_replace([$file_path,'/'], ['','\\'], $dir).']('.str_replace([$file_path,'Swoole/'], ['',''], $dir).')'.PHP_EOL;
        //print $dir.' '.$ns_prefix.PHP_EOL;
        $new_ns_prefix = str_replace('/','\\',substr($dir, strpos($dir, $ns_prefix)));
        print $new_ns_prefix.PHP_EOL;
        generate_readme_index($dir.'/', $new_ns_prefix);
    }

    $classes_list_str = '';
    $files = glob($file_path.'*.md');
    foreach ($files as $file) {
        $classes_list_str .= '* ['.$ns_prefix.'\\'.str_replace([$file_path,'/','.md'], ['','\\',''], $file).']('.str_replace([$file_path,'Swoole/'], ['',''], $file).')'.PHP_EOL;
    }



    $title = str_replace('\\', '/', dirname($file_path));

    if ($ns_list_str) {
        $ns_list_str = '## Namespaces'.PHP_EOL.$ns_list_str.PHP_EOL;
    }
    if ($classes_list_str) {
        $classes_list_str = '## Classes'.PHP_EOL.$classes_list_str.PHP_EOL;
    }

    $readme_index_content = <<<README
# $title
$ns_list_str
$classes_list_str
README;
    $readme_index_file = $file_path.'/README.md';
    file_put_contents($readme_index_file, $readme_index_content);


}


function include_all_doc_classes(string $path) {
    $Directory = new \RecursiveDirectoryIterator($path);
    $Iterator = new \RecursiveIteratorIterator($Directory);
    $Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RegexIterator::GET_MATCH);
    foreach ($Regex as $path=>$match) {
        require_once($path);
    }
}


