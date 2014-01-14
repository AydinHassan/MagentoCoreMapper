<?php

namespace AydinHassan\MagentoCoreMapper\Command;

/**
 * Class GenerateAbstractTest
 * @package AydinHassan\MagentoCoreMapper\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
abstract class GenerateAbstractTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string Project Root
     */
    protected $projectRoot = null;

    /**
     * Create temp directory for working in
     */
    public function setUp()
    {
        $dir = rtrim(sys_get_temp_dir(), "/") . "/" . time() . rand(0, 1000);
        echo $dir;
        mkdir($dir, 0777, true);
        $this->projectRoot = realpath($dir);
    }

    /**
     * Remove left over files
     */
    public function tearDown()
    {
        $this->clean($this->projectRoot);
    }

    /**
     * Clean al files recursively
     *
     * @param string $file
     */
    protected function clean($file)
    {
        if(is_dir($file) && !is_link($file)) {
            $dir = new \FilesystemIterator($file);
            foreach($dir as $childFile) {
                $this->clean($childFile);
            }

            rmdir($file);
        } else {
            unlink($file);
        }
    }
} 