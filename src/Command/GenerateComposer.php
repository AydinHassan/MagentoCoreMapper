<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateComposer
 * @package AydinHassan\MagentoCoreMapper\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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

        parent::configure();

        $this
            ->setName('generate:composer')
            ->setDescription('Create a composer mapping file for your Magento Core Package')
            ->setHelp(<<<EOT
This command creates a composer mapping file for your magento core package.

It will change to the directory you pass in as "project-root" and recursively list all files and create the mapping also in the "project-root" folder
A basic composer.json file must exist in the "project-root" dir. You can create one manually or run "composer init" to generate one interactively.
This command will add the mappings to the existing composer.json.

If the mappings already exist in composer.json, use -f flag or --force-write to force the write!
EOT
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function create(InputInterface $input, OutputInterface $output)
    {
        $composerFile = self::MAPFILE;
        if(is_readable($composerFile)) {
            //composer.json exists
            $composerContent = json_decode(file_get_contents($composerFile), true);

            if(!\is_array($composerContent)) {
                throw new \Exception(sprintf('Invalid data in "%s"', $composerFile));
            }

            if(!isset($composerContent['extra'])) {
                $composerContent['extra'] = array();
            }

            if(isset($composerContent['extra']['map']) && !$input->getOption("force-write")) {
                throw new \Exception(sprintf('Mappings seem to already exist in "%s" run with force-write option to overwrite', $composerFile));
            }

            $createRequire = false;
            if(isset($composerContent['require']) && is_array($composerContent['require'])) {
                if(!\array_key_exists('quafzi/magento-core-installer', $composerContent['require'])) {
                    $createRequire = true;
                }
            } elseif(!isset($composerContent['require'])) {
                $createRequire = true;
            }

            if($createRequire) {
                $composerContent['require']['quafzi/magento-core-installer'] = "*";
            }

            $composerContent['type'] = "magento-core";
            $composerContent['extra']['map'] = array();

            foreach($this->fileProcessor->processFiles() as $file) {
                $file = substr($file, 2);
                $composerContent['extra']['map'][] = array($file, $file);
            }

            $json = \AydinHassan\MagentoCoreMapper\Helper\Json::encode($composerContent);
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