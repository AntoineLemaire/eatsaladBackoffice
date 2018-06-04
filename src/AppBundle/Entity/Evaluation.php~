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
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="controller_name", type="string", length=255)
     */
    private $controllerName;

    /**
     * @var string
     *
     * @ORM\Column(name="controller_signature", type="string", length=255)
     */
    private $controllerSignature;

    /**
     * @var string
     *
     * @ORM\Column(name="franchised_signature", type="text")
     */
    private $franchisedSignature;


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
}
