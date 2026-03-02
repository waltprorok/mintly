# Mintly
Personal Budget App

### Requirements

    PHP      8.2
    Composer 2
    Laravel  11
    Database MySQL 8.0
    NodeJS   18.20.8
    NPM      10.8.2

### Getting started

Clone the repository

    git clone https://github.com/waltprorok/mintly.git

Switch to the repo folder

    cd mintly

Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Generate a new application key

    php artisan key:generate

Install and build node dependencies

    npm install
    npm run dev

Run the database migrations (**Set the database connection in .env before migrating**)

    DB_CONNECTION=sqlite
    DB_HOST=127.0.0.1
    DB_DATABASE=/mintly/database/database.sqlite
    
    php artisan migrate

Run the Queue

    php artisan queue:work

Database seeding

    php artisan db:seed

Start the local development server

    php artisan serve

You can now access the server at http://localhost:8000
