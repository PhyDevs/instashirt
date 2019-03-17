<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends BaseFixture implements OrderedFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(User::class, 5, function (User $user, int $count) {
            $user->setUsername( str_replace('.','_',$this->faker->userName));
            $user->setEmail($this->faker->email);
            $user->setPassword('password'. ($count+1) );
            if($count === 0) {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }
        });
        $manager->flush();
    }

    public function getOrder()
    {
        return 10;
    }
}
