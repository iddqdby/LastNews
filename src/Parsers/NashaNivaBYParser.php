<?php

namespace IDDQDBY\LastNews\Parsers;

use Exception;
use IDDQDBY\LastNews\Parsers\Result\Article;

/**
 * Parser for nn.by
 * 
 * It parses SHORT BASIC information for each article, without details.
 * 
 * @author Sergey Protasevich
 */
class NashaNivaBYParser extends AbstractHTMLParser {
    
    const SECTION_TOP = 'top';
    const SECTION_ECONOMY = 'economy';
    const SECTION_ACCIDENTS = 'accidents';
    const SECTION_STYLE = 'style';
    const SECTION_TECHNOLOGIES = 'technologies';
    const SECTION_CULTURE = 'culture';
    const SECTION_SPORT = 'sport';
    const SECTION_AUTO = 'auto';
    const SECTION_LOVE_AND_SEX = 'love_and_sex';
    const BASE_URI = 'http://nn.by/';
    const MAX_AMOUNT = 30;
    
    private static $sections_uri = [
        self::SECTION_TOP => '?c=ca&i=559',
        self::SECTION_ECONOMY => '?c=ca&i=582',
        self::SECTION_ACCIDENTS => '?c=ca&i=611',
        self::SECTION_STYLE => '?c=ca&i=621',
        self::SECTION_TECHNOLOGIES => '?c=ca&i=623',
        self::SECTION_CULTURE => '?c=ca&i=593',
        self::SECTION_SPORT => '?c=ca&i=566',
        self::SECTION_AUTO => '?c=ca&i=624',
        self::SECTION_LOVE_AND_SEX => '?c=ca&i=625',
    ];
    
    private static $sections_title = [
        self::SECTION_TOP => 'Навіны',
        self::SECTION_ECONOMY => 'Гаспадарка',
        self::SECTION_ACCIDENTS => 'Здарэнні',
        self::SECTION_STYLE => 'Стыль',
        self::SECTION_TECHNOLOGIES => 'Тэхналогіі',
        self::SECTION_CULTURE => 'Культура',
        self::SECTION_SPORT => 'Спорт',
        self::SECTION_AUTO => 'Аўто',
        self::SECTION_LOVE_AND_SEX => 'Каханне і сэкс',
    ];

    /**
     * {@inheritdoc}
     */
    public function getSections() {
        return array_keys( self::$sections_uri );
    }
    
    protected function getDefaultSection() {
        return self::SECTION_TOP;
    }

    protected function getResourceTitle( $section ) {
        return 'Наша Ніва: '.self::$sections_title[ $section ];
    }
    
    protected function getBaseURI() {
        return self::BASE_URI;
    }

    protected function getMaxAmout( $section ) {
        return self::MAX_AMOUNT;
    }

    protected function sectionExists( $section ) {
        return array_key_exists( $section, self::$sections_uri );
    }

    protected function getSectionURI( $section ) {
        return self::$sections_uri[ $section ];
    }

    protected function parseArticleInfo( $base_uri, $section, $section_uri, $full_uri, $amount, $section_html, $article_number ) {
        
        $a = htmlqp( $section_html )
            ->find('div.main-container div.content div.list-headline div.title a')
            ->get( $article_number );
        
        if( !$a ) {
            throw new Exception('DOM Node not found: '.$article_number);
        }
        
        return htmlqp( $a )->attr('href');
    }

    protected function parseArticle( \GuzzleHttp\Client $http_client, $base_uri, $section, $section_uri, $full_uri, $article_info ) {
        
        if( !preg_match( '/^https?:\/\//', $article_info ) ) {
            $article_info = rtrim( $base_uri, '/' ).$article_info;
        }
        
        $article_html = $http_client
                ->get( $article_info )
                ->getBody()
                ->getContents();
        $article_html = mb_convert_encoding( $article_html, 'UTF-8', 'WINDOWS-1251' );
        
        $article_object = htmlqp( $article_html, null, [ 'convert_to_encoding' => 'UTF-8' ] );

        $title = $article_object
                ->find( 'div.main-container div.content article h1' )
                ->get( 0 );

        $text = $article_object
                ->find( 'div.main-container div.content div.article-content p' )
                ->get( 0 );
        
        $title_string = $title
                ? trim( htmlqp( $title )->text() )
                : '';

        $text_string = $text
                ? trim( htmlqp( $text )->text() )
                : '';

        $title_string = mb_convert_encoding( $title_string, 'WINDOWS-1251', 'UTF-8' );
        $text_string = mb_convert_encoding( $text_string, 'WINDOWS-1251', 'UTF-8' );
        
        return new Article( $title_string, $text_string, $article_info );
    }

}
