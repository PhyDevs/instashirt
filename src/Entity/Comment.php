<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comments")
 */
class Comment implements AuthoredEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $published_date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shirt", inversedBy="comments")
     * @ORM\JoinColumn(name="shirt_id", referencedColumnName="id", nullable=false)
     */
    private $shirt;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getPublishedDate(): ?\DateTimeInterface
    {
        return $this->published_date;
    }

    public function setPublishedDate(\DateTimeInterface $published_date): void
    {
        $this->published_date = $published_date;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    public function getShirt(): ?Shirt
    {
        return $this->shirt;
    }

    public function setShirt(?Shirt $shirt): void
    {
        $this->shirt = $shirt;
    }
}
