<?php 
namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait Timestamp
{
    #[ORM\Column(type: 'datetime')]
    private $created_at;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->created_at = new \DateTime;
    }

    
}