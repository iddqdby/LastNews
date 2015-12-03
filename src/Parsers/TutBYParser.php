<?php

namespace LastNews\Parsers;

/**
 * Parser for TUT.BY.
 *
 * @author Sergey Protasevich
 */
class TutBYParser extends AbstractHtmlParser {
    
    const SECTION_DEFAULT = 'www';
    const SECTION_FINANCE = 'finance';
    const SECTION_REALTY = 'realty';
    const SECTION_AUTO = 'auto';
    const SECTION_SPORT = 'sport';
    const SECTION_42 = '42';
    const SECTION_LADY = 'lady';
    const SECTION_PLACEHOLDER = '%section%';
    const ATTEMPTS = 10;
    
    private static $sections = [
        self::SECTION_DEFAULT => 9,
        self::SECTION_FINANCE => 6,
        self::SECTION_REALTY => 3,
        self::SECTION_AUTO => 3,
        self::SECTION_SPORT => 3,
        self::SECTION_42 => 3,
        self::SECTION_LADY => 3,
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
        return str_replace( self::SECTION_PLACEHOLDER, $section_uri, $base_uri );
    }

    protected function getDefaultAmout( $section ) {
        return self::$sections[ $section ];
    }

    protected function getResourceTitle( $section ) {
        
        $title = 'Новости TUT.BY: ';
        
        switch( $section ) {
            case self::SECTION_DEFAULT:
                $title .= 'Главные новости';
                break;
            case self::SECTION_FINANCE:
                $title .= 'Финансы';
                break;
            case self::SECTION_REALTY:
                $title .= 'Недвижимость';
                break;
            case self::SECTION_AUTO:
                $title .= 'Авто';
                break;
            case self::SECTION_SPORT:
                $title .= 'Спорт';
                break;
            case self::SECTION_42:
                $title .= 'Высокие технологии';
                break;
            case self::SECTION_AFISHA:
                $title .= 'Афиша';
                break;
            case self::SECTION_LADY:
                $title .= 'Леди';
                break;
        }
        
        return $title;
    }

    protected function parseArticleInfo( $base_uri, $section, $section_uri, $amount, $section_html, $article_number ) {
        
        if( $this->getDefaultAmout( $section ) <= $article_number ) {
            return null;
        }
        
        $article_info = null;
        $section_object = htmlqp( $section_html );
        
        switch( $section ) {
            case self::SECTION_DEFAULT:
                
                switch( $article_number ) {
                    case 0:
                        $selector = '#title_news_block .b-general .entry-head a';
                        $node_num = 0;
                        break;
                    case 1:
                    case 2:
                        $selector = '#title_news_block .b-topnews .b-topc-hot a.entry__link';
                        $node_num = $article_number - 1;
                        break;
                    default:
                        $selector = '#title_news_block .b-topnews .b-newsfeed .news-entry a.entry__link';
                        $node_num = $article_number - 3;
                }
                
                break;
            case self::SECTION_FINANCE:
                
                switch( $article_number ) {
                    case 0:
                        $selector = '.b-title-news .main_news a';
                        $node_num = 0;
                        break;
                    default:
                        $selector = '.b-title-news .additional_news li a';
                        $node_num = $article_number - 1;
                }
                
                break;
            case self::SECTION_REALTY:
            case self::SECTION_AUTO:
            case self::SECTION_SPORT:
            case self::SECTION_42:
            case self::SECTION_LADY:
                
                $selector = '.b-mainnews th a';
                $node_num = $article_number;
                
                break;
        }
        
        $a = $section_object
                ->find( $selector )
                ->get( $node_num );
        
        if( $a ) {
            $article_info = htmlqp( $a )->attr('href');
        }
        
        return $article_info;
    }

    protected function parseArticle( \GuzzleHttp\Client $http_client, $base_uri, $section, $section_uri, $article_info ) {
        
        if( empty( $article_info ) ) {
            return '';
        }
        
        $title_string = '';
        $text_string = '';
        
        $attempt = self::ATTEMPTS;
        
        do {
        
            sleep( 1 );

            $article_html = $http_client
                    ->get( $article_info )
                    ->getBody()
                    ->getContents();

            $article_object = htmlqp( $article_html, null, [ 'convert_to_encoding' => 'UTF-8' ] );

            $title_selector = 'div.m_header h1';
            $title_node_num = 0;

            $text_selector = '#article_body p strong';
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
            
        } while( ( empty( $title_string ) || empty( $text_string ) ) && 0 < $attempt-- );
        
        return $title_string."\n\n".$text_string;
    }

}
