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
     * @ORM\JoinColumn(name="answer_id", referencedColumnName="id", onDelete="SET NULL")
     *
     */
    private $answer;

    /**
     * One EvaluationAnswer have Many Photos.
     * @ORM\ManyToMany(targetEntity="Image", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="evaluation_answer_image",
     *      joinColumns={@ORM\JoinColumn(name="evaluation_answer_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     *      )
     */
    private $images;

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
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set comment
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
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set question
     *
     * @param \AppBundle\Entity\Question $question
     *
     * @return EvaluationAnswer
     */
    public function setQuestion(\AppBundle\Entity\Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \AppBundle\Entity\Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set answer
     *
     * @param \AppBundle\Entity\Answer $answer
     *
     * @return EvaluationAnswer
     */
    public function setAnswer(\AppBundle\Entity\Answer $answer = null)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return \AppBundle\Entity\Answer
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Add image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return EvaluationAnswer
     */
    public function addImage(\AppBundle\Entity\Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\Image $image
     */
    public function removeImage(\AppBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }
}
