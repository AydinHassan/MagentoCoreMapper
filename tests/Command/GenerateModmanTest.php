<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use AydinHassan\MagentoCoreMapper\Command\GenerateModman;

/**
 * Class GenerateModmanTest
 * @package AydinHassan\MagentoCoreMapper\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class GenerateModmanTest extends GenerateAbstractTest
{

    /**
     * @var AydinHassan\MagentoCoreMapper\Command\GenerateModman
     */
    protected $command = null;

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $fileProcessor
     * @return CommandTester
     */
    public function getCommandTester(\PHPUnit_Framework_MockObject_MockObject $fileProcessor)
    {
        $application = new Application();
        $application->add(new GenerateModman(null, $fileProcessor));

        $this->command = $application->find('generate:modman');
        return new CommandTester($this->command);
    }

    /**
     * Test that modman file is not overwritten if no -f flag passed
     */
    public function testModmanFileNotOverwrittenIfExists()
    {
        touch("$this->projectRoot/modman");
        $commandTester = $this->getCommandTester($this->getMockFileProcessor(array()));

        $this->setExpectedException('Exception', 'File "modman" already exists, run with force-write option to overwrite');
        $commandTester->execute(
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

        $commandTester = $this->getCommandTester($this->getMockFileProcessor(array('./file1.php')));
        $commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
                '-f'            => true,
            )
        );

        $content = "file1.php file1.php";
        $this->assertFileExists("$this->projectRoot/modman");
        $this->assertEquals($content, trim(\file_get_contents("$this->projectRoot/modman")));
    }

    /**
     * Exception should be thrown if file_put_contents tries to write empty file
     */
    public function testExceptionIsThrownIfNoFilesAreInProjectRoot()
    {
        $commandTester = $this->getCommandTester($this->getMockFileProcessor(array()));

        $this->setExpectedException('Exception', 'File "modman" could not be written');
        $commandTester->execute(
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
        $commandTester = $this->getCommandTester($this->getMockFileProcessor(array('./file1.php')));
        $commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
            )
        );

        $content = "file1.php file1.php";
        $this->assertFileExists("$this->projectRoot/modman");
        $this->assertEquals($content, trim(\file_get_contents("$this->projectRoot/modman")));
    }

    /**
     * Verify modman file contains correct data when multiple files present
     */
    public function testModmanMappingIsCreatedWithMultipleFiles()
    {

        $files = array('./file1.php', './folder/evenmoar.php', './folder/moarcode.php');
        $commandTester = $this->getCommandTester($this->getMockFileProcessor($files));
        $commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
            )
        );

        $content = "file1.php file1.php\nfolder/evenmoar.php folder/evenmoar.php\nfolder/moarcode.php folder/moarcode.php\n";
        $this->assertFileExists("$this->projectRoot/modman");
        $this->assertEquals($content, \file_get_contents("$this->projectRoot/modman"));
    }

} 