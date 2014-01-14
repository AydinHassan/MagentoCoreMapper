<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use AydinHassan\MagentoCoreMapper\Service\FileProcessor;

/**
 * Class GenerateAbstract
 * @package AydinHassan\MagentoCoreMapper\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
abstract class GenerateAbstract extends Command
{

    /**
     * @var string
     */
    protected $projectRoot;

    /**
     * @var \AydinHassan\MagentoCoreMapper\Service\FileProcessor
     */
    protected $fileProcessor;

    /**
     * @param null $name
     * @param FileProcessor $fileProcessor
     */
    public function __construct($name = null, FileProcessor $fileProcessor)
    {
        $this->fileProcessor = $fileProcessor;
        parent::__construct($name);
    }

    /**
     * @param string $projectRoot
     * @return $this
     */
    protected function setProjectRoot($projectRoot)
    {
        $this->projectRoot = $projectRoot;
        chdir($projectRoot);
        return $this;
    }

    /**
     * @return string
     */
    protected function getProjectRoot()
    {
        return $this->projectRoot;
    }

    /**
     * Write the content
     * Overwrite if force option is specified
     *
     * @param string $fileName
     * @param string $content
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function writeFile($fileName, $content, InputInterface $input, OutputInterface $output)
    {
        if(!@file_put_contents($fileName, $content)) {
            throw new \Exception(sprintf('File "%s" could not be written', $fileName));
        }

        $output->writeln(sprintf('<info>Successfully wrote file:</info> <comment>%s</comment>', $fileName));
    }

    /**
     * Add default args & options
     */
    protected function configure()
    {
        $this->setDefinition(array())
             ->addArgument("project-root", InputArgument::REQUIRED, "The folder you wish to create the mappings in")
             ->addOption("force-write", "-f", InputOption::VALUE_NONE, "If mapping exists then overwrite it");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectRoot = $input->getArgument("project-root");

        if(!is_readable($projectRoot)) {
            throw new \InvalidArgumentException(sprintf('Given project root "%s" is not readable or does not exist', $projectRoot));
        }
        $this->setProjectRoot($projectRoot);

        $output->writeln("");
        $output->writeln(sprintf("<info>Creating Map In:</info> <comment>%s</comment>", realpath($projectRoot)));
        $output->writeln(sprintf("<info>Map Type:</info> <comment>%s</comment>", $this->getMapType()));

        // Call extending class
        $this->create($input, $output);
    }

    /**
     * Create the mapping
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected abstract function create(InputInterface $input, OutputInterface $output);


    /**
     * Return the Map Type
     *
     * @return string
     */
    protected abstract function getMapType();

}