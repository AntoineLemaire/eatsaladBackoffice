<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\Restaurant;

class RestaurantController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/restaurant/")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Restaurant')->findAll();
        if ($restresult === null) {
            return new View("there are no restaurants exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/restaurant/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Restaurant')->find($id);
        if ($singleresult === null) {
            return new View("restaurant not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Get("/rest/restaurant-by-city/{id_city}")
     */
    public function getByCityAction($id_city)
    {
        $city = $this->getDoctrine()->getRepository('AppBundle:City')->find($id_city);
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Restaurant')->findByCity($city);
        if ($restresult === null) {
            return new View("there are no restaurants exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Post("/rest/restaurant")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = new Restaurant();
        $name = $request->get('name');
        $address = $request->get('address');
        $emails = $request->get('emails');
        $address = $request->get('address');
        $id_city = $request->get('id_city');
        $city = $em->getRepository('AppBundle:City')->find($id_city);
        if(empty($name) || empty($emails) || empty($address) || empty($city))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setName($name);
        $data->setAddress($address);
        $data->setEmails($emails);
        $data->setCity($city);
        $em->persist($data);
        $em->flush();
        return new View("Restaurant Added Successfully", Response::HTTP_OK);
    }
}
