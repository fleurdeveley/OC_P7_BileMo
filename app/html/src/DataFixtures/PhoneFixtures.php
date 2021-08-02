<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PhoneFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $brand = ['Apple', 'Samsung', 'LG', 'Sony'];
        $model = [
            'iPhone 12 Pro',
            'iPhone 12',
            'iPhone SE',
            'iPhone 11',
            'iPhone Xr',
            'Galaxy Z',
            'Galaxy S',
            'Galaxy Note',
            'Galaxy A',
            'Galaxy M',
            'LG Wing',
            'LG K52',
            'LG Velvet',
            'LG G',
            'LG Double Ecran',
            'Xperia 1 II',
            'Xperia L4',
            'Xperia 10 Plus',
            'Xperia XZ3',
            'Xperia XA Plus'
        ];
        $content = [
            'Une 5G en mode Pro. Une puce A14 Bionic qui distance nettement toutes les autres puces 
            de smart­phone. Un système photo pro qui révolutionne la prise de vues en conditions de 
            faible éclairage – de façon plus spectaculaire encore sur l’iPhone 12 Pro Max. Et le 
            Ceramic Shield, qui multiplie par quatre la résistance aux chutes. La première impression 
            est excellente. Attendez de voir la suite.',
            'L’avenir du mobile est décliné en deux riches couleurs : Noir Mystique et Bronze 
            Mystique. Choisissez le noir pour un style élégant et intemporel, ou optez pour le bronze 
            pour vous démarquer.',
            'Bienvenue dans la sphère LG des smartphones. Les smartphones LG présentent un design 
            épuré et moderne, pour une prise en main parfaite. Ainsi que de nombreux avantages et 
            accessoires mobiles dont vous ne saurez plus vous passer !',
            'Le Xperia XZ2 Compact est conçu pour vous offrir une expérience de divertissement 
            exceptionnelle. Enregistrez des vidéos à couper le souffle en 4K HDR ou regardez des 
            films au format HDR : le Xperia XZ2 Compact offre un divertissement grand écran au creux 
            de votre main.'
        ];

        for($p = 0; $p < 20; $p++) {
            $phone = new Phone;

            $phone->setBrand($faker->randomElement($brand))
                ->setModel($faker->randomElement($model))
                ->setContent($faker->randomElement($content))
                ->setPrice(mt_rand(500, 1500));

            $manager->persist($phone);
        }

        $manager->flush();
    }
}
