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
     * @ORM\ManyToOne(targetEntity="Question", cascade={"persist"})
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     *
     */
    private $question;

    /**
     * One EvaluationAnswer have Many Photos.
     * @ORM\ManyToOne(targetEntity="Answer", cascade={"persist"})
     * @ORM\JoinColumn(name="answer_id", referencedColumnName="id")
     *
     */
    private $answer;

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
     * Generates the magic method
     *
     */
    public function __toString(){
        // to show the name of the Category in the select
        return $this->question." : ".$this->answer;
        // to show the id of the Category in the select
        // return $this->id;
    }

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
     * Set question.
     *
     * @param \AppBundle\Entity\Question|null $question
     *
     * @return EvaluationAnswer
     */
    public function setQuestion(\AppBundle\Entity\Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question.
     *
     * @return \AppBundle\Entity\Question|null
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set answer.
     *
     * @param \AppBundle\Entity\Answer|null $answer
     *
     * @return EvaluationAnswer
     */
    public function setAnswer(\AppBundle\Entity\Answer $answer = null)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer.
     *
     * @return \AppBundle\Entity\Answer|null
     */
    public function getAnswer()
    {
        return $this->answer;
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
