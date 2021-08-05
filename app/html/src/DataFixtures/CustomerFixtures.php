<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomerFixtures extends Fixture
{
    protected $hasher;
    protected $faker;
    protected $customers = [];

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        // 3 customers
        for($c = 0; $c < 3; $c++) {
            $customer = new Customer;

            $customer->setFullName($this->faker->name())
                ->setAddress($this->faker->streetAddress())
                ->setPostalCode(mt_rand(10000, 99999))
                ->setCity($this->faker->city())
                ->setEmail("user$c@gmail.com")
                ->setPhoneNumber($this->faker->phoneNumber());

            $manager->persist($customer);

            $this->customers[] = $customer;
        }

        // 30 users
        for($u = 0; $u < 30; $u++) {
            $user = new User;

            $user->setEmail("user$u@gmail.com")
                ->setPassword($this->hasher->hashPassword($user, 'password'))
                ->setRoles(['ROLE_USER'])
                ->setFullName($this->faker->name())
                ->setCustomer($this->faker->randomElement($this->customers));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
