<?php

namespace IDDQDBY\LastNews\Parsers;

use Exception;
use GuzzleHttp\Client;
use IDDQDBY\LastNews\Parsers\Result\Article;
use IDDQDBY\LastNews\Parsers\Result\Excerpt;

/**
 * Abstract HTML parser.
 * 
 * One can use this class to implement parsers for various HTML resources such
 * as web-pages.
 *
 * @author Sergey Protasevich
 */
abstract class AbstractHTMLParser implements IParser {
    
    /**
     * {@inheritdoc}
     */
    public function parse( $section, $amount ) {
        
        try {
            
            if( !$this->sectionExists( $section ) ) {
                $section = $this->getDefaultSection();
            }

            $base_uri = $this->getBaseURI();
            $section_uri = $this->getSectionURI( $section );
            $full_uri = $this->constructFullURI( $base_uri, $section, $section_uri );

            if( 0 >= $amount ) {
                $amount = $this->getMaxAmout( $section );
            }

            $http_options = $this
                    ->getHTTPOptions( $base_uri, $section, $section_uri, $amount );
            $http_client = $this
                    ->createHTTPClient( $http_options, $base_uri, $section, $section_uri, $amount );
        
            $section_html = $http_client
                    ->get( $full_uri )
                    ->getBody()
                    ->getContents();
        
            $article_number = 0;
            $article_info_array = [];
            $article_array = [];
            $errors_array = [];
            
            try {
                while(
                        ( 0 === $amount || $article_number < $amount )
                        &&
                        null !== ( $article_info = $this->parseArticleInfo(
                                $base_uri,
                                $section,
                                $section_uri,
                                $full_uri,
                                $amount,
                                $section_html,
                                $article_number
                        ) )
                ) {
                    $article_info_array[ $article_number++ ] = $article_info;
                }
            } catch( Exception $ex ) {
                $errors_array[ $article_number++ ] = $ex;
            }

            foreach( $article_info_array as $article_number => $article_info ) {
                try {
                    $article_array[ $article_number ] = $this->parseArticle(
                        $http_client,
                        $base_uri,
                        $section,
                        $section_uri,
                        $full_uri,
                        $article_info
                    );
                } catch( Exception $ex ) {
                    $errors_array[ $article_number ] = $ex;
                }
            }
            
            $resource_title = $this->getResourceTitle( $section );
            
            return new Excerpt( $resource_title, $article_array, $errors_array );
            
        } catch( Exception $ex ) {
            return new Excerpt( '', [], [ $ex ] );
        }
    }

    /**
     * Get default section.
     * 
     * By default, empty string is returned. Override this method to return
     * custom string.
     * 
     * @return string default section
     */
    protected function getDefaultSection() {
        return '';
    }

    /**
     * Construct full URI.
     * 
     * By default, base URI and section URI are concatenated. Override this
     * method to implement custom way of construction.
     * 
     * @param string $base_uri base URI of the resource
     * @param string $section the name of the section
     * @param string $section_uri relative URI of the section
     * @return string full URI
     */
    protected function constructFullURI( $base_uri, $section, $section_uri ) {
        return $base_uri.$section_uri;
    }
    
    /**
     * Get HTTP options for the resource.
     * 
     * Override this method to get custom options.
     * 
     * @param string $base_uri base URI of the resource
     * @param string $section the name of the section
     * @param string $section_uri relative URI of the section
     * @param int $amount amount of articles to parse
     * @return array HTTP options for the resource
     */
    protected function getHTTPOptions( $base_uri, $section, $section_uri, $amount ) {
        return [
            'defaults' => [
                'headers' => [
                    'Referer' => $base_uri,
                    'User-Agent' => $this->getUserAgent(),
                ],
                'cookies' => true,
            ],
        ];
    }
    
    /**
     * Get User Agent for HTTP requests.
     * 
     * Returns User Agent string for Chrome 41. Override this method to use
     * custom string.
     * 
     * @return string User Agent HTTP header string
     */
    protected function getUserAgent() {
        return 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
    }
    
    /**
     * Create new HTTP client.
     * 
     * Override this method to do something with the newly created client before
     * it's use or to implement custom way of it's creation.
     * 
     * @param array $http_options HTTP options for the client
     * @param string $base_uri base URI of the resource
     * @param string $section the name of the section
     * @param string $section_uri relative URI of the section
     * @param int $amount amount of articles to parse
     * @return Client the instance of Client
     */
    protected function createHTTPClient(
            array $http_options, $base_uri, $section, $section_uri, $amount ) {
        
        return new Client( $http_options );
    }
    
    /**
     * Check if given section exists.
     * 
     * @param string $section the name of the section
     * @return bool true if given section exists, false otherwise
     */
    protected abstract function sectionExists( $section );

    /**
     * Get base URI of the resource.
     * 
     * @return string base URI of the resource
     */
    protected abstract function getBaseURI();
    
    /**
     * Get relative URI of the section.
     * 
     * @param string $section the name of the section
     * @return string relative URI of the section
     */
    protected abstract function getSectionURI( $section );
    
    /**
     * Get max amount of last articles to parse.
     * 
     * @param string $section the name of the section
     * @return int max amount of articles to parse
     */
    protected abstract function getMaxAmout( $section );
    
    /**
     * Get the title of the resource.
     * 
     * @param string $section the name of the section
     * @return string the title of the resource
     */
    protected abstract function getResourceTitle( $section );

    /**
     * Parse information about given article.
     * 
     * It may be a URI to full text of the article, or entire text of it.
     * Result of this method will be passed to the <code>parseArticle()</code>
     * method.
     * 
     * @param string $base_uri base URI of the resource
     * @param string $section the name of the section
     * @param string $section_uri relative URI of the section
     * @param string $full_uri full URI of the section
     * @param int $amount amount of articles to parse
     * @param string $section_html HTML of the section page
     * @param int $article_number number of the article
     * @return mixed information about given article
     * @throws Exception if something goes wrong
     */
    protected abstract function parseArticleInfo(
            $base_uri, $section, $section_uri, $full_uri, $amount, $section_html, $article_number );
    
    /**
     * Parse article.
     * 
     * This method must return the article. One can do additional HTTP requests
     * with the instance of HTTP client if necessary.
     * 
     * @param Client $http_client the instance of HTTP client
     * @param string $base_uri base URI of the resource
     * @param string $section the name of the section
     * @param string $section_uri relative URI of the section
     * @param string $full_uri full URI of the section
     * @param mixed $article_info information about given article returned by
     * the <code>parseArticleInfo()</code> method
     * @return Article the article
     * @throws Exception if something goes wrong
     */
    protected abstract function parseArticle(
            Client $http_client, $base_uri, $section, $section_uri, $full_uri, $article_info );
    
}
