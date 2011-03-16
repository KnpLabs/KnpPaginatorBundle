<?php

namespace Knplabs\PaginatorBundle\Tests\Fixture\Entity;

/**
 * @Entity
 */
class Comment
{
    /** 
     * @Id 
     * @GeneratedValue 
     * @Column(type="integer") 
     */
    private $id;

    /**
     * @Column(type="text")
     */
    private $message;

    public function getId()
    {
        return $this->id;
    }
    
    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}