<?php

namespace IDDQDBY\LastNews\Parsers;

use IDDQDBY\LastNews\Parsers\Result\Article;

/**
 * Parser for TUT.BY.
 * 
 * It parses SHORT BASIC information for each article, without details.
 *
 * @author Sergey Protasevich
 */
class TutBYParser extends AbstractHTMLParser {
    
    const SECTION_DEFAULT = 'm';
    const SECTION_FINANCE = 'finance';
    const SECTION_AUTO = 'auto';
    const SECTION_SPORT = 'sport';
    const SECTION_42 = '42';
    const SECTION_LADY = 'lady';
    const SECTION_PLACEHOLDER = '%section%';
    
    private static $sections_title = [
        self::SECTION_DEFAULT => 'Главные новости',
        self::SECTION_FINANCE => 'Финансы',
        self::SECTION_AUTO => 'Авто',
        self::SECTION_SPORT => 'Спорт',
        self::SECTION_42 => 'Высокие технологии',
        self::SECTION_LADY => 'Леди',
    ];

    /**
     * {@inheritdoc}
     */
    public function getSections() {
        return [
            self::SECTION_DEFAULT,
            self::SECTION_FINANCE,
            self::SECTION_AUTO,
            self::SECTION_SPORT,
            self::SECTION_42,
            self::SECTION_LADY,
        ];
    }

    protected function sectionExists( $section ) {
        return array_key_exists( $section, self::$sections_title );
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
    
    protected function getHTTPOptions( $base_uri, $section, $section_uri, $amount ) {
        $options = parent::getHTTPOptions( $base_uri, $section, $section_uri, $amount );
        $options['defaults']['headers']['Referer'] = str_replace( self::SECTION_PLACEHOLDER, self::SECTION_DEFAULT, $base_uri );
        return $options;
    }

    protected function getResourceTitle( $section ) {
        return 'Новости TUT.BY: '.self::$sections_title[ $section ];
    }

    protected function parseArticleInfo( $base_uri, $section, $section_uri, $full_uri, $amount, $section_html, $article_number ) {
        
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

    protected function parseArticle( \GuzzleHttp\Client $http_client, $base_uri, $section, $section_uri, $full_uri, $article_info ) {

        if( !preg_match( '/^https?:\/\//', $article_info ) ) {
            $article_info = str_replace( '/pda/', $full_uri, $article_info );
        }
        
        $article_html = $http_client
                ->get( $article_info )
                ->getBody()
                ->getContents();

        $article_object = htmlqp( $article_html, null, [ 'convert_to_encoding' => 'UTF-8' ] );

        $title = $article_object
                ->find( 'div#maincontent div.body div.h h2' )
                ->get( 0 );

        $text = $article_object
                ->find( 'div#maincontent div.body p' )
                ->get( 0 );

        $title_string = $title
                ? trim( htmlqp( $title )->text() )
                : '';

        $text_string = $text
                ? trim( htmlqp( $text )->text() )
                : '';
        
        return new Article( $title_string, $text_string, $article_info );
    }

}
