<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\Evaluation;
use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Service\HtmlToPdf;
// Import the BinaryFileResponse
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;


class EvaluationController extends FOSRestController
{

    /**
     * @Rest\Get("/rest/evaluation")
     */
    public function getAction()
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->findAll();
        if ($restresult === null) {
            return new View("there are no evaluations", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/rest/evaluation/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);
        if ($singleresult === null) {
            return new View("evaluation not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Get("/rest/evaluations-by-restaurant/{id_restaurant}")
     */
    public function getByRestaurantAction($id_restaurant)
    {
        $restaurant = $this->getDoctrine()->getRepository('AppBundle:Restaurant')->find($id_restaurant);
        if ($restaurant === null) {
            return new View("evaluation not found", Response::HTTP_NOT_FOUND);
        }
        return $restaurant->getEvaluations();
    }

    /**
     * @Rest\Post("/rest/evaluation")
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $evaluation = new Evaluation();
        $date = new \Datetime('NOW');
        $id_restaurant = $request->get('id_restaurant');
        $subcategoriesDone = $request->get('subcategories_done');
        $restaurant = $em->getRepository('AppBundle:Restaurant')->find($id_restaurant);
        if(empty($date) || empty($restaurant))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $evaluation->setDate($date);
        $evaluation->setSubcategoriesDone($subcategoriesDone);
        $evaluation->setRestaurant($restaurant);
        $evaluation->setTemp(true);
        $em->persist($restaurant);
        $em->persist($evaluation);
        $em->flush();
        return new Response($evaluation->getId(), Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/rest/evaluation/subcategory-done")
     */
    public function addSubcategoriesDone(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id_evaluation = $request->request->get('id_evaluation');
        $subcategory_done = $request->request->get('subcategory_done');
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id_evaluation);
        if(empty($subcategory_done))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $alreadyDone = $evaluation->getSubcategoriesDone();
        $alreadyDone[] = $subcategory_done;
        $evaluation->setSubcategoriesDone($alreadyDone);
        $em->persist($evaluation);
        $em->flush();
        return new Response($evaluation, Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/rest/evaluation/refusal")
     */
    public function setRefusal(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $refusal = $request->request->get('refusal');
        $id_evaluation = $request->request->get('id_evaluation');
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id_evaluation);
        if(empty($subcategory_done))
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $evaluation->setRefusal($refusal);
        $em->persist($evaluation);
        $em->flush();
        return new Response($evaluation, Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/rest/evaluation/{id}")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id);
        if (empty($evaluation)) {
            return new View("Evaluation not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $fileSystem = new Filesystem();
            $restPath = $this->container->getParameter('photos_directory');
            $evaluationAnswers = $evaluation->getEvaluationAnswers();
            foreach ($evaluationAnswers as $index => $evaluationAnswer) {
                $photos = $evaluationAnswer->getPhotos();
                foreach ($photos as $index => $photo) {
                    $fileSystem->remove($restPath.'/'.$evaluation->getId());
                }
            }
            $em->remove($evaluation);
            $em->flush();
        }
        return new View("Deleted successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/rest/evaluation/comment")
     */
    public function commentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id_evaluation = $request->request->get('id_evaluation');
        $comment = $request->request->get('comment');
        $evaluation = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->find($id_evaluation);
        $evaluation->setComment($comment);
        $em->persist($evaluation);
        $em->flush();

        return new View("Comment added successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/rest/evaluation-signature/upload")
     */
    public function uploadSignatureAction(Request $request)
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
     * @Rest\Post("/rest/evaluation/report")
     */
    public function createReport(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $id_evaluation = $request->request->get('id_evaluation');
        $controllerName = $request->request->get('controllerName');
        $controllerImage = $request->request->get('controllerSignature');
        $franchisedImage = $request->request->get('franchisedSignature');
        $restPath = $this->container->getParameter('photos_directory');

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($restPath.'/'.$id_evaluation.'/signatures')){
            try {
                $fileSystem->mkdir($restPath.'/'.$id_evaluation.'/signatures');
            } catch (IOExceptionInterface $exception) {
                return new View("An error occurred while creating your directory at ".$exception->getPath(), Response::HTTP_NOT_ACCEPTABLE);
            }
        }
        // Save Signatures to server
        $controllerBase64 = str_replace('data:image/png;base64,', '', $controllerImage);
        $controllerSignature = base64_decode(str_replace(' ', '+', $controllerBase64));
        $controllerPath = $id_evaluation.'/signatures/controller.png';
        $fullControllerPath = $restPath.'/'.$controllerPath;
        $successController = file_put_contents($fullControllerPath, $controllerSignature);
        if(!$successController || empty($controllerName))
            return new View("Error while uploading controller data", Response::HTTP_NOT_ACCEPTABLE);

        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id_evaluation);

        if($franchisedImage != null || $franchisedImage != ""){
            $franchisedBase64 = str_replace('data:image/png;base64,', '', $franchisedImage);
            $franchisedSignature = base64_decode(str_replace(' ', '+', $franchisedBase64));
            $franchisedPath = $id_evaluation.'/signatures/franchised.png';
            $fullFranchisedPath = $restPath.'/'.$franchisedPath;
            $successFranchised = file_put_contents($fullFranchisedPath, $franchisedSignature);
            $evaluation->setAccepted(true);
            if(!$successFranchised)
                return new View("Error while uploading franchised signature", Response::HTTP_NOT_ACCEPTABLE);
        }else{
            $franchisedPath = null;
            $evaluation->setAccepted(false);
        }

        $evaluation->setControllerName($controllerName);
        $evaluation->setControllerSignature($controllerPath);
        $evaluation->setFranchisedSignature($franchisedPath);
        $evaluation->setTemp(false);
        $em->persist($evaluation);
        $em->flush();

        $categories = $em->getRepository('AppBundle:Category')->findAll();

        foreach ($categories as $index => &$category) {
            $categoryScore = $evaluation->getCategoryScore($category->getId());
            $category->score = $categoryScore;
            foreach ($category->getSubcategories() as $index => &$subcategory) {
                $subCategoryScore = $evaluation->getSubcategoryScore($subcategory->getId());
                $subcategory->score = $subCategoryScore;
            }
        }
        // Create pdf
        $templateReport = $this->renderView('Pdf/pdf-report.html.twig', array('evaluation' => $evaluation));
        $templateStatistic = $this->renderView('Pdf/pdf-statistic.html.twig', array('evaluation' => $evaluation, 'categories' => $categories));

        if (!$fileSystem->exists($restPath.'/'.$id_evaluation.'/pdf')){
            try {
                $fileSystem->mkdir($restPath.'/'.$id_evaluation.'/pdf');
            } catch (IOExceptionInterface $exception) {
                return new View("An error occurred while creating pdf directory at ".$exception->getPath(), Response::HTTP_NOT_ACCEPTABLE);
            }
        }

        $report = $this->get('knp_snappy.pdf')->getOutputFromHtml($templateReport);
        file_put_contents($restPath.'/'.$id_evaluation.'/pdf/visite-de-conformité-'.$evaluation->getId().'.pdf', $report);

        $statistics = $this->get('knp_snappy.pdf')->getOutputFromHtml($templateStatistic);
        file_put_contents($restPath.'/'.$id_evaluation.'/pdf/statistiques-'.$evaluation->getId().'.pdf', $statistics);

        foreach ($evaluation->getRestaurant()->getEmails() as $index => $email) {
            $message = (new \Swift_Message('Viste de conformité'))
               ->setFrom('benmgne@gmail.com')
               ->setTo($email)
                ->attach(\Swift_Attachment::fromPath($restPath.'/'.$id_evaluation.'/pdf/statistiques-'.$evaluation->getId().'.pdf'))
                ->attach(\Swift_Attachment::fromPath($restPath.'/'.$id_evaluation.'/pdf/visite-de-conformité-'.$evaluation->getId().'.pdf'))
                ->setBody(
                   $this->renderView(
                       'Email/evaluation-conformite.html.twig',
                       array(
                           'evaluation' => $evaluation
                       )
                   ),
                   'text/html'
               );
            $this->get('mailer')->send($message);

        }
        return 'Rapport généré et Email envoyé';
    }

    /**
     * @Route(path = "/admin/evaluation/report/download", name = "report_download")
     */
    public function reportDownload(Request $request)
    {
        // change the properties of the given entity and save the changes
        $em = $this->getDoctrine()->getManager();
        $id_evaluation = $request->query->get('id');
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id_evaluation);

        $filePath = $this->container->getParameter('photos_directory') . '/' . $evaluation->getId() . '/pdf/visite-de-conformité-' . $evaluation->getId() . '.pdf';

        // This should return the file located in /mySymfonyProject/web/public-resources/TextFile.txt
        // to being viewed in the Browser
        return new BinaryFileResponse($filePath);
    }

    /**
     * @Route(path = "/admin/evaluation/statistics/download", name = "statistics_download")
     */
    public function statisticsDownload(Request $request)
    {
        // change the properties of the given entity and save the changes
        $em = $this->getDoctrine()->getManager();
        $id_evaluation = $request->query->get('id');
        $evaluation = $em->getRepository('AppBundle:Evaluation')->find($id_evaluation);

        $filePath = $this->container->getParameter('photos_directory') . '/' . $evaluation->getId() . '/pdf/statistiques-' . $evaluation->getId() . '.pdf';

        // This should return the file located in /mySymfonyProject/web/public-resources/TextFile.txt
        // to being viewed in the Browser
        return new BinaryFileResponse($filePath);
    }
}