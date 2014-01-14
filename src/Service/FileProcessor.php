<?php

namespace AydinHassan\MagentoCoreMapper\Service;

use \AydinHassan\MagentoCoreMapper\RecursiveFilterIterator\Mappings;

/**
 * Class FileProcessor
 * @package AydinHassan\MagentoCoreMapper\Service
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FileProcessor
{
    /**
     * Get all files within the project root dir. We allready chdir'd there
     *  - we chdir and use "." so we can get relative path and not /Users/bleh/projects/etc prepended
     * to each file
     *
     * @return \RecursiveIteratorIterator
     */
    public function processFiles()
    {
        $dirIterator    = new \RecursiveDirectoryIterator(".", \FilesystemIterator::SKIP_DOTS | \RecursiveIteratorIterator::SELF_FIRST);
        $filter         = new Mappings($dirIterator);
        $iterator       = new \RecursiveIteratorIterator($filter);
        return $iterator;
    }

} 