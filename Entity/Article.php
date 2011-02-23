<?php

namespace Knplabs\PaginatorBundle\Entity;

/**
 * @orm:Table(name="test_articles")
 * @orm:Entity
 */
class Article
{
    /**
     * @var integer $id
     *
     * @orm:Column(type="integer")
     * @orm:Id
     * @orm:GeneratedValue
     */
    private $id;

    /**
     * @var string $title
     *
     * @validation:NotBlank()
     * @orm:Column(length=64)
     */
    private $title;

    /**
     * @var text $content
     *
     * @validation:NotBlank()
     * @orm:Column(type="text")
     */
    private $content;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param text $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return text $content
     */
    public function getContent()
    {
        return $this->content;
    }
}