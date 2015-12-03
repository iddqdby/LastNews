<?php

namespace LastNews\Parsers;

use Exception;
use GuzzleHttp\Client;

/**
 * Abstract HTML parser.
 * 
 * One can use this class to implement parsers for various HTML resources such
 * as web-pages.
 *
 * @author Sergey Protasevich
 */
abstract class AbstractHtmlParser implements IParser {
    
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
                $amount = $this->getDefaultAmout( $section );
            }

            $http_options = $this
                    ->getHTTPOptions( $base_uri, $section, $section_uri, $amount );
            $http_client = $this
                    ->createHTTPClient( $http_options, $base_uri, $section, $section_uri, $amount );
        
            $section_html = $http_client
                    ->get( $full_uri )
                    ->getBody()
                    ->getContents();
        
            $articles = [
                $this->getResourceTitle( $section ),
            ];

            for( $i = 0; $i < $amount; $i++ ) {

                try {
                    $article_info = $this
                            ->parseArticleInfo( $base_uri, $section, $section_uri, $amount, $section_html, $i );
                    $article = $this
                            ->parseArticle( $http_client, $base_uri, $section, $section_uri, $article_info );
                } catch( Exception $ex ) {
                    $article = $this->createErrorText( $ex );
                }
                
                $articles[] = $article;
            }

            $articles_separator = $this->getArticlesSeparator();

            $text_final = implode( $articles_separator, $articles );
            return $text_final;
            
        } catch( Exception $ex ) {
            $text_error = $this->createErrorText( $ex );
            return $text_error;
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
     * @return Client the instance of client
     */
    protected function createHTTPClient(
            array $http_options, $base_uri, $section, $section_uri, $amount ) {
        
        return new Client( $http_options );
    }
    
    /**
     * Create text for error.
     * 
     * Override this method to create custom text.
     * 
     * @param Exception $ex the exception
     * @return string the text for HTTP error
     */
    protected function createErrorText( Exception $ex ) {
        return "ERROR:\nFail to get data.\nException:\n".$ex;
    }
    
    /**
     * Get separator string for the articles in the final text.
     * 
     * Override this method to use custom one.
     * 
     * @return string separator string for the articles
     */
    protected function getArticlesSeparator() {
        return "\n\n================\n\n";
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
     * Get default amount of articles to parse.
     * 
     * @param string $section the name of the section
     * @return int default amount of articles to parse
     */
    protected abstract function getDefaultAmout( $section );
    
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
     * @param int $amount amount of articles to parse
     * @param string $section_html HTML of the section page
     * @param int $article_number number of the article
     * @return mixed information about given article
     */
    protected abstract function parseArticleInfo(
            $base_uri, $section, $section_uri, $amount, $section_html, $article_number );
    
    /**
     * Parse article.
     * 
     * This method must return the text of the article. One can do additional
     * HTTP requests with the instance of HTTP client if necessary.
     * 
     * @param Client $http_client the instance of HTTP client
     * @param string $base_uri base URI of the resource
     * @param string $section the name of the section
     * @param string $section_uri relative URI of the section
     * @param mixed $article_info information about given article returned by
     * the <code>parseArticleInfo()</code> method
     * @return string the text of the article
     */
    protected abstract function parseArticle(
            Client $http_client, $base_uri, $section, $section_uri, $article_info );
    
}
