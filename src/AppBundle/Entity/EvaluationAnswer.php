<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EvaluationAnswer
 *
 * @ORM\Table(name="evaluation_answer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EvaluationAnswerRepository")
 */
class EvaluationAnswer
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
     * @ORM\Column(name="comment", type="string", length=255)
     */
    private $comment;

    /**
     * One EvaluationAnswer have Many Photos.
     * @ORM\ManyToMany(targetEntity="Photo", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="evaluation_answer_photo",
     *      joinColumns={@ORM\JoinColumn(name="evaluation_answer_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="photo_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     *      )
     */
    private $photos;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set comment.
     *
     * @param string $comment
     *
     * @return EvaluationAnswer
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Add photo.
     *
     * @param \AppBundle\Entity\Photo $photo
     *
     * @return EvaluationAnswer
     */
    public function addPhoto(\AppBundle\Entity\Photo $photo)
    {
        $this->photos[] = $photo;

        return $this;
    }

    /**
     * Remove photo.
     *
     * @param \AppBundle\Entity\Photo $photo
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePhoto(\AppBundle\Entity\Photo $photo)
    {
        return $this->photos->removeElement($photo);
    }

    /**
     * Get photos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotos()
    {
        return $this->photos;
    }
}
