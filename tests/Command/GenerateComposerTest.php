<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use AydinHassan\MagentoCoreMapper\Command\GenerateComposer;

/**
 * Class GenerateComposerTest
 * @package AydinHassan\MagentoCoreMapper\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class GenerateComposerTest extends GenerateAbstractTest
{

    /**
     * @var AydinHassan\MagentoCoreMapper\Command\GenerateComposer
     */
    protected $command = null;

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $fileProcessor
     * @return CommandTester
     */
    public function getCommandTester(\PHPUnit_Framework_MockObject_MockObject $fileProcessor)
    {
        $application = new Application();
        $application->add(new GenerateComposer(null, $fileProcessor));

        $this->command = $application->find('generate:composer');
        return new CommandTester($this->command);
    }

    /**
     * Mapping should fail if no composer.json exists
     */
    public function testMappingFailsIfComposerFileNotExists()
    {

        $commandTester = $this->getCommandTester($this->getMockFileProcessor(array()));

        $this->setExpectedException('Exception', sprintf('Composer file "composer.json" does not exist. Please create one in your project root "%s" using "composer init", before adding the mappings', $this->projectRoot));
        $commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
                'magento-root'  => "htdocs",
            )
        );
    }

    /**
     * Mappings should not be overwritten if no -f flag
     */
    public function testMappingsNotOverwrittenIfExistInComposerFile()
    {
        $data = \json_encode(array('extra' => array('map' => array())));
        \file_put_contents("$this->projectRoot/composer.json", $data);

        $commandTester = $this->getCommandTester($this->getMockFileProcessor(array()));

        $this->setExpectedException('Exception', 'Mappings seem to already exist in "composer.json" run with force-write option to overwrite');
        $commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                'project-root'  => $this->projectRoot,
                'magento-root'  => "htdocs",
            )
        );
    }

    /**
     * Mappings should be overwritted in -f flag passed
     */
    public function testMappingsOverwrittenIfExistInComposerFileAndForceFlagPassed()
    {

        $commandTester = $this->getCommandTester($this->getMockFileProcessor(array('./file1.php')));

        $data = \json_encode(array('extra' => array('map' => array('file1.php' => 'file1.php'))));
        \file_put_contents("$this->projectRoot/composer.json", $data);

        $commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                '-f'            => true,
                'project-root'  => $this->projectRoot,
                'magento-root'  => "htdocs",
                '-f'            => true,
            )
        );

        $expected = array(
            'extra' => array(
                'map' => array(
                    array('file1.php', 'file1.php')
                ),
                'magento-root-dir' => 'htdocs'
            ),
            'type' => 'magento-core'
        );

        $this->assertSame($expected, \json_decode(\file_get_contents("$this->projectRoot/composer.json"), true));
    }

    /**
     * If composer.json content does not decode into an array then should throw an exception
     */
    public function testExceptionIfThrownIsComposerFileInvalid()
    {
        touch("$this->projectRoot/composer.json");

        $commandTester = $this->getCommandTester($this->getMockFileProcessor(array()));

        $this->setExpectedException("Exception", 'Invalid data in "composer.json"');
        $commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                '-f'            => true,
                'project-root'  => $this->projectRoot,
                'magento-root'  => "htdocs",
            )
        );

    }

    /**
     * Verify composer.json file contains correct data
     */
    public function testComposerMappingIsCreated()
    {

        $commandTester = $this->getCommandTester($this->getMockFileProcessor(array('./file1.php')));

        $data = \json_encode(array());
        \file_put_contents("$this->projectRoot/composer.json", $data);

        $commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                '-f'            => true,
                'project-root'  => $this->projectRoot,
                'magento-root'  => "htdocs",
                '-f'            => true,
            )
        );

        $expected = array(
            'extra' => array(
                'magento-root-dir' => 'htdocs',
                'map' => array(
                    array('file1.php', 'file1.php')
                ),
            ),
            'type' => 'magento-core'
        );

        $this->assertSame($expected, \json_decode(\file_get_contents("$this->projectRoot/composer.json"), true));
    }

    /**
     * Verify composer.json contains correct data when multiple files present
     */
    public function testComposerMappingIsCreatedWithMultipleFiles()
    {

        $files = array('./file1.php', './folder/evenmoar.php', './folder/moarcode.php');
        $commandTester = $this->getCommandTester($this->getMockFileProcessor($files));

        $data = \json_encode(array());
        \file_put_contents("$this->projectRoot/composer.json", $data);

        $commandTester->execute(
            array(
                'command'       => $this->command->getName(),
                '-f'            => true,
                'project-root'  => $this->projectRoot,
                'magento-root'  => "htdocs",
                '-f'            => true,
            )
        );

        $expected = array(
            'extra' => array(
                'magento-root-dir' => 'htdocs',
                'map' => array(
                    array('file1.php', 'file1.php'),
                    array('folder/evenmoar.php', 'folder/evenmoar.php'),
                    array('folder/moarcode.php', 'folder/moarcode.php'),
                ),
            ),
            'type' => 'magento-core'
        );

        $this->assertEquals($expected, \json_decode(\file_get_contents("$this->projectRoot/composer.json"), true));
    }
} 