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
use AppBundle\Entity\EvaluationAnswer;
use AppBundle\Entity\Image;
use AppBundle\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class EvaluationAnswerController extends FOSRestController
{
    /**
     * @Rest\Get("/rest/evaluation-answer/{id_evaluation}")
     */
    public function getAction($id_evaluation)
    {
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id_evaluation);
        $restresult = $this->getDoctrine()->getRepository('AppBundle:EvaluationAnswer')->findByEvaluation($evaluation);
        if ($restresult === null) {
            return new View("there are no answer exist", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/evaluation-answer/{id_evaluation}/{id}")
     */
    public function idAction($id_evaluation, $id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Answer')->find($id);
        if ($singleresult === null) {
            return new View("answer not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/rest/evaluation-answer")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $postDatas = $request->request->all();
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($postDatas['id_evaluation']);
        if (empty($evaluation))
        {
            return new View("No evaluation", Response::HTTP_NOT_ACCEPTABLE);
        }
        foreach ($postDatas['answers'] as $index => $postData) {
            $evaluationAnswer = new EvaluationAnswer();
            $question = $em->getRepository('AppBundle:Question')->find($postData['data']['question']['id']);
            $answer = $em->getRepository('AppBundle:Answer')->find($postData['data']['answer']['id']);
            if(empty($answer) || empty($question))
            {
                return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
            }
            $evaluationAnswer->setComment($postData['data']['comment']);
            $evaluationAnswer->setQuestion($question);
            $evaluationAnswer->setAnswer($answer);

            $em->persist($evaluationAnswer);
            $em->flush();

            if(array_key_exists('photos', $postData['data']))
            {
                $photos = $postData['data']['photos'];
                foreach ($photos as $photo) {
                    $evaluationAnswerImages = new Image();
                    $evaluationAnswerImages->setEvaluationAnswer($evaluationAnswer);
                    $evaluationAnswerImages->setPath($postDatas['id_evaluation'].'/'.$evaluationAnswer->getId().'/');
                    $evaluationAnswerImages->setName($photo['name']);
                    $evaluationAnswer->addImage($evaluationAnswerImages);
                }
            }

            $em->persist($evaluationAnswer);
            $em->flush();

            $evaluation->addEvaluationAnswer($evaluationAnswer);
            $em->persist($evaluation);
            $em->flush();
        }

        // Return answers of the current evaluation
        return $evaluation->getEvaluationAnswers();
    }

    /**
     * @Rest\Post("/rest/evaluation-answer/upload")
     */
    public function uploadPhotosAction(Request $request)
    {
        $uploadedFile = $request->files->get('file');
        $folder_path = $request->request->get('folder_path');
        $restPath = $this->container->getParameter('photos_directory');
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($restPath.'/'.$folder_path)){
            try {
                $fileSystem->mkdir($restPath.'/'.$folder_path);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $finalDirectory = $restPath.'/'.$folder_path;
        $uploadedFile->move($finalDirectory,
            $uploadedFile->getClientOriginalName()
        );

        return "Upload ok";
    }

    /**
     * @Rest\Delete("/rest/evaluation-answer/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $evaluationAnswer = $this->getDoctrine()->getRepository('AppBundle:EvaluationAnswer')->find($id);
        if (empty($evaluationAnswer)) {
            return new View("EvaluationAnswer not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $em->remove($evaluationAnswer);
            $em->flush();
        }
        return new View("Deleted successfully", Response::HTTP_OK);
    }
}
