<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Restaurant
 *
 * @ORM\Table(name="restaurant")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RestaurantRepository")
 */
class Restaurant
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
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var array
     *
     * @ORM\Column(name="emails", type="array", nullable=false)
     */
    private $emails;

    /**
     * One Restaurant have Many Evaluations.
     * @ORM\OneToMany(targetEntity="Evaluation", cascade={"persist", "remove"}, mappedBy="restaurant")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $evaluations;

    /**
     * @var array
     *
     * @ORM\Column(name="totalscore", type="integer")
     */
    private $totalscore;

    /**
     * One Restaurant have Many Evaluations.
     * @ORM\ManyToOne(targetEntity="City", cascade={"persist", "remove"}, inversedBy="restaurants")
     */
    private $city;

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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->evaluations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get totalscore
     *
     * @return string
     */
    public function getTotalscore()
    {
        $calc = 0;
        $i = 0;
        foreach ($this->getEvaluations() as $evaluation) {
            $calc += $evaluation->getScore();
            $i++;
        }
        if ($i == 0){
            return "Pas encore d'évaluations disponibles";
        }
        $score = round(($calc / $i), 1);
        if ($score >= 75)
            $color = "#8EC172";
        elseif ($score < 75 && $score >= 50)
            $color = "#9AD430";
        elseif ($score < 50 && $score >= 25)
            $color = "#FFC500";
        else
            $color = "#FF4200";
        return "<span style='border-radius: 2px;padding: 5px 10px;color:#fff;font-weight:bold;background-color:".$color." '>".$score."%</span>";
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Restaurant
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
     * Set address
     *
     * @param string $address
     *
     * @return Restaurant
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set emails
     *
     * @param array $emails
     *
     * @return Restaurant
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * Get emails
     *
     * @return array
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Add evaluation
     *
     * @param \AppBundle\Entity\Evaluation $evaluation
     *
     * @return Restaurant
     */
    public function addEvaluation(\AppBundle\Entity\Evaluation $evaluation)
    {
        $this->evaluations[] = $evaluation;

        return $this;
    }

    /**
     * Remove evaluation
     *
     * @param \AppBundle\Entity\Evaluation $evaluation
     */
    public function removeEvaluation(\AppBundle\Entity\Evaluation $evaluation)
    {
        $this->evaluations->removeElement($evaluation);
    }

    /**
     * Get evaluations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvaluations()
    {
        return $this->evaluations;
    }

    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return Restaurant
     */
    public function setCity(\AppBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \AppBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }
}
