<?php

namespace Knp\Bundle\PaginatorBundle\Tests\Fixture\Document;

/**
 * @Document
 */
class Article
{
    /**
     * @Id
     */
    private $id;

    /**
     * @String
     */
    private $title;

    /**
     * @String
     */
    private $type;

	/**
     * @ReferenceMany(targetDocument="Comment", mappedBy="article")
     */
    private $comments;

    public function addComment(Comment $comment)
    {
        $comment->setArticle($this);
        $this->comments[] = $comment;
    }

    public function getComments()
    {
        return $this->comments;
    }

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
}