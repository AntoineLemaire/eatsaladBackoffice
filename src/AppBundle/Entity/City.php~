<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 *
 * @ORM\Table(name="city")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CityRepository")
 */
class City
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
     * @ORM\Column(name="postcode", type="string", length=255)
     */
    private $postcode;

    /**
     * @ORM\OneToMany(targetEntity="Restaurant", mappedBy="city")
     */
    private $restaurants;

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
     * @return City
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
     * Set postcode
     *
     * @param string $postcode
     *
     * @return City
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->restaurants = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add restaurant
     *
     * @param \AppBundle\Entity\Restaurant $restaurant
     *
     * @return City
     */
    public function addRestaurant(\AppBundle\Entity\Restaurant $restaurant)
    {
        $this->restaurants[] = $restaurant;

        return $this;
    }

    /**
     * Remove restaurant
     *
     * @param \AppBundle\Entity\Restaurant $restaurant
     */
    public function removeRestaurant(\AppBundle\Entity\Restaurant $restaurant)
    {
        $this->restaurants->removeElement($restaurant);
    }

    /**
     * Get restaurants
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRestaurants()
    {
        return $this->restaurants;
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
