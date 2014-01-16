MagentoCoreMapper
=================
[![Build Status](https://travis-ci.org/AydinHassan/MagentoCoreMapper.png?branch=master)](https://travis-ci.org/AydinHassan/MagentoCoreMapper)
[![Dependency Status](https://www.versioneye.com/user/projects/52d68e7dec13752fc5000268/badge.png)](https://www.versioneye.com/user/projects/52d68e7dec13752fc5000268)
[![Latest Stable Version](https://poser.pugx.org/aydin-hassan/magento-core-mapper/version.png)](https://packagist.org/packages/aydin-hassan/magento-core-mapper)
[![Latest Untable Version](https://poser.pugx.org/aydin-hassan/magento-core-mapper/v/unstable.png)](https://packagist.org/packages/aydin-hassan/magento-core-mapper)

A small library to create modman or composer.json mappings for Magento Core packages

Compatibility
-------------

This tool works with any version of PHP >= 5.3. It is automatically tested using Travis on version PHP versions 5.3, 5.4, 5.5 & HHVM. 

Installation
------------

### Phar
TODO

### Composer
    
    composer create-project aydin-hassan/magento-core-mapper:0.1.0
    cd magento-core-mapper
    ./bin/magento-core-mapper

### Git

    git clone https://github.com/AydinHassan/MagentoCoreMapper.git
    cd MagentoCoreMapper
    composer install
    ./bin/magento-core-mapper
    
Runnning Tests
--------------

    cd MagentoCoreMapper
    ./vendor/bin/phpunit
    
Usage
-----

This package supports to types of mappings: Modman & Composer. Each type has some different pre-requistes:
You must first download a Magento Core package from your usual sources and extract it to a folder.

### Composer
Composer type packages are installable using: [magento-core-installer](https://github.com/quafzi/magento-core-installer)

Before you can create `composer.json` mappings you must create a `composer.json` file for the package. You can do this manually or interactively. To create interactively run `composer init` in the package root and answer the questions. Then you can run:

    ./bin/magento-core-mapper generate:composer path-to-magento-package 
    
This will modify the `composer.json` file in the root of your Magento package folder and add in the mappings under the `['extra']['map']` key. 
If mappings alreasy exist then you can use the `-f` flag to force an overwrite.


### Modman
Modman type packages are installable using: [Modman](https://github.com/colinmollenhour/modman)

    ./bin/magento-core-mapper generate:modman path-to-magento-package
    
This will create a `modman` file in the root of your Magento package folder containing the mappings of all files in the package. 
If a `modman` file exists you can use the `-f` flag to force an overwrite.

