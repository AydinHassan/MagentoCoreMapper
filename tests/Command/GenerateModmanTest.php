<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Tester\CommandTester;
use AydinHassan\MagentoCoreMapper\Command\GenerateModman;
use Symfony\Component\Console\Application;

/**
 * Class GenerateModmanTest
 * @package AydinHassan\MagentoCoreMapper\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class GenerateModmanTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string Project Root
     */
    protected $projectRoot = null;

    /**
     * @var \Symfony\Component\Console\Tester\CommandTester
     */
    protected $commandTester = null;

    /**
     * @var \AydinHassan\MagentoCoreMapper\Command\GenerateModman
     */
    protected $command = null;

    /**
     * Create project root dir and set up app + command
     */
    public function setUp()
    {
        $this->projectRoot = __DIR__ . "/../magentotestinstall";

        if(!file_exists($this->projectRoot)) {
            mkdir($this->projectRoot, 0777, true);
        }

        $application = new Application();
        $application->add(new GenerateModman());

        $this->command = $application->find('generate:modman');
        $this->commandTester = new CommandTester($this->command);

    }

    /**
     * Test that modman file is not overwritten if no -f flag passed
     */
    public function testModmanFileNotOverwrittenIfExists()
    {
        touch("$this->projectRoot/modman");

        $this->setExpectedException('Exception', 'File "modman" already exists, run with force-write option to overwrite');

        $this->commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
            )
        );
    }

    /**
     * Test that modman file is overwritten if -f flag passed
     */
    public function testModmanFileOverwrittenIfExistsAndForceFlagPassed()
    {
        touch("$this->projectRoot/modman");
        touch("$this->projectRoot/file1.php");

        $this->commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
                '-f'            => true,
            )
        );

        $content = "file1.php file1.php";
        $this->assertFileExists("$this->projectRoot/modman");
        $this->assertEquals($content, trim(\file_get_contents("$this->projectRoot/modman")));
        unlink("$this->projectRoot/file1.php");
    }

    /**
     * Exception should be thrown if file_put_contents tries to write empty file
     */
    public function testExceptionIsThrownIfNoFilesAreInProjectRoot()
    {
        $this->setExpectedException('Exception', 'File "modman" could not be written');

        $this->commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
            )
        );
    }

    /**
     * Verify modman file contains correct data
     */
    public function testModmanMappingIsCreated()
    {
        touch("$this->projectRoot/file1.php");

        $this->commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
            )
        );

        $content = "file1.php file1.php";
        $this->assertFileExists("$this->projectRoot/modman");
        $this->assertEquals($content, trim(\file_get_contents("$this->projectRoot/modman")));
        unlink("$this->projectRoot/file1.php");

    }

    /**
     * Verify modman file contains correct data when multiple files present
     */
    public function testModmanMappingIsCreatedWithMultipleFiles()
    {
        touch("$this->projectRoot/file1.php");
        mkdir("$this->projectRoot/folder");
        touch("$this->projectRoot/folder/evenmoar.php");
        touch("$this->projectRoot/folder/moarcode.php");

        $this->commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
            )
        );

        $content = "file1.php file1.php\nfolder/evenmoar.php folder/evenmoar.php\nfolder/moarcode.php folder/moarcode.php";
        $this->assertFileExists("$this->projectRoot/modman");
        $this->assertEquals($content, trim(\file_get_contents("$this->projectRoot/modman")));

        unlink("$this->projectRoot/file1.php");
        unlink("$this->projectRoot/folder/moarcode.php");
        unlink("$this->projectRoot/folder/evenmoar.php");
        rmdir("$this->projectRoot/folder");
    }

    /**
     * Remove left over files
     */
    public function tearDown()
    {
        if(file_exists("$this->projectRoot/modman")) {
            unlink("$this->projectRoot/modman");
        }
        rmdir(__DIR__ . "/../magentotestinstall");
    }

} 