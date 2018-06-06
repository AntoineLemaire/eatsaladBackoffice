<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Evaluation
 *
 * @ORM\Table(name="evaluation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EvaluationRepository")
 */
class Evaluation
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var \boolean
     *
     * @ORM\Column(name="temp", type="boolean")
     */
    private $temp;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var array
     *
     * @ORM\Column(name="subcategories_done", type="array", nullable=false)
     */
    private $subcategoriesDone;

    /**
     * @var string
     *
     * @ORM\Column(name="controller_name", type="string", length=255, nullable=true)
     */
    private $controllerName;

    /**
     * @var string
     *
     * @ORM\Column(name="controller_signature", type="string", length=255, nullable=true)
     */
    private $controllerSignature;

    /**
     * @var string
     *
     * @ORM\Column(name="franchised_signature", type="text", nullable=true)
     */
    private $franchisedSignature;

    /**
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="evaluations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $restaurant;

    /**
     * @ORM\OneToMany(targetEntity="EvaluationAnswer", mappedBy="evaluation",cascade={"remove"})
     */
    private $evaluationAnswers;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Evaluation
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set temp.
     *
     * @param bool $temp
     *
     * @return Evaluation
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;

        return $this;
    }

    /**
     * Get temp.
     *
     * @return bool
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * Set comment.
     *
     * @param string|null $comment
     *
     * @return Evaluation
     */
    public function setComment($comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set subcategoriesDone.
     *
     * @param array\null $subcategoriesDone
     *
     * @return Evaluation
     */
    public function setSubcategoriesDone($subcategoriesDone)
    {
        $this->subcategoriesDone = $subcategoriesDone;

        return $this;
    }

    /**
     * Get subcategoriesDone.
     *
     * @return array
     */
    public function getSubcategoriesDone()
    {
        return $this->subcategoriesDone;
    }

    /**
     * Set controllerName.
     *
     * @param string|null $controllerName
     *
     * @return Evaluation
     */
    public function setControllerName($controllerName = null)
    {
        $this->controllerName = $controllerName;

        return $this;
    }

    /**
     * Get controllerName.
     *
     * @return string|null
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * Set controllerSignature.
     *
     * @param string|null $controllerSignature
     *
     * @return Evaluation
     */
    public function setControllerSignature($controllerSignature = null)
    {
        $this->controllerSignature = $controllerSignature;

        return $this;
    }

    /**
     * Get controllerSignature.
     *
     * @return string|null
     */
    public function getControllerSignature()
    {
        return $this->controllerSignature;
    }

    /**
     * Set franchisedSignature.
     *
     * @param string|null $franchisedSignature
     *
     * @return Evaluation
     */
    public function setFranchisedSignature($franchisedSignature = null)
    {
        $this->franchisedSignature = $franchisedSignature;

        return $this;
    }

    /**
     * Get franchisedSignature.
     *
     * @return string|null
     */
    public function getFranchisedSignature()
    {
        return $this->franchisedSignature;
    }

    /**
     * Set restaurant.
     *
     * @param \AppBundle\Entity\Restaurant $restaurant
     *
     * @return Evaluation
     */
    public function setRestaurant(\AppBundle\Entity\Restaurant $restaurant)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * Get restaurant.
     *
     * @return \AppBundle\Entity\Restaurant
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->evaluationAnswers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add evaluationAnswer.
     *
     * @param \AppBundle\Entity\EvaluationAnswer $evaluationAnswer
     *
     * @return Evaluation
     */
    public function addEvaluationAnswer(\AppBundle\Entity\EvaluationAnswer $evaluationAnswer)
    {
        $this->evaluationAnswers[] = $evaluationAnswer;

        return $this;
    }

    /**
     * Remove evaluationAnswer.
     *
     * @param \AppBundle\Entity\EvaluationAnswer $evaluationAnswer
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEvaluationAnswer(\AppBundle\Entity\EvaluationAnswer $evaluationAnswer)
    {
        return $this->evaluationAnswers->removeElement($evaluationAnswer);
    }

    /**
     * Get evaluationAnswers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvaluationAnswers()
    {
        return $this->evaluationAnswers;
    }
}
