<?php

namespace IDDQDBY\LastNews\Parsers;

use IDDQDBY\LastNews\Parsers\Result\Excerpt;

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
     * @return Excerpt the result containing title and array of articles and
     * (optionally) array of errors
     */
    function parse( $section, $amount );
    
    /**
     * Get array of supported sections.
     * 
     * @return array supported sections
     */
    function getSections();
    
}
