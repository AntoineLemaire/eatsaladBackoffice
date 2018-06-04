<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionSubCategory
 *
 * @ORM\Table(name="question_sub_category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionSubCategoryRepository")
 */
class QuestionSubCategory
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="QuestionCategory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $questionCategory;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question", mappedBy="questionSubCategory")
     */
    private $questions;

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
     * Set name
     *
     * @param string $name
     *
     * @return QuestionSubCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set questionCategory
     *
     * @param \AppBundle\Entity\QuestionCategory $questionCategory
     *
     * @return QuestionSubCategory
     */
    public function setQuestionCategory(\AppBundle\Entity\QuestionCategory $questionCategory)
    {
        $this->questionCategory = $questionCategory;

        return $this;
    }

    /**
     * Get questionCategory
     *
     * @return \AppBundle\Entity\QuestionCategory
     */
    public function getQuestionCategory()
    {
        return $this->questionCategory;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add question
     *
     * @param \AppBundle\Entity\Question $question
     *
     * @return QuestionSubCategory
     */
    public function addQuestion(\AppBundle\Entity\Question $question)
    {
        $this->questions[] = $question;

        return $this;
    }

    /**
     * Remove question
     *
     * @param \AppBundle\Entity\Question $question
     */
    public function removeQuestion(\AppBundle\Entity\Question $question)
    {
        $this->questions->removeElement($question);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Generates the magic method
     *
     */
    public function __toString(){
        // to show the name of the Category in the select
        return $this->name;
        // to show the id of the Category in the select
        // return $this->id;
    }
}
