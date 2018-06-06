<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\QuestionCategory;

class QuestionCategoryController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/question-categories")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:QuestionCategory')->findAll();
        if ($restresult === null) {
            return new View("there are no category exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/question-category/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:QuestionCategory')->find($id);
        if ($singleresult === null) {
            return new View("category not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/rest/question-category")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = new QuestionCategory();
        $name = $request->get('name');
        if(empty($name))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setName($name);
        $em->persist($data);
        $em->flush();
        return new View("Category Added Successfully", Response::HTTP_OK);
    }
}
