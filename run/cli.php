<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../autoload.php';

use IDDQDBY\LastNews\LastNewsReader;
use IDDQDBY\LastNews\Parsers\ParserResult;

$resource = @$argv[1];
$amount = (int)@$argv[2];
$section = @$argv[3];

$reader = new LastNewsReader();
$reader->read( $resource, $section, $amount, function ( ParserResult $result ) {
    
    echo $result->getTitle();
    
    foreach( $result->getArticles() as $article ) {
        echo "\n\n\n\n";
        echo $article;
    }
    
    echo "\n";
    
    foreach( $result->getErrors() as $ex ) {
        file_put_contents( 'php://stderr', "\n\n\n\n".$ex, FILE_APPEND );
    }
    
} );
