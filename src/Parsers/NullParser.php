<?php

namespace IDDQDBY\LastNews\Parsers;

/**
 * Stub for unimplemented/invalid resource names.
 *
 * @author Sergey Protasevich
 */
class NullParser implements IParser {
    
    /**
     * {@inheritdoc}
     */
    public function parse( $section, $amount ) {
        return 'Resource is not implemented or name is invalid.';
    }

}
