<?php

namespace IDDQDBY\LastNews\Parsers;

/**
 * Parser for TUT.BY.
 *
 * @author Sergey Protasevich
 */
class TutBYParser extends AbstractHtmlParser {
    
    const SECTION_DEFAULT = 'm';
    const SECTION_FINANCE = 'finance';
    const SECTION_AUTO = 'auto';
    const SECTION_SPORT = 'sport';
    const SECTION_42 = '42';
    const SECTION_LADY = 'lady';
    const SECTION_PLACEHOLDER = '%section%';
    
    private static $sections = [
        self::SECTION_DEFAULT => 'Главные новости',
        self::SECTION_FINANCE => 'Финансы',
        self::SECTION_AUTO => 'Авто',
        self::SECTION_SPORT => 'Спорт',
        self::SECTION_42 => 'Высокие технологии',
        self::SECTION_LADY => 'Леди',
    ];

    protected function sectionExists( $section ) {
        return array_key_exists( $section, self::$sections );
    }
    
    protected function getDefaultSection() {
        return self::SECTION_DEFAULT;
    }
    
    protected function getBaseURI() {
        return 'http://'.self::SECTION_PLACEHOLDER.'.tut.by/';
    }

    protected function getSectionURI( $section ) {
        return $section;
    }
    
    protected function constructFullURI( $base_uri, $section, $section_uri ) {
        $uri = str_replace( self::SECTION_PLACEHOLDER, $section_uri, $base_uri );
        if( self::SECTION_DEFAULT !== $section ) {
            $uri .= 'pda/';
        }
        return $uri;
    }

    protected function getMaxAmout( $section ) {
        return 0;
    }

    protected function getResourceTitle( $section ) {
        return 'Новости TUT.BY: '.self::$sections[ $section ];
    }

    protected function parseArticleInfo( $base_uri, $section, $section_uri, $amount, $section_html, $article_number ) {
        
        $max_amount = $this->getMaxAmout( $section );
        if( 0 < $max_amount && $article_number >= $max_amount ) {
            return null;
        }
        
        $uri_array = [];
        
        $section_object = htmlqp( $section_html );
        
        $dl_array = $section_object
                ->find('div#maincontent div.news dl')
                ->get();
        
        $dt_found = false;
        foreach( $dl_array as $dl_node ) {
            
            $dl = htmlqp( $dl_node );
            $dt = $dl
                    ->find('dt')
                    ->get( 0 );
            
            if( $dt && $dt_found ) {
                // end of main section
                break;
            } else {
                $dt_found = true;
            }
            
            $a_array = $dl
                    ->find('a')
                    ->get();
            
            foreach( $a_array as $a_node ) {
                $uri_array[] = htmlqp( $a_node )->attr('href');
            }
        }
        
        return array_key_exists( $article_number, $uri_array ) ? $uri_array[ $article_number ] : null;
    }

    protected function parseArticle( \GuzzleHttp\Client $http_client, $base_uri, $section, $section_uri, $article_info ) {

        $article_html = $http_client
                ->get( $article_info )
                ->getBody()
                ->getContents();

        $article_object = htmlqp( $article_html, null, [ 'convert_to_encoding' => 'UTF-8' ] );

        $title_selector = 'div#maincontent div.body div.h h2';
        $title_node_num = 0;

        $text_selector = 'div#maincontent div.body p';
        $text_node_num = 0;

        $title = $article_object
                ->find( $title_selector )
                ->get( $title_node_num );

        $text = $article_object
                ->find( $text_selector )
                ->get( $text_node_num );

        $title_string = $title
                ? htmlqp( $title )->text()
                : '';

        $text_string = $text
                ? htmlqp( $text )->text()
                : '';
        
        return $title_string."\n\n".$text_string;
    }

}
