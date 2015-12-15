<?php

namespace IDDQDBY\LastNews;

use InvalidArgumentException;
use IDDQDBY\LastNews\ParserProvider;

/**
 * Last news reader.
 *
 * @author Sergey Protasevich
 */
class LastNewsReader {
    
    const DEFAULT_RESOURCE = '';
    
    private static $built_in_parser_classes = [
        self::DEFAULT_RESOURCE  => '\\IDDQDBY\\LastNews\\Parsers\\NullParser',
        'tut.by'                => '\\IDDQDBY\\LastNews\\Parsers\\TutBYParser',
        'nn.by'                 => '\\IDDQDBY\\LastNews\\Parsers\\NashaNivaBYParser',
//        'elementy.ru'           => '\\IDDQDBY\\LastNews\\Parsers\\ElementyRUParser',
        // TODO Add another parsers here
    ];
    
    private $parser_provider;
    
    /**
     * Create instance of reader.
     */
    public function __construct() {
        $this->parser_provider
                = new ParserProvider();
        $this->parser_provider
                ->setParserClasses( self::$built_in_parser_classes );
    }

    /**
     * Read last news from the given resource.
     * 
     * @param string $resource the name of the resource
     * @param string $section the name of the section
     * @param int $amount maximum amount of news to read
     * @param callable $processor callback to process the result (optional);
     * instance of <code>ParserResult</code>
     * will be passed to the callback
     * @return mixed the result; instance of <code>ParserResult</code> if
     * <code>$processor</code> is not provided, or result of the
     * <code>$processor</code> callback
     */
    public function read( $resource, $section, $amount, callable $processor = null ) {
        
        if( !$this->parser_provider->isRegistered( $resource ) ) {
            $resource = self::DEFAULT_RESOURCE;
        }
        
        $parser = $this->parser_provider->getParser( $resource );
        $result = $parser->parse( $section, $amount );
        
        return is_null( $processor ) ? $result : call_user_func( $processor, $result );
    }
    
    /**
     * Get array of supported sections for given resource.
     * 
     * @param string $resource the name of the resource
     * @return array supported sections
     * @throws InvalidArgumentException if parser for given resourse is not
     * registered
     */
    public function getSupportedSections( $resource ) {
        return $this
                ->getParserProvider()
                ->getParser( $resource )
                ->getSections();
    }
    
    /**
     * Get parser provider.
     * 
     * @return ParserProvider parser provider
     */
    public function getParserProvider() {
        return $this->parser_provider;
    }
    
}
