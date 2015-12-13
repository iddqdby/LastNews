<?php

spl_autoload_register( function ( $class ) {
    
    $namespace = 'IDDQDBY\\LastNews';
    $basedir = __DIR__.'/src';
    
    if( 0 !== strncmp( $namespace.'\\', $class, strlen( $namespace ) ) ) {
        return;
    }

    $relative_class = substr( $class, strlen( $namespace ) );
    $relative_path = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class );
    $file = $basedir.DIRECTORY_SEPARATOR.$relative_path.'.php';

    if( file_exists( $file ) ) {
        require $file;
    }
} );
