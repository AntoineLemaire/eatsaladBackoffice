<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="question", type="string", length=255)
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity="QuestionSubCategory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $questionSubCategory;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set question
     *
     * @param string $question
     *
     * @return Question
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set questionSubCategory
     *
     * @param \AppBundle\Entity\QuestionSubCategory $questionSubCategory
     *
     * @return Question
     */
    public function setQuestionSubCategory(\AppBundle\Entity\QuestionSubCategory $questionSubCategory)
    {
        $this->questionSubCategory = $questionSubCategory;

        return $this;
    }

    /**
     * Get questionSubCategory
     *
     * @return \AppBundle\Entity\QuestionSubCategory
     */
    public function getQuestionSubCategory()
    {
        return $this->questionSubCategory;
    }

    /**
     * Generates the magic method
     *
     */
    public function __toString(){
        // to show the name of the Category in the select
        return $this->question;
        // to show the id of the Category in the select
        // return $this->id;
    }
}
