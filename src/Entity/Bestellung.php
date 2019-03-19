<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BestellungRepository")
 */
class Bestellung
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $woche;

    /**
     * @ORM\Column(type="integer")
     */
    private $tag;
    private $tagName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zusagen;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $gericht;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lieferant;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWoche(): ?int
    {
        return $this->woche;
    }

    public function setWoche(int $woche): self
    {
        $this->woche = $woche;

        return $this;
    }

    public function getTag(): ?int
    {
        return $this->tag;
    }

    public function setTag(int $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTagName()
    {
        return $this->tagName;
    }

    public function setTagName()
    {
        switch ($this->tag) {
            case 1:
                $this->tagName = 'Montag';
                break;
            case 2:
                $this->tagName = 'Dienstag';
                break;
            case 3:
                $this->tagName = 'Mittwoch';
                break;
            case 4:
                $this->tagName = 'Donnerstag';
                break;
            case 5:
                $this->tagName = 'Freitag';
                break;
            case 6:
                $this->tagName = 'Samstag';
                break;
            case 7:
                $this->tagName = 'Sonntag';
                break;
            default:
                $this->tagName = 'Error';
                break;
        }
    }

    public function getZusagen(): ?string
    {
        return $this->zusagen;
    }

    public function setZusagen(?string $zusagen): self
    {
        $this->zusagen = $zusagen;

        return $this;
    }

    public function getGericht(): ?string
    {
        return $this->gericht;
    }

    public function setGericht(?string $gericht): self
    {
        $this->gericht = $gericht;

        return $this;
    }

    public function getLieferant(): ?string
    {
        return $this->lieferant;
    }

    public function setLieferant(?string $lieferant): self
    {
        $this->lieferant = $lieferant;

        return $this;
    }
}
