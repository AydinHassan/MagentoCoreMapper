<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class GenerateCommand extends Command
{

    /**
     * Modman
     */
    const TYPE_MODMAN = 'modman';

    /**
     * Composer
     */
    const TYPE_COMPOSER = 'composer';

    /**
     * @var array Mapping Attributes
     */
    protected static $mappingTypes = array(
        self::TYPE_MODMAN       => array("file" => "modman"),
        self::TYPE_COMPOSER     => array("file" => "composer.json"),
    );

    /**
     * @var string
     */
    protected $projectRoot;

    /**
     * Set options
     */
    protected function configure()
    {

        $this
            ->setName('generate')
            ->setDescription('Create a mapping file for your Magento Core Package')
            ->setDefinition(array())
            ->setHelp(<<<EOT
This command creates a mapping file for your magento core package. It can be configured to create either of the following mappings:
    -> composer.json
    -> modman


It will change to the directory you pass in as "project-root" and recursively list all files and create the mapping also in the "project-root" folder
For composer mapping, a basic composer.json file must exist in the "project-root" dir. You can create one manually or run "composer init" to generate one interactively.
This command will add the mappings to the existing composer.json.

Also the composer mapping requires a "magento-root-dir" to be specified in the composer.json. See: https://github.com/magento-hackathon/magento-composer-installer/issues/50.

If the modman file or the mappings already exist in composer.json, use -f flag or --force-write to force the write!


EOT
            )
            ->addArgument("map-type", InputArgument::REQUIRED, "The type of mapping you require")
            ->addArgument("project-root", InputArgument::REQUIRED, "The folder you wish to create the mappings in")
            ->addArgument("magento-root", InputArgument::REQUIRED, "The Magento root dir when using composer mapping")
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
        $mapType = $input->getArgument("map-type");

        if(!in_array($mapType, array_keys(self::$mappingTypes))) {
            throw new \InvalidArgumentException(sprintf('Given Mapping Type "%s" is invalid. Valid types are "%s"', $mapType, implode(", ", array_keys(self::$mappingTypes))));
        }

        $projectRoot = $input->getArgument("project-root");
        $magentoRoot = $input->getArgument("magento-root");

        if($mapType == self::TYPE_COMPOSER) {
            if(!$magentoRoot) {
                throw new \InvalidArgumentException('When using Composer mappings you need to specify a Magento root folder');
            }
        }

        if(!is_readable($projectRoot)) {
            throw new \InvalidArgumentException(sprintf('Given project root "%s" is not readable or does not exist', $projectRoot));
        }
        $this->setProjectRoot($projectRoot);

        $output->writeln("");
        $output->writeln(sprintf("<info>Creating Map In:</info> <comment>%s</comment>", realpath($projectRoot)));
        $output->writeln(sprintf("<info>Map Type:</info> <comment>%s</comment>", $mapType));


        switch($mapType) {
            case self::TYPE_MODMAN:
                $this->createModmanMap($input, $output);
                break;
            case self::TYPE_COMPOSER:
                $this->createComposerMap($input, $output);
                break;
        }

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function createComposerMap(InputInterface $input, OutputInterface $output)
    {
        $composerFile = self::$mappingTypes[self::TYPE_COMPOSER]['file'];

        if(is_readable($composerFile)) {
            //composer.json exists
            $composerContent = json_decode(file_get_contents($composerFile), true);

            if(!isset($composerContent['extra'])) {
                $composerContent['extra'] = array();
            }

            if(isset($composerContent['extra']['map']) && !$input->getOption("force-write")) {
                throw new \Exception(sprintf('Mappings seem to already exist in "%s" run with force-write option to overwrite', $composerFile));
            }

            $composerContent['type'] = "magento-core";
            $composerContent['extra']['magento-root-dir'] = $input->getOption("magento-root");
            $composerContent['extra']['map'] = array();

            foreach($this->processFiles() as $file) {
                $file = substr($file, 2);
                $composerContent['extra']['map'][] = array($file, $file);
            }

            //use Composer JsonEncoder as it can pretty print on php < 5.4
            $json = JsonFile::encode($composerContent);
            $this->writeFile($composerFile, $json, $input, $output);

        } else {
            //composer.json does not exist - inform user to create one first
            throw new \Exception(sprintf('Composer file "%s" does not exist. Please create one in your project root "%s" using "composer init", before adding the mappings', $composerFile, $this->getProjectRoot()));
        }
    }

    /**
     * Modman mapping
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function createModmanMap(InputInterface $input, OutputInterface $output)
    {

        $fileName = self::$mappingTypes[self::TYPE_MODMAN]['file'];
        if(file_exists($fileName) && !$input->getOption("force-write")) {
            throw new \Exception(sprintf('File "%s" already exists, run with force-write option to overwrite', $fileName));
        }

        $content = "";
        foreach($this->processFiles() as $file) {
            $file = substr($file, 2);
            $content .= sprintf("%s %s\n", $file, $file);
        }

        $this->writeFile($fileName, $content, $input, $output);
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
     * Get all files within the project root dir. We allready chdir'd there
     *  - we chdir and use "." so we can get relative path and not /Users/bleh/projects/etc prepended
     * to each file
     *
     * @return \RecursiveIteratorIterator
     */
    protected function processFiles()
    {
        $dirIterator    = new \RecursiveDirectoryIterator(".", \FilesystemIterator::SKIP_DOTS);
        $filter         = new \AydinHassan\MagentoCoreMapper\RecursiveFilterIterator\Mappings($dirIterator);
        $iterator       = new \RecursiveIteratorIterator($filter);
        return $iterator;
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
}
