<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Sport;
use Faker\Factory;

class AppFixtures extends Fixture
{
	protected $faker;

    public function load(ObjectManager $manager)
    {
       	$this->faker = Factory::create();
       	for ($i = 0; $i < 1000000; $i++) {
            $product = new Sport();
            $product->setName($this->faker->name);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
