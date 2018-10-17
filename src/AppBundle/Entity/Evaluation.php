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
     * @var boolean
     *
     * @ORM\Column(name="temp", type="boolean", nullable=false)
     */
    private $temp;

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
        $score = $this->getScore();
        if ($score >= 75)
            $color = "#8EC172";
        elseif ($score < 75 && $score >= 50)
            $color = "#9AD430";
        elseif ($score < 50 && $score >= 25)
            $color = "#FFC500";
        else
            $color = "#FF4200";
        return "Évaluation du ".$this->date->format('d/m/Y').", <span style='border-radius: 2px;padding: 5px 10px;color:#fff;font-weight:bold;background-color:".$color." '>score : ".$this->getScore()."%</span>";
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
    public function removeSubcategoryDone($id_subcategory)
    {
        $subcategoriesDone = $this->getSubcategoriesDone();
        if (($key = array_search($id_subcategory, $subcategoriesDone)) !== false) {
            unset($subcategoriesDone[$key]);
        }
        $this->setSubcategoriesDone($subcategoriesDone);

        return $this;
    }

    /**
     * Generate the total score
     *
     * @return int
     */
    public function getSubcategoryScore($id_subcategory)
    {
        $subcategoryScore = 0;
        $totalQuestions = 0;
        foreach ($this->getEvaluationAnswers() as $index => $evaluationAnswer) {
            if ($evaluationAnswer->getQuestion()->getSubcategory()->getId() == $id_subcategory){
                $subcategoryScore += $evaluationAnswer->getAnswer()->getScore();
                $totalQuestions++;
            }
        }
        if ($totalQuestions == 0)
            return 0;
        else
            return round(($subcategoryScore / ($totalQuestions * 3)) * 100);
    }

    /**
     * Generate the category score
     *
     * @return int
     */
    public function getCategoryScore($id_category)
    {
        $categoryScore = 0;
        $totalQuestions = 0;
        foreach ($this->getEvaluationAnswers() as $index => $evaluationAnswer) {
            if ($evaluationAnswer->getQuestion()->getSubcategory()->getCategory()->getId() == $id_category){
                $categoryScore += $evaluationAnswer->getAnswer()->getScore();
                $totalQuestions++;
            }
        }
        if ($totalQuestions == 0)
            return 0;
        else
            return round(($categoryScore / ($totalQuestions * 3)) * 100);
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->evaluationAnswers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
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
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Evaluation
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set subcategoriesDone
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
     * Get subcategoriesDone
     *
     * @return array
     */
    public function getSubcategoriesDone()
    {
        return $this->subcategoriesDone;
    }

    /**
     * Set controllerName
     *
     * @param string $controllerName
     *
     * @return Evaluation
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;

        return $this;
    }

    /**
     * Get controllerName
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * Set controllerSignature
     *
     * @param string $controllerSignature
     *
     * @return Evaluation
     */
    public function setControllerSignature($controllerSignature)
    {
        $this->controllerSignature = $controllerSignature;

        return $this;
    }

    /**
     * Get controllerSignature
     *
     * @return string
     */
    public function getControllerSignature()
    {
        return $this->controllerSignature;
    }

    /**
     * Set franchisedSignature
     *
     * @param string $franchisedSignature
     *
     * @return Evaluation
     */
    public function setFranchisedSignature($franchisedSignature)
    {
        $this->franchisedSignature = $franchisedSignature;

        return $this;
    }

    /**
     * Get franchisedSignature
     *
     * @return string
     */
    public function getFranchisedSignature()
    {
        return $this->franchisedSignature;
    }

    /**
     * Set accepted
     *
     * @param boolean $accepted
     *
     * @return Evaluation
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted
     *
     * @return boolean
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set temp
     *
     * @param boolean $temp
     *
     * @return Evaluation
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;

        return $this;
    }

    /**
     * Get temp
     *
     * @return boolean
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * Set restaurant
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
     * Get restaurant
     *
     * @return \AppBundle\Entity\Restaurant
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * Add evaluationAnswer
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
     * Remove evaluationAnswer
     *
     * @param \AppBundle\Entity\EvaluationAnswer $evaluationAnswer
     */
    public function removeEvaluationAnswer(\AppBundle\Entity\EvaluationAnswer $evaluationAnswer)
    {
        $this->evaluationAnswers->removeElement($evaluationAnswer);
    }

    /**
     * Get evaluationAnswers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvaluationAnswers()
    {
        return $this->evaluationAnswers;
    }
}
