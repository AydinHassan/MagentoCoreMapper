<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateModman
 * @package AydinHassan\MagentoCoreMapper\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class GenerateModman extends GenerateAbstract
{

    /**
     * Mapping Type
     */
    const MAPTYPE = 'modman';

    /**
     * Mapping File
     */
    const MAPFILE = 'modman';

    /**
     * Set options
     */
    protected function configure()
    {

        parent::configure();

        $this
            ->setName('generate:modman')
            ->setDescription('Create a modman mapping file for your Magento Core Package')
            ->setHelp(<<<EOT
This command creates a modman mapping file for your magento core package.

It will change to the directory you pass in as "project-root" and recursively list all files and create the mapping also in the "project-root" folder

If the modman file already exists, use -f flag or --force-write to force the write!
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
        $fileName = self::MAPFILE;
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
     * @return string
     */
    protected function getMapType()
    {
        return self::MAPTYPE;
    }

} 