<?php

if (!function_exists('includeIfExists')) {
    function includeIfExists($file)
    {
        if (file_exists($file)) {
            return include $file;
        }
    }
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}

$fileProcessor = new \AydinHassan\MagentoCoreMapper\Service\FileProcessor();
$app = new \Symfony\Component\Console\Application('Magento Core Mapper', '0.2.5');
$app->addCommands(array(
    new \AydinHassan\MagentoCoreMapper\Command\GenerateComposer(null, $fileProcessor),
    new \AydinHassan\MagentoCoreMapper\Command\GenerateModman(null, $fileProcessor),
));

return $app;
