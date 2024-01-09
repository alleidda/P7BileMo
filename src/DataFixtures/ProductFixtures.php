<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture 
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 50; $i++) {
            $product = new Product;
            $product->setName('Phone ' . $i);
            $product->setDescription('Description Phone ' . $i);
            $product->setPrice(mt_rand(750, 1500));
            $product->setQuantity(mt_rand(3, 8));
            $product->setModel('Model phone ' . $i);

            $manager->persist($product);
        }
        $manager->flush();
    }
}