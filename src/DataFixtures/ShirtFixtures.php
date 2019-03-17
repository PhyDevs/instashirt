<?php

namespace App\DataFixtures;

use App\Entity\Shirt;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ShirtFixtures extends BaseFixture implements OrderedFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $slugify = Slugify::create();
        $this->createMany(Shirt::class, 20, function (Shirt $shirt) use ($slugify) {
            $shirt->setTitle($this->faker->sentence(5));
            $shirt->setSlug($slugify->slugify($shirt->getTitle()));
            $shirt->setDescription($this->faker->text(180));
            $shirt->setFrontPath($this->faker->imageUrl());
            $shirt->setBackPath($this->faker->imageUrl());
            $shirt->setPublishedDate($this->faker->dateTimeThisYear);

            $randUser = $this->getReference(User::class. '_' .rand(0,4));
            $shirt->setAuthor($randUser);
        });
        $manager->flush();
    }

    public function getOrder()
    {
        return 20;
    }
}
