<?php

namespace AydinHassan\MagentoCoreMapper\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Composer\Downloader\FilesystemException;
use Composer\IO\ConsoleIO;
use Composer\Util\RemoteFilesystem;


/**
 * Class SelfUpdate
 * @package AydinHassan\MagentoCoreMapper\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SelfUpdate extends Command
{

    /**
     * Phar URL
     */
    const MCM_PHAR_URL = 'https://raw.github.com/AydinHassan/MagentoCoreMapper/master/build/mage-core-mapper.phar';

    /**
     * Github Tag API URL
     */
    const MCM_GITHUB_TAG_API = 'https://api.github.com/repos/aydinhassan/magentocoremapper/tags';

    /**
     * Set name & description
     */
    protected function configure()
    {
        $this->setName('self-update')
             ->setDescription('Update Magento Core Mapper to the latest version');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Composer\Downloader\FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localFileName  = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
        $tempFileName   = sprintf('%s/%s-temp', dirname($localFileName), basename($localFileName));

        if(!is_writeable(dirname($tempFileName))) {
            throw new FilesystemException(sprintf("Magento Core Mapper update failed. Could not write to directory: '%s'", dirname($tempFileName)));
        }

        if(!is_writeable($localFileName)) {
            throw new FilesystemException(sprintf("Magento Core Mapper update failed. Could not write to file: '%s'", $localFileName));
        }

        $io = new ConsoleIO($input, $output, $this->getHelperSet());
        $rfs = new RemoteFilesystem($io);

        $output->writeln('<comment>Checking latest version..</comment>');
        $tag = current(json_decode($rfs->getContents('api.github.com', self::MCM_GITHUB_TAG_API), true));

        //if there is a newer version of the program
        if(isset($tag['name']) && $this->getApplication()->getVersion() < $tag["name"]) {
            $output->writeln(sprintf("<comment>Updating to version: '%s'</comment>", $tag['name']));
            $rfs->copy('raw.github.com', self::MCM_PHAR_URL, $tempFileName);

            try {
                @chmod($tempFileName, 0777 & ~umask());
                //$phar = new \Phar($tempFileName);
                //unset($phar);
                @rename($tempFileName, $localFileName);
                $output->writeln("<info>Successfully update Magento Core Mapper");
                exit;

            } catch(\Exception $e) {
                @unlink($tempFileName);
                $output->writeln(sprintf("<error>There was an issue updating Magento Core Mapper: '%s'</error>", $e->getMessage()));
            }
        } else {
            $output->writeln("<comment>You are already using the latest version of Magento Core Mapper</comment>");
        }
    }
}
