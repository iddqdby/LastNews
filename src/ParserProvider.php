<?php

namespace IDDQDBY\LastNews;

use InvalidArgumentException;
use IDDQDBY\LastNews\Parsers\IParser;

/**
 * Provider of various parsers.
 *
 * @author Sergey Protasevich
 */
class ParserProvider {
    
    private $classes = [];
    private $instances = [];

    /**
     * Get parser for the given key.
     * 
     * @param string $key the key
     * @return IParser the instance of parser
     * @throws InvalidArgumentException if no parser nor parser class are
     * registered under the given key
     */
    public function getParser( $key ) {
        
        if( array_key_exists( $key, $this->instances ) ) {
            return $this->instances[ $key ];
        }
        
        if( array_key_exists( $key, $this->classes ) ) {
            return $this->instances[ $key ] = new $this->classes[ $key ]();
        }
        
        throw new InvalidArgumentException( 'No parsers are registered under the given key' );
    }
    
    /**
     * Set parser for the given key.
     * 
     * @param string $key the key
     * @param IParser $parser the instance of IParser
     */
    public function setParser( $key, IParser $parser ) {
        $this->instances[ $key ] = $parser;
    }
    
    /**
     * Set parsers for the given keys.
     * 
     * @param array $parsers parsers
     */
    public function setParsers( array $parsers ) {
        foreach( $parsers as $key => $parser ) {
            $this->setParser( $key, $parser );
        }
    }
    
    /**
     * Set parser class for the given key.
     * 
     * Class will be instantiated with no-arg constructor.
     * 
     * @param string $key the key
     * @param string $parserClassName parser class name
     * @throws InvalidArgumentException if given class does not implement
     * <code>\LastNews\Parsers\IParser</code> interface
     */
    public function setParserClass( $key, $parserClassName ) {
        
        if( !in_array( 'IDDQDBY\\LastNews\\Parsers\\IParser', class_implements( $parserClassName )) ) {
            throw new InvalidArgumentException( 'Parser must implement \\IDDQDBY\\LastNews\\Parsers\\IParser interface' );
        }
        
        $this->classes[ $key ] = $parserClassName;
    }
    
    /**
     * Set parser classes for the given keys.
     * 
     * Class will be instantiated with no-arg constructor.
     * 
     * @param array $parserClasses parser classes
     */
    public function setParserClasses( array $parserClasses ) {
        foreach( $parserClasses as $key => $parserClassName ) {
            $this->setParserClass( $key, $parserClassName );
        }
    }
    
    /**
     * Is key registered.
     * 
     * @param string $key the key
     * @return bool true if key is registered, false otherwise
     */
    public function isRegistered( $key ) {
        return array_key_exists( $key, $this->instances ) || array_key_exists( $key, $this->classes );
    }
    
}
