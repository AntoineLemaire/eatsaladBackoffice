<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\SubCategory;

class SubCategoryController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/sub-categories")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:SubCategory')->findAll();
        if ($restresult === null) {
            return new View("there are no subcategories exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/sub-category/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:SubCategory')->find($id);
        if ($singleresult === null) {
            return new View("subcategory not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/rest/sub-category")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $subCategory = new SubCategory();
        $name = $request->get('name');
        $id_category = $request->get('id_category');
        $category = $em->getRepository('AppBundle:Category')->find($id_category);
        if(empty($name) || empty($category))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $subCategory->setName($name);
        $category->addSubcategory($subCategory);
        $em->persist($subCategory);
        $em->persist($category);
        $em->flush();
        return new View("SubCategory Added Successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/rest/$sub-category/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $subCategory = $this->getDoctrine()->getRepository('AppBundle:SubCategory')->find($id);
        if (empty($subCategory)) {
            return new View("SubCategory not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $em->remove($subCategory);
            $em->flush();
        }
        return new View("Deleted successfully", Response::HTTP_OK);
    }
}
