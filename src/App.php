<?php

namespace LastNews;

/**
 * Main class of the application.
 *
 * @author Sergey Protasevich
 */
class App {
    
    private $parser;
    private $section;
    private $amount;
    
    /**
     * Create new instance of the app for given resource, amount of news
     * (optional), and section (optional).
     * 
     * @param string $resource the name of the resource
     * @param int $amount amount of news (optional, if <= 0 then default value
     * for the given resource will be used)
     * @param string $section the name of the section (optional, empty string
     * for default value)
     */
    public function __construct( $resource, $amount = 0, $section = '' ) {
        $this->parser = ParserProvider::getInstance()->getParser( $resource );
        $this->amount = $amount;
        $this->section = $section;
    }

    /**
     * Parse news and get plain text.
     * 
     * @return string plain text of parsed news
     */
    public function parse() {
        return $this
                ->parser
                ->parse( $this->section, $this->amount );
    }
    
    /**
     * Run the applicaton from CLI.
     * 
     * @param int $argc the number of arguments passed to script
     * @param array $argv array of arguments passed to script
     */
    public static function main( $argc, array $argv ) {
        
        if( 1 >= $argc ) {
            echo "Usage: <script_name> [resource] [amount (optional)] [section (optional)]\n";
            return;
        }
        
        $resource = @$argv[1];
        $amount = (int)@$argv[2];
        $section = @$argv[3];
        
        $app = new self( $resource, $amount, $section );
        $output = $app->parse();
        
        echo $output."\n";
    }
    
}
