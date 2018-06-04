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

class QuestionController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/question")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Question')->findAll();
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
        $data = new Question();
        $question = $request->get('question');
        $id_subcategory = $request->get('id_subcategory');
        $subcategory = $em->getRepository('AppBundle:QuestionSubCategory')->find($id_subcategory);
        if(empty($question) || empty($subcategory))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setQuestion($question);
        $data->setQuestionSubCategory($subcategory);
        $em->persist($data);
        $em->flush();
        return new View("Question Added Successfully", Response::HTTP_OK);
    }
}
