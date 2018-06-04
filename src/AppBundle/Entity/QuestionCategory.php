<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionCategory
 *
 * @ORM\Table(name="question_category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionCategoryRepository")
 */
class QuestionCategory
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
     * @ORM\OneToMany(targetEntity="QuestionSubCategory", mappedBy="questionCategory")
     */
    private $questionSubCategories; // Notez le « s », une annonce est liée à plusieurs candidatures

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
     * @return QuestionCategory
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
     * Constructor
     */
    public function __construct()
    {
        $this->questionSubCategories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add questionSubCategory
     *
     * @param \AppBundle\Entity\QuestionSubCategory $questionSubCategory
     *
     * @return QuestionCategory
     */
    public function addQuestionSubCategory(\AppBundle\Entity\QuestionSubCategory $questionSubCategory)
    {
        $this->questionSubCategories[] = $questionSubCategory;

        return $this;
    }

    /**
     * Remove questionSubCategory
     *
     * @param \AppBundle\Entity\QuestionSubCategory $questionSubCategory
     */
    public function removeQuestionSubCategory(\AppBundle\Entity\QuestionSubCategory $questionSubCategory)
    {
        $this->questionSubCategories->removeElement($questionSubCategory);
    }

    /**
     * Get questionSubCategories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestionSubCategories()
    {
        return $this->questionSubCategories;
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
