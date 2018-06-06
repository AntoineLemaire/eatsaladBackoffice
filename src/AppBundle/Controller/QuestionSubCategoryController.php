<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\QuestionSubCategory;

class QuestionSubCategoryController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/question-sub-categories/")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:QuestionSubCategory')->findAll();
        if ($restresult === null) {
            return new View("there are no subcategories exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/question-sub-category/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:QuestionSubCategory')->find($id);
        if ($singleresult === null) {
            return new View("subcategory not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/rest/question-sub-category")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = new QuestionSubCategory();
        $name = $request->get('name');
        $id_category = $request->get('id_category');
        $category = $em->getRepository('AppBundle:QuestionCategory')->find($id_category);
        if(empty($name) || empty($category))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setName($name);
        $data->setQuestionCategory($category);
        $em->persist($data);
        $em->flush();
        return new View("SubCategory Added Successfully", Response::HTTP_OK);
    }
}
