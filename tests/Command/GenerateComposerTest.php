<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Tester\CommandTester;
use AydinHassan\MagentoCoreMapper\Command\GenerateComposer;
use Symfony\Component\Console\Application;

/**
 * Class GenerateComposerTest
 * @package AydinHassan\MagentoCoreMapper\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class GenerateComposerTest extends \PHPUnit_Framework_TestCase
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
     * @var \AydinHassan\MagentoCoreMapper\Command\GenerateComposer
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
        $application->add(new GenerateComposer());

        $this->command = $application->find('generate:composer');
        $this->commandTester = new CommandTester($this->command);

    }

    /**
     * Mapping should fail if no composer.json exists
     */
    public function testMappingFailsIfComposerFileNotExists()
    {
        $this->setExpectedException('Exception', sprintf('Composer file "composer.json" does not exist. Please create one in your project root "%s" using "composer init", before adding the mappings', $this->projectRoot));

        $this->commandTester->execute(
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

        $this->setExpectedException('Exception', 'Mappings seem to already exist in "composer.json" run with force-write option to overwrite');

        $this->commandTester->execute(
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

        touch("$this->projectRoot/test.php");

        $data = \json_encode(array('extra' => array('map' => array('file1.php' => 'file1.php'))));
        \file_put_contents("$this->projectRoot/composer.json", $data);

        $this->commandTester->execute(
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
                    array('test.php', 'test.php')
                ),
                'magento-root-dir' => 'htdocs'
            ),
            'type' => 'magento-core'
        );

        $this->assertSame($expected, \json_decode(\file_get_contents("$this->projectRoot/composer.json"), true));
        unlink("$this->projectRoot/test.php");
    }

    /**
     * If composer.json content does not decode into an array then should throw an exception
     */
    public function testExceptionIfThrownIsComposerFileInvalid()
    {

        touch("$this->projectRoot/composer.json");

        $this->setExpectedException("Exception", 'Invalid data in "composer.json"');
        $this->commandTester->execute(
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

        touch("$this->projectRoot/test.php");
        $data = \json_encode(array());
        \file_put_contents("$this->projectRoot/composer.json", $data);

        $this->commandTester->execute(
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
                    array('test.php', 'test.php')
                ),
            ),
            'type' => 'magento-core'
        );

        $this->assertSame($expected, \json_decode(\file_get_contents("$this->projectRoot/composer.json"), true));
        unlink("$this->projectRoot/test.php");
    }

    /**
     * Verify composer.json contains correct data when multiple files present
     */
    public function testComposerMappingIsCreatedWithMultipleFiles()
    {

        touch("$this->projectRoot/file1.php");
        mkdir("$this->projectRoot/folder");
        touch("$this->projectRoot/folder/evenmoar.php");
        touch("$this->projectRoot/folder/moarcode.php");

        $data = \json_encode(array());
        \file_put_contents("$this->projectRoot/composer.json", $data);

        $this->commandTester->execute(
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

        $this->assertSame($expected, \json_decode(\file_get_contents("$this->projectRoot/composer.json"), true));

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
        if(file_exists("$this->projectRoot/composer.json")) {
            unlink("$this->projectRoot/composer.json");
        }
        rmdir(__DIR__ . "/../magentotestinstall");
    }

} 