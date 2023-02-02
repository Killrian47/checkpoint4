# Checkpoint 4

## Prerequisites

For install this project, you need to have some prerequisite :
- You need to have ``composer``
- You need to have ``php``

If you don't have this one go on the documentation for the installation

## Now you can install the project

1. Clone this repository
2. Run ``composer install``

## Don't forget !

1. Create a file named ``.env.local``
2. Copy content in ``.env`` to the new file
3. Configure your environment for database (line 26 to 33)
4. To create your database, you can use the following command ``symfony console doctrine:database:create`` (or ``symfony console d:d:c``)
5. Launch all migrations for update your database with ``symfony console doctrine:migration:migrate``
6. You can launch fixtures with ``symfony console doctrine:fixtures:load``
7. And now, you're ready to launch your local server !

## Launch the local server !

Run ``symfony server:start`` to launch your local php web server

