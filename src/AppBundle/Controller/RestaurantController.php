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
     * @Rest\Get("/rest/restaurants")
     */
    public function getAction()
    {
        $em = $this->getDoctrine()->getManager();
        $restresult = $em->createQueryBuilder();
        $dql = $restresult->select('r')
            ->from('AppBundle:Restaurant', 'r')
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        if ($dql === null) {
            return new View("there are no restaurants exist", Response::HTTP_NOT_FOUND);
        }
        return $dql;
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
     * @Rest\Get("/rest/restaurants-by-city/{id_city}")
     */
    public function getByCityAction($id_city)
    {
        $em = $this->getDoctrine()->getManager();
        $city = $this->getDoctrine()->getRepository('AppBundle:City')->find($id_city);
        $restresult = $em->createQueryBuilder();
        $dql = $restresult->select('r')
            ->from('AppBundle:Restaurant', 'r')
            ->andWhere('r.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        if ($dql === null) {
            return new View("there are no restaurants exist", Response::HTTP_NOT_FOUND);
        }
        return $dql;
    }

    /**
     * @Rest\Post("/rest/restaurant")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $restaurant = new Restaurant();
        $name = $request->get('name');
        $emails = explode(';', $request->get('emails'));
        $address = $request->get('address');
        $id_city = $request->get('id_city');
        $city = $em->getRepository('AppBundle:City')->find($id_city);
        if(empty($name) || empty($emails) || empty($address) || empty($city))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $restaurant->setName($name);
        $restaurant->setAddress($address);
        $restaurant->setEmails($emails);
        $restaurant->setCity($city);
        $em->persist($restaurant);
        $em->flush();
        return new View("Restaurant Added Successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/rest/restaurant/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $restaurant = $this->getDoctrine()->getRepository('AppBundle:Restaurant')->find($id);
        if (empty($restaurant)) {
            return new View("Restaurant not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $em->remove($restaurant);
            $em->flush();
        }
        return new View("Deleted successfully", Response::HTTP_OK);
    }
}
