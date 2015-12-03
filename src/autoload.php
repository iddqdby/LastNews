<?php

require __DIR__.'/../vendor/autoload.php';

define( 'APP_NAMESPACE', 'LastNews' );
define( 'APP_BASEDIR', __DIR__ );

$app_namespace_len = strlen( APP_NAMESPACE );

spl_autoload_register( function ( $class ) use ( $app_namespace_len ) {
    
    if( 0 !== strncmp( APP_NAMESPACE.'\\', $class, $app_namespace_len ) ) {
        return;
    }

    $relative_class = substr( $class, $app_namespace_len );
    $file = APP_BASEDIR.'/'.str_replace( '\\', '/', $relative_class ).'.php';

    if( file_exists( $file ) ) {
        require $file;
    }
} );
