<?php

namespace IDDQDBY\LastNews\Parsers;

use Exception;
use GuzzleHttp\Client;
use IDDQDBY\LastNews\Parsers\Result\Article;
use IDDQDBY\LastNews\Parsers\Result\Excerpt;

/**
 * Parser for elementy.ru.
 * 
 * It parses data from RSS feed.
 *
 * @author Sergey Protasevich
 */
class ElementyRUParser implements IParser {

    const TITLE = '"Элементы": новости науки';
    const MAX_AMOUNT = 10;
    
    /**
     * {@inheritdoc}
     */
    public function parse( $section, $amount ) {
        
        if( 0 >= $amount || 10 < $amount ) {
            $amount = self::MAX_AMOUNT;
        }
        
        $xml = (new Client())
                ->get('http://elementy.ru/rss/news')
                ->xml();
        
        $articles = [];
        $errors = [];
        
        set_error_handler( function ( $errno, $errstr ) {
            throw new Exception( $errno.' '.$errstr );
        } );
        for( $i = 0; $i < $amount; $i++ ) {
            try {
                $item = $xml->xpath('/rss/channel/item['.($i + 1).']')[0];

                $title = trim( strip_tags( (string)$item->xpath('category[1]')[0] ) );
                $text = trim( strip_tags( (string)$item->xpath('description[1]')[0] ) );
                $uri = trim( strip_tags( (string)$item->xpath('link[1]')[0] ) );

                $articles[] = new Article( $title, $text, $uri );
            } catch( Exception $ex ) {
                $errors[] = $ex;
            }
        }
        restore_error_handler();
        
        return new Excerpt( self::TITLE, $articles, $errors );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSections() {
        return [ '' ];
    }

}
