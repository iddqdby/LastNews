<?php

namespace IDDQDBY\LastNews\Parsers;

/**
 * Parser interface.
 * 
 * All parsers must implement this interface.
 * 
 * @author Sergey Protasevich
 */
interface IParser {
    
    /**
     * Parse given amount of articles from the given section.
     * 
     * @param string $section the name of the section
     * @param int $amount amount of articles to parse
     * @return ParserResult the result containing title and array of articles
     */
    function parse( $section, $amount );
    
}
