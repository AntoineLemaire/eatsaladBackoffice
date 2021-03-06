<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\Question;
use Symfony\Component\HttpFoundation\JsonResponse;

class QuestionController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/question")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Question')->findBy(['active' => true], ['position' => 'ASC']);
        if ($restresult === null) {
            return new View("there are no questions exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/question/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Question')->find($id);
        if ($singleresult === null) {
            return new View("user not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/rest/question")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $question = new Question();
        $questionContent = $request->get('question');
        $id_subcategory = $request->get('id_subcategory');
        $subcategory = $em->getRepository('AppBundle:SubCategory')->find($id_subcategory);
        if(empty($questionContent) || empty($subcategory))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $question->setQuestion($questionContent);
        $subcategory->addQuestion($question);
        $em->persist($question);
        $em->persist($subcategory);
        $em->flush();
        return new View("Question Added Successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/rest/question/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $question = $this->getDoctrine()->getRepository('AppBundle:Question')->find($id);
        if (empty($question)) {
            return new View("Question not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $em->remove($question);
            $em->flush();
        }
        return new View("Deleted successfully", Response::HTTP_OK);
    }

    /**
     * Resorts an item using it's doctrine sortable property
     * @param integer $id
     * @param integer $position
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sortAction(Request $request)
    {
        $id = $request->request->get('id');
        $position = $request->request->get('position');
        $em = $this->getDoctrine()->getManager();
        $question = $this->getDoctrine()->getRepository('AppBundle:Question')->find($id);
        $question->setPosition($position);
        $em->persist($question);
        $em->flush();

        return new JsonResponse($question->getPosition(), 200);
    }
}
