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
        $em = $this->getDoctrine()->getManager();
        $restresult = $em->createQueryBuilder();
        $dql = $restresult->select('c')
            ->from('AppBundle:City', 'c')
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        if ($restresult === null) {
            return new View("there are no city exist", Response::HTTP_NOT_FOUND);
        }
        return $dql;
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
        return new View($data->getId(), Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/rest/city/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $city = $this->getDoctrine()->getRepository('AppBundle:City')->find($id);
        if (empty($city)) {
            return new View("City not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $em->remove($city);
            $em->flush();
        }
        return new View("Deleted successfully", Response::HTTP_OK);
    }
}
