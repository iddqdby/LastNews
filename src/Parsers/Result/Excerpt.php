<?php

namespace IDDQDBY\LastNews\Parsers\Result;

use Exception;
use InvalidArgumentException;
use IDDQDBY\LastNews\Parsers\Result\Article;

/**
 * Result of parsing.
 *
 * @author Sergey Protasevich
 */
class Excerpt {
    
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
        
        foreach( $articles as $article ) {
            if( !$article instanceof Article ) {
                throw new InvalidArgumentException('Article is not an instance of Article class');
            }
        }
        foreach( $errors as $error ) {
            if( !$error instanceof Exception ) {
                throw new InvalidArgumentException('Error is not an instance of Exception class');
            }
        }
        
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
