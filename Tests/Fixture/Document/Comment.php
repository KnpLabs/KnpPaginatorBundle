<?php

namespace Knp\Bundle\PaginatorBundle\Tests\Fixture\Document;

/**
 * @Document
 */
class Comment
{
    /**
     * @Id
     */
    private $id;

    /**
     * @Column(type="text")
     */
    private $message;

    /**
     * @ReferenceOne(targetDocument="Article", inversedBy="comments")
     */
    private $article;

    public function setArticle($article)
    {
        $this->article = $article;
    }

    public function getArticle()
    {
        return $this->article;
    }

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