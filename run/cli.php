<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../autoload.php';

use IDDQDBY\LastNews\LastNewsReader;
use IDDQDBY\LastNews\Parsers\Result\Excerpt;

$resource = @$argv[1];
$amount = (int)@$argv[2];
$section = @$argv[3];

$reader = new LastNewsReader();
$reader->read( $resource, $section, $amount, function ( Excerpt $excerpt ) {
    
    echo $excerpt->getTitle();
    
    foreach( $excerpt->getArticles() as $article ) {
        echo "\n\n\n\n";
        echo $article->getTitle();
        echo "\n\n";
        echo $article->getText();
    }
    
    echo "\n";
    
    foreach( $excerpt->getErrors() as $ex ) {
        file_put_contents( 'php://stderr', "\n\n\n\n".$ex, FILE_APPEND );
    }
    
} );
