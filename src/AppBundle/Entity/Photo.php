<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Photos
 *
 * @ORM\Table(name="photo")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PhotosRepository")
 */
class Photo
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
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

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
     * Set path.
     *
     * @param string $path
     *
     * @return Photo
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Photo
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set evaluationAnswer.
     *
     * @param \AppBundle\Entity\EvaluationAnswer|null $evaluationAnswer
     *
     * @return Photo
     */
    public function setEvaluationAnswer(\AppBundle\Entity\EvaluationAnswer $evaluationAnswer = null)
    {
        $this->evaluationAnswer = $evaluationAnswer;

        return $this;
    }

    /**
     * Get evaluationAnswer.
     *
     * @return \AppBundle\Entity\EvaluationAnswer|null
     */
    public function getEvaluationAnswer()
    {
        return $this->evaluationAnswer;
    }
}
