<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Provider\fr_FR\PhoneNumber;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new PhoneNumber($faker));

        for($c = 0; $c < 3; $c++) {
            $customer = new Customer;

            $customer->setFullName($faker->name())
                ->setAddress($faker->address())
                ->setPostalCode(mt_rand(10000, 99999))
                ->setCity($faker->city())
                ->setEmail("user$c@gmail.com")
                ->setPhoneNumber($faker->phoneNumber());

            $manager->persist($customer);
        }

        $manager->flush();
    }
}
