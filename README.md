# MapApp

This project was generated with [Angular CLI](https://github.com/angular/angular-cli) version 9.0.3 and PHP version 7.4.4

#Running the application

Go into Frontend folder and run npm install and after that ng serve. Navigate to `http://localhost:4200/`

## Frontend Build

Go into Frontend folder and run `ng build` to build the project. The build artifacts will be stored in the `dist/` directory. Use the `--prod` flag for a production build.

## Running frontend unit tests

Go into Frontend folder and run `ng test` to execute the unit tests via [Karma](https://karma-runner.github.io).

## Running frontend end-to-end tests

Go into Frontend folder and run `ng e2e` to execute the end-to-end tests via [Protractor](http://www.protractortest.org/).

## Running backend unit tests

Go into application's main folder where the vendor folder is situated and run `./vendor/bin/phpunit tests` to execute the phpunit tests.

## Updating backend

Go into application's main folder where the vendor folder is situated and run `php composer.phar install` and then 'php composer.phar dumpautoload -0'