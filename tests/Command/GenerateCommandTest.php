<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Tester\CommandTester;
use AydinHassan\MagentoCoreMapper\Command\GenerateCommand;
use Symfony\Component\Console\Application;


class GenerateCommandTest extends \PHPUnit_Framework_TestCase
{

    protected $magentoRoot = null;

    protected $projectRoot = null;

    public function setUp()
    {
        $this->magentoRoot = __DIR__ . "/../magentotestinstall/htdocs";
        $this->projectRoot = __DIR__ . "/../magentotestinstall";

        if(!file_exists($this->magentoRoot)) {
            mkdir($this->magentoRoot, 0777, true);
        }
    }

    public function testModmanFileNotOverwrittenIfExists()
    {
        touch("$this->projectRoot/modman");

        $application = new Application();
        $application->add(new GenerateCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $this->setExpectedException('Exception', 'File "modman" already exists, run with force-write option to overwrite');

        $commandTester->execute(
            array(
                'command'       => $command->getName(),
                'magento-root'  => $this->magentoRoot,
                'project-root'  => $this->projectRoot,
                'map-type'      => 'modman',
            )
        );
    }

    public function testModmanFileOverwrittenIfExistsAndForceFlagPassed()
    {
        touch("$this->projectRoot/modman");

        $application = new Application();
        $application->add(new GenerateCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);

        $this->setExpectedException('Exception', 'File "modman" already exists, run with force-write option to overwrite');

        $commandTester->execute(
            array(
                'command'       => $command->getName(),
                'magento-root'  => $this->magentoRoot,
                'project-root'  => $this->projectRoot,
                'map-type'      => 'modman',
            )
        );
    }

    public function testModmanMappingIsCreated()
    {
        touch("$this->magentoRoot/file1.php");

        $application = new Application();
        $application->add(new GenerateCommand());

        $command = $application->find('generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'       => $command->getName(),
                'magento-root'  => $this->magentoRoot,
                'project-root'  => $this->projectRoot,
                'map-type'      => 'modman',
            )
        );

        $this->assertTrue(true);
    }

    public function tearDown()
    {
        unlink("$this->projectRoot/modman");
        if(file_exists("$this->magentoRoot/file1.php")) {
            unlink("$this->magentoRoot/file1.php");
        }
    }

    public static function tearDownAfterClass()
    {
        rmdir(__DIR__ . "/../magentotestinstall/htdocs");
        rmdir(__DIR__ . "/../magentotestinstall");
    }
} 