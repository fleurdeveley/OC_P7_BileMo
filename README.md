# OC_P7_BileMo

TODO badge codacy

## Description of the project
  * TODO

## Technologies
  * PHP 7.4
  * Symfony 5.3
  * Composer 1.10.1
  * GitHub

## PHP Dependencies
  * TODO

## Source
 1. Clone the GitHub repository :
```
  git clone https://github.com/fleurdeveley/OC_P7_BileMo.git
```

## Installation
 2. Enter the project file :
```
  cd OC_P7_BileMo
```

 3. Configurez vos variables d'environnement :
  * containers de Docker, à la racine du projet : 
```
  cp .env.example .env
```
 * serveur SMTP et base de données
```
  cp app/html/.env app/html/.env.local
```

 4. Create the docker network
```
  docker network create project7
```

 5. Launch the containers
```
  docker-composer up -d
```

 6. Enter the PHP container to launch the commands for the database
```
  docker exec -ti [nom du container php] bash
```

 7. Install php dependencies with composer
```
  composer install
```

 8. Install the database
```
  php bin/console doctrine:migrations:migrate
```

 9. Install the fixture (dummy data demo)
```
  php bin/console doctrine:fixtures:load
```

 11. Leave the container
```
  exit
```

## Database
  * Connection to PHPMyAdmin : http://localhost:8081
  * Server : project7_mysql
  * User : admin
  * Password : password

## Access to the project
  * http://localhost:8080
  * Login : TODO
  * Password : TODO

## Author 
Fleur (https://github.com/fleurdeveley)
