# 8SELECT - Plugin for Shopware 5

## Installation manual

https://www.8select.com/8select-cse-installationsanleitung-shopware

### Generate new manual

**de**
```
pandoc --standalone --toc --metadata=title:"8select Curated Shopping Engine - Modul für Shopware" -o anleitung.html docs/de/installationsanleitung.md
```

**en** 
```
pandoc --standalone --toc --metadata=title:"8select Curated Shopping Engine - Modul für Shopware" -o anleitung-en.html docs/en/installation.md
```

## Development Workflow

### 1. Setup up a Shopware Test Environment

- Go to the [docker-shopware](https://github.com/8select/docker-shopware) repository and follow the instructions in the README.md
- clone this repository to your local machine

### 2. Write Code

- Go to your local clone of this repository and write code
- run `bin/copy2dev.sh [version] [profile] [docker container name]` to copy the current state of your repository to the plugins directory within your running `docker-shopware` container
    - name your version with a version number that is higher than the current stable release and prefix it with a release candidate
    - **Example** (if the current stable version is 1.5.3): `bin/copy2dev.sh 1.6.0-RC1 staging 5217-php7_shopware_1`
- the above mentioned script will...

  - ... create a `staging` build within your docker container so to avaoid test outputs to the `production` environment
  - ... automatically update the installed plugin in your testshop
  - ... clear the cache of your local testshop

- now you can test your changes within your local testshop (endpoints for export and widgets from staging stack)

### 3. Debugging

- use https://github.com/FriendsOfShopware/FroshProfiler

### 4. Run Unit Tests

**Note: Running PHP Unit Tests for the 8select CSE plugin requires PHP 7.0 or higher. Please check the PHP version installed on your local web server before installing or using [phpunit](https://phpunit.de/)**
- for compatibility with PHP v7.1 or higher use phpunit v6.5 or higher
- for compatibilty with PHP v7.0 use phpunit max. v6.4.44
- for more information see https://github.com/sebastianbergmann/phpunit/wiki 

**How to run:** 
- Make sure to have [phpunit](https://phpunit.de/) installed on your local web server
- Make sure you have the 8select CSE plugin installed in your local shopware environment
- Open a terminal to your server and move to directory `/shopware/custom/plugins/CseEightSelectBasic`
- run `phpunit`
