# shopware-plugin-sob

https://www.8select.com/8select-cse-installationsanleitung-shopware

&nbsp;
___
&nbsp;

## Development Workflow

### 1. Setup up a Shopware Test Environment
- Go to the [docker-shopware](https://github.com/8select/docker-shopware) repository and follow the instructions in the README.md
- clone this repository to your local machine

### 2. Develop and Test
- Go to your local clone of this repository and write code
- run `bin/copy2dev.sh [docker contianer name] [version]`  to copy the current state of your repository to the plugins directory within your running `docker-shopware` container; prefix your version with a reference to the JIRA issue i.e. `CSE-880`
- update the plugin through Shopware Plugin Manager's "local update" button (see screenshot)
- test your changes within your local Testshop

![update the plugin through Shopware's Plugin Manager](update_local_dev_plugin.png)

**Note:** The `bin/copy2dev.sh` will always create a `staging` build within your docker container so to avaoid test outputs to the `production` environment. This means product exports as well as widget integrations will use endpoints from the `staging` stack.


