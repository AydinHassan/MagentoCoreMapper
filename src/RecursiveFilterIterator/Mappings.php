<?php

namespace AydinHassan\MagentoCoreMapper\RecursiveFilterIterator;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
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
    );

    public function accept() {
        return !in_array(
            $this->current()->getFilename(),
            self::$filters,
            true
        );
    }
}
