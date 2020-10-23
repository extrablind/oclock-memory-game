<?php

namespace App\Entity;

use App\Repository\MemoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MemoryRepository::class)
 */
// This class will define our memory entity : the representation of a finished game saved by user for posterity

class Memory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3)
     * We only use 3 character (length) to remember old school flipper system
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * A score never contain any floating point
     */
    private $score;

    /**
     * @ORM\Column(type="datetime")
     * The date is saved as well
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    // Time when the game has been completed : in secs
    private $time;

    /*
    Easily retrieve info from object with getter and setters below
    */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }
}
