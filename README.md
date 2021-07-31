# OC_P6_SnowTricks

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/5a451dd063364417bfce07175fbed8e2)](https://www.codacy.com/gh/fleurdeveley/OC_P6_SnowTricks/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=fleurdeveley/OC_P6_SnowTricks&amp;utm_campaign=Badge_Grade)

## Description of the project
  * As a part of study project, creation of a community snowboard site.

## Technologies
  * PHP 7.4
  * Symfony 5.3
  * Composer 1.10.1
  * Bootstrap 5.0
  * MVC
  * GitHub

## PHP Dependencies
  * "cocur/slugify": "^4.0",
  * "symfony/asset": "5.3.*",
  * "symfony/mailer": "5.3.*",
  * "symfony/security-bundle": "5.3.*",
  * "symfony/form": "5.3.*",
  * "symfony/validator": "5.3.*",

## Source
 1. Clone the GitHub repository :
```
  git clone https://github.com/fleurdeveley/OC_P6_SnowTricks.git
```

## Installation
 2. Enter the project file :
```
  cd OC_P6_Snowtricks
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
  docker network create project6
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
  * Server : project6_mysql
  * User : admin
  * Password : password

## Access to the project
  * http://localhost:8080
  * Login : user1@gmail.com
  * Password : password

## Author 
Fleur (https://github.com/fleurdeveley)
# OC_P7_BileMo
