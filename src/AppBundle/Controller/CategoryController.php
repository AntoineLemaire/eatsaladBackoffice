<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\Category;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategoryController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/categories")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Category')->findBy(['active' => true], ['position' => 'ASC']);
        if ($restresult === null) {
            return new View("there are no category exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/category/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);
        if ($singleresult === null) {
            return new View("category not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/rest/category")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = new Category();
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

    /**
     * @Rest\Delete("/rest/category/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);
        if (empty($category)) {
            return new View("Category not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $em->remove($category);
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
        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);
        $category->setPosition($position);
        $em->persist($category);
        $em->flush();

        return new JsonResponse($category->getPosition(), 200);
    }
}
