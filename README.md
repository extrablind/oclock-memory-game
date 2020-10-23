# Oclock Memory Game

This is a demo memory game featuring Vue.js and Symfony.
For educational purpose.

## Install

``` bash
# Clone the repo
git clone https://github.com/extrablind/oclock-memory-game
# Go to newly created dir
cd oclock-memory-game
# Install dependencies
composer install
# Create database
bin/console doctrine:database:create
bin/console doctrine:migration:migrate

# Install js dependencies and start webpack in dev mode
yarn install
yarn encore dev-server --hot
# OR production
yarn encore production

# Start symfony server
symfony serve --port=2140 --no-tls

# OR if symfony app is not installed, start build in php server with document-root in ./public dir. (not suitable for production)
php -S localhost:2140 -t ./public/

# OPTONNAL (when needed) reset the score and time : truncate table that save scores
bin/console memory:reset
```


## Main files and dirs

Quick discover of main dir for quick overview

* webpack.config.js is defining two compiled files :
    * app (for whole website) : assets/app.js, memory css
    * memory (specific to the game - vuejs) : assets/vue/src/main.js
* Vue files are in ./assets/vue/
* Templates (sf views) are in ./templates/Game/* with layout ./templates/base.html.twig
* Parts html template are in ./templates/Parts for menu : shared html parts accross pages, well we'have only one page for the moment


## Carefull

This app is not suitable for production env. out of the box ;)


## TODO

This project could probably be optimized in many ways :

* lots of external libs : lodash could probably be avoided as we use only few functions...
* bootstrap is not optimized to only load components we use...
* etc...
