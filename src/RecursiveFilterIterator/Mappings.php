<?php

namespace AydinHassan\MagentoCoreMapper\RecursiveFilterIterator;


/**
 * Class Mappings
 * @package AydinHassan\MagentoCoreMapper\RecursiveFilterIterator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Mappings extends \RecursiveFilterIterator
{

    public static $filters = array(
        '.DS_Store',
        '.svn',
        '.idea',
        '.git',
        'composer.json',
        'composer.phar',
        'modman',
        '.gitignore',
        'vendor',
    );

    public function accept() {
        return !in_array(
            $this->current()->getFilename(),
            self::$filters,
            true
        );
    }
}
