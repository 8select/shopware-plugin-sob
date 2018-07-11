# shopware-plugin-sob

https://www.8select.com/8select-cse-installationsanleitung-shopware

&nbsp;
___
&nbsp;

## Development Workflow

### 1. Setup up a Shopware Test Environment
- Go to the [docker-shopware](https://github.com/8select/docker-shopware) repository and follow the instructions in the README.md
- clone this repository to your local machine

### 2. Write Code
- Go to your local clone of this repository and write code
- run `bin/copy2dev.sh [docker container name] [version]`  to copy the current state of your repository to the plugins directory within your running `docker-shopware` container; prefix your version with a reference to the JIRA issue i.e. `CSE-880` **Example**: `bin/copy2dev.sh 5217-php7_shopware_1 CSE-880`
- the above mentioned script will...
  - ... create a `staging` build within your docker container so to avaoid test outputs to the `production` environment
  - ... automatically update the installed plugin in your testshop
  - ... clear the cache of your local testshop

- now you can test your changes within your local testshop (endpoints for export and widgets from staging stack)
