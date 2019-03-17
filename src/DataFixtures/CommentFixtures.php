<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Shirt;
use App\Entity\User;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CommentFixtures extends BaseFixture implements OrderedFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(Comment::class, 100, function (Comment $comment) {
            $comment->setContent($this->faker->realText(170));

            $randUser = $this->getReference(User::class. '_' .rand(0,4));
            $comment->setAuthor($randUser);

            $randShirt = $this->getReference(Shirt::class. '_' .rand(0,19));
            $comment->setShirt($randShirt);
            $comment->setPublishedDate(
                (new \DateTime(
                    $randShirt->getPublishedDate()->format('Y-m-d\TH:i:s')
                ))->add(new \DateInterval('P'.rand(0,30).'D'))
            );
        });
        $manager->flush();
    }

    public function getOrder()
    {
        return 30;
    }
}
