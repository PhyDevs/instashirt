<?php

namespace App\Entity;


interface AuthoredEntityInterface
{
    public function setAuthor(User $user): void;

    public function setPublishedDate(\DateTimeInterface $published_date): void;
}
