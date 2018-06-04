<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\City;

class CityController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/city")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:City')->findAll();
        if ($restresult === null) {
            return new View("there are no city exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/city/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:City')->find($id);
        if ($singleresult === null) {
            return new View("city not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
    * @Rest\Post("/rest/city")
    */
    public function postAction(Request $request)
    {
        $data = new City;
        $name = $request->get('name');
        $postcode = $request->get('postcode');
        if(empty($name) || empty($postcode))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setName($name);
        $data->setPostcode($postcode);
        $em = $this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();
        return new View("City Added Successfully", Response::HTTP_OK);
    }
}
