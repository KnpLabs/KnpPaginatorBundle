<?php

namespace Knplabs\PaginatorBundle\Tests\Fixture\Entity;

/**
 * @Entity
 */
class Article
{
    /** 
     * @Id
     * @GeneratedValue
     * @Column(type="integer") 
     */
    private $id;

    /**
     * @Column(length=128)
     */
    private $title;
    
    /**
     * @Column(length=16)
     */
    private $type;
    
    /**
     * @ManyToMany(targetEntity="Comment")
     * @JoinTable(
     *  name="articles_comments",
     *  joinColumns={@JoinColumn(name="article_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="comment_id", referencedColumnName="id", unique=true)}
     * )
     */
    private $comments;

    
    public function __construct() {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
    
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;
    }

    public function getComments()
    {
        return $this->comments;
    }
}