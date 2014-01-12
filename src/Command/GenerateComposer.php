<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Campiers\JsonPretty\JsonPretty;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @package AydinHassan\MagentoCoreMapper\Command
 */
class GenerateComposer extends GenerateAbstract
{

    /**
     * Mapping Type
     */
    const MAPTYPE = 'composer';

    /**
     * Mapping File
     */
    const MAPFILE = 'composer.json';

    /**
     * Set options
     */
    protected function configure()
    {

        $this
            ->setName('generate:composer')
            ->setDescription('Create a composer mapping file for your Magento Core Package')
            ->setDefinition(array())
            ->setHelp(<<<EOT
This command creates a composer mapping file for your magento core package.

It will change to the directory you pass in as "project-root" and recursively list all files and create the mapping also in the "project-root" folder
A basic composer.json file must exist in the "project-root" dir. You can create one manually or run "composer init" to generate one interactively.
This command will add the mappings to the existing composer.json.

Also the composer mapping requires a "magento-root-dir" to be specified in the composer.json. See: https://github.com/magento-hackathon/magento-composer-installer/issues/50.

If the mappings already exist in composer.json, use -f flag or --force-write to force the write!
EOT
            )
            ->addArgument("project-root", InputArgument::REQUIRED, "The folder you wish to create the mappings in")
            ->addArgument("magento-root", InputArgument::REQUIRED, "The Magento root dir when using composer mapping")
            ->addOption("force-write", "-f", InputOption::VALUE_NONE, "If mapping exists then overwrite it");
    }

    protected function create(InputInterface $input, OutputInterface $output)
    {
        $composerFile = self::MAPFILE;
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
            if(version_compare("5.4", ">=")) {
                $json = json_encode($composerContent);
            } else {
                $jsonPretty = new JsonPretty();
                $json = $jsonPretty->prettify($composerContent);
            }

            $this->writeFile($composerFile, $json, $input, $output);

        } else {
            //composer.json does not exist - inform user to create one first
            throw new \Exception(sprintf('Composer file "%s" does not exist. Please create one in your project root "%s" using "composer init", before adding the mappings', $composerFile, $this->getProjectRoot()));
        }
    }

    /**
     * @return string
     */
    protected function getMapType()
    {
        return self::MAPTYPE;
    }
}