<?php

namespace LastNews;

/**
 * Provider of various parsers.
 *
 * @author Sergey Protasevich
 */
class ParserProvider {
    
    private $class_map;
    private $parsers = [];
    
    /**
     * Get parser for given resource.
     * 
     * @param string $resource the name of the resource
     * @return IParser the instance of parser for the given resource
     */
    public function getParser( $resource ) {
        
        if( !array_key_exists( $resource, $this->class_map ) ) {
            $resource = '';
        }
        
        $class_name = APP_NAMESPACE.'\\Parsers\\'.$this->class_map[ $resource ];
        
        if( empty( $this->parsers[ $class_name ] ) ) {
            $this->parsers[ $class_name ] = new $class_name();
        }
        
        return $this->parsers[ $class_name ];
    }
    
    private function init() {
        $this->class_map = [
            ''              => 'NullParser', // Stub
            'tut.by'        => 'TutBYParser',
            'onliner.by'    => 'OnlinerBYParser',
            
            // TODO Add other implementations
            
        ];
    }


    /**
     * Get the instance of ParserProvider.
     * 
     * @staticvar ParserProvider $instance the instance ParserProvider
     * @return ParserProvider the instance of ParserProvider
     */
    public static function getInstance() {
        static $instance = null;
        if( is_null( $instance ) ) {
            $instance = new self();
            $instance->init();
        }
        return $instance;
    }
    
}
