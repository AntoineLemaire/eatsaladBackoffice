<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\Answer;

class AnswerController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/answer/")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Answer')->findAll();
        if ($restresult === null) {
            return new View("there are no answer exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/answer/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Answer')->find($id);
        if ($singleresult === null) {
            return new View("answer not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/rest/answer")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = new Answer();
        $answer = $request->get('answer');
        if(empty($answer))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setAnswer($answer);
        $em->persist($data);
        $em->flush();
        return new View("Answer Added Successfully", Response::HTTP_OK);
    }
}
