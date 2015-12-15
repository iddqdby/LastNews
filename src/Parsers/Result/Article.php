<?php

namespace IDDQDBY\LastNews\Parsers\Result;

/**
 * The article.
 *
 * @author Sergey Protasevich
 */
class Article {
    
    private $title;
    private $text;
    private $uri;
    
    /**
     * New article.
     * 
     * @param string $title the title
     * @param string $text the text
     * @param string $uri the URI
     */
    public function __construct( $title, $text, $uri ) {
        $this->title = $title;
        $this->text = $text;
        $this->uri = $uri;
    }

    /**
     * Get title.
     * 
     * @return string the title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Get text.
     * 
     * @return string the text
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Get URI.
     * 
     * @return string the URI
     */
    public function getURI() {
        return $this->uri;
    }

}
