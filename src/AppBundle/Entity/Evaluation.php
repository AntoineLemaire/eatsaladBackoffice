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
     * @var boolean
     *
     * @ORM\Column(name="accepted", type="boolean", nullable=true)
     */
    private $accepted;

    /**
     * One Evaluation have Many EvaluationAnswers.
     * @ORM\ManyToMany(targetEntity="EvaluationAnswer", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="evaluation_evaluation_answer",
     *      joinColumns={@ORM\JoinColumn(name="evaluation_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="evaluation_answer_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     *      )
     */
    private $evaluationAnswers;

    /**
     * Generates the magic method
     *
     */
    public function __toString(){
        // to show the name of the Category in the select
        return "Évaluation du ".$this->date->format('Y-m-d H:i:s');
        // to show the id of the Category in the select
        // return $this->id;
    }

    /**
     * Generate the total score
     *
     * @return int
     */
    public function getScore()
    {
        $totalScore = 0;
        $totalQuestions = 0;
        foreach ($this->getEvaluationAnswers() as $index => $evaluationAnswer) {
            $totalScore += $evaluationAnswer->getAnswer()->getScore();
            $totalQuestions++;
        }
        return round(($totalScore / ($totalQuestions * 3)) * 100);
    }

    /**
     * Generate the total score
     *
     * @return int
     */
    public function getSubcategoryScore($id_subcategory)
    {
        $totalScore = 0;
        $totalQuestions = 0;
        foreach ($this->getEvaluationAnswers() as $index => $evaluationAnswer) {
            $totalScore += $evaluationAnswer->getAnswer()->getScore();
            $totalQuestions++;
        }
        return round(($totalScore / ($totalQuestions * 3)) * 100);
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->evaluationAnswers = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @param array $subcategoriesDone
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
     * Set accepted.
     *
     * @param bool|null $accepted
     *
     * @return Evaluation
     */
    public function setAccepted($accepted = null)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted.
     *
     * @return bool|null
     */
    public function getAccepted()
    {
        return $this->accepted;
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
     * @return \AppBundle\Entity\Evaluation
     */
    public function getRestaurant()
    {
        return $this->restaurant;
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
