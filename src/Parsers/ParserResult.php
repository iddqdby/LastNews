<?php

namespace IDDQDBY\LastNews\Parsers;

/**
 * Result of parsing.
 *
 * @author Sergey Protasevich
 */
class ParserResult {
    
    private $title;
    private $articles;
    private $errors;
    
    /**
     * Create result of parsing.
     * 
     * @param string $title the title
     * @param array $articles array of articles
     * @param array $errors array of errors
     */
    public function __construct( $title, array $articles, array $errors = [] ) {
        $this->title = $title;
        $this->articles = $articles;
        $this->errors = $errors;
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
     * Get articles.
     * 
     * @return array the articles
     */
    public function getArticles() {
        return $this->articles;
    }
    
    /**
     * Get errors
     * 
     * @return array the errors
     */
    public function getErrors() {
        return $this->errors;
    }

}
