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
     * @Rest\Get("/rest/evaluation/{id}/subcategoriesdone")
     */
    public function getEvaluationSubcategoriesDoneAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Evaluation')->findSubcategoriesDone($id);
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
        $em = $this->getDoctrine()->getManager();
        $restresult = $em->createQueryBuilder();
        $dql = $restresult->select('e')
            ->from('AppBundle:Evaluation', 'e')
            ->andWhere('e.restaurant = :restaurant')
            ->andWhere('e.temp = :temp')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('temp', false)
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        if ($dql === null) {
            return new View("evaluation not found", Response::HTTP_NOT_FOUND);
        }
//        $result = [];
//        foreach ($restaurant->getEvaluations() as $index => $evaluation) {
//            if (!$evaluation->getTemp()){
//                $result[] = $evaluation;
//            }
//        }
        return $dql;
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
                $photos = $evaluationAnswer->getImages();
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

        $categories = $em->getRepository('AppBundle:Category')->findEnabledOnes();

        foreach ($categories as $index => &$category) {
            $categoryScore = $evaluation->getCategoryScore($category->getId());
            $category->setScore($categoryScore);
            foreach ($category->getSubcategories() as $index => &$subcategory) {
                $subCategoryScore = $evaluation->getSubcategoryScore($subcategory->getId());
                $subcategory->setScore($subCategoryScore);
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
               ->setFrom('corporate.barat@gmail.com')
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

    /**
     * @Rest\Get("/rest/evaluation/report/{id_evaluation}")
     */
    public function createReportError($id_evaluation)
    {
        $em = $this->getDoctrine()->getManager();
        $controllerName = 'Eveluation backup';
        $controllerImage = "data:image/png;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/7QCEUGhvdG9zaG9wIDMuMAA4QklNBAQAAAAAAGgcAgUACVNpZ25hdHVyZRwCGQAKc2lnbmF0dXJlOxwCUAAKdGFtcG9uYS5mchwCZAAGRnJhbmNlHAJpAAlTaWduYXR1cmUcAnMACnRhbXBvbmEuZnIcAngACVNpZ25hdHVyZRwCegAAAP/+ADtDUkVBVE9SOiBnZC1qcGVnIHYxLjAgKHVzaW5nIElKRyBKUEVHIHY5MCksIHF1YWxpdHkgPSA5MAr/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wgARCAMgAyADASIAAhEBAxEB/8QAHQABAAIDAQEBAQAAAAAAAAAAAAcIAQUGBAMCCf/EABQBAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhADEAAAAbUgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMYP0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcGVy6+PumLagAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGDLGQADFQ7efz/J3+np60lAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADVbWNzwytGslAAHH1bk70HFWbqZcsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQLPVWif+m+P2ABgpVaWld0istzaw2eAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFSra1FLdAAazZxgVZm3jtYWFkf5fUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMDIAMVDt1UYt4ABXSxdOSQo56/oSegAADBljIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMHEQ5vvkWCAMGjrvMHAlgwAKCX6/noSFbOo9zwAYM48dbSdKkRnaQ6iYvx+wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABxvZV+Oy4eX+DJkA+H34M4bacxJJ35gy5uPSUv5m/0EoGXDnvwe8Y82iNxGMXwUeSSJ5ms53owAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA8UK/eWiJpTg6xoBiIJegc5CxdWbgH7iztKxnr21maxno5bQzKTxjz1fJqpnr7iEFW22uTGQMROSy0G/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGM8IRlYeLu6IIlCtclliHg15q+U5yZSqVyKKyQeKyNUpcO1rr13BEHWvgP4HolacZJPF7gGDPPx7Ug6+KrTzceHuAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAxXGxdcix8HzjWw08iRFuj1b6YolOYtDVqcyq1lYF25DtxaXSMS9SbrLolDv6UUO/oSZAY4w6upkaTYRLdPf8A7MZAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABjPzP3nie2I2+Ud2MPlWbvuQI47z4bUn+Cvbrz56qcaamzneDo+PnJ8pWHNZs8/g/nt/Qz+e39CDONLTYmGuclW2OKkPIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAR3IkPkm7LPlK4WYrbJxFPK852pD/Wcr2hEkjyltiw/8wbYVbN3sLM8qWYzrq7Fm9XA8vlNbM0gmU4y1sj7cxkAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAELzBDhNcbSPUU8P4nThyQ692Br6czPXE84dxoIfk88cr2B1JR66ngrSfm2Pn2xHtSt9yZ2t3qhW+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAI698eaQxGtmIkLQ1tsFBh+dL4+pOQrVu5bNhb79ZAIm4HgLDHa0i81vDS1+u/U0/dsKu2iAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGEMEVzPCFwxV+yNfDsewh6xxQv2x9ZMq1eWu91j2AAqxx918HMdQCsFn6vnqstXWxQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAxnnDm44560hAlhq/WBOOj/AKblzm+AibqySLc1s6U4i1EcyMAAAAKt2kqydRP0DzwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADB56kbawJvtL0vFnFzREkpkT1P6nSFhuPlb5Ee7SxGQAAAABD8wDjOzAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADEJSTXMkmaMZHGdnz5G8Dx/b85Hl5chw9FtOJ7gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAYzDRD9tYfnAH5FOPbIxxFrKy2WK32UrZZQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAxTW0UEFnv0/Jmpf07oxO+RF0TWqwcn1oAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQX0X0rYXJqbmzRwk0sgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADGQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB//xAAtEAABBAIABAYDAAIDAQAAAAAGAwQFBwECABQVYBAREhMWICQlJiMwFzawMv/aAAgBAQABBQL/AMy6wjZ5LzNOkLt/3Ebz3x0brOJ9WKPQzl13DdUx7z9RlgZqGlmntD/cK+fmVhXI6w3Fq0Z8mF9syjvkI2sVVnAf9S+T6QNU1Gc0RXc79TqGachEds2K65QMrhL2Qr63RI8vA01H8sNmv7u0e2rld+yLjLblB363LIcwSiMf0kYCf6Gz+2rqV95dFP2kvrNZ+SWEXSHRxeko30te2rA25+y/rJOsMY6r2mZE2uiT9iDryM6WIdtOM9Rur62U95IMpFl6n1iqZI7ATT1ST7aDs8/bP1ut57UJUCOrASrVLYhOu1fP677ejWncc0S/W7HnuTT5z8dp+nYnkhvtQvJUxeJhXk/Gm/0nV+WhKPQ/xfWy3OX5vaTjZV9ER2kTF/afJo8aakdrysspV8nJyo/2Wn/YWIjt1K4foeu9Ug+lkPQN/VDbEyfhumTCx/q7eoMECm4U08JpSZXJiVSto7jXXGmvZRfN4Hh+vITMIM1n+znvFZXVBKylfZr+p0faC/pKOOTjGzrZsrT0LyA94unyLLUpLloZtOzrqddiNXvp7iEH2A+07MMP6gzJX3Sx6qGHJh/iYu/ajrpX9seAUfYDvCaImA83jbYgpF6fueVDYuPUlpFizTj2XDl0k0TUeOHGhJZjWMywi5YykxKsGMB2c8dpsWtYtVH+bdfcsKQbHpkN4v8AfqtjXg44gEOWg+Do4REmg0AvS1xagvGRo0ZyO6tU0xBauH/gUGMYMJFBxJFiojUzmS4jYtpENezrTkVOnRUcnExp7+4OPHOfLFcLdalrj35kmT09vSdmEYCKBINY2nuLekdn7iz5bXXenG/tiq7hNqiX27xCjsqZPxKu48Yx9ZqzoSFcQk21II/smE/rLH4Hc9ctMbLNCN/4GUj0oXqtjyQaa/n2rxbcqrJy8HEpQUUWljUUj4TRaPausrL7DRhHBoKRmEoXuRGo1XPDJihHNvpMzrIfZl1mvyHiOjXMs7DR34vA9kG858fG63hOii0vIaxUXGKrxdd0205ePePm8c3iZ5hOp3M/yiPwjLEbDzcwkws98aFsLoE7/KrKLbLbxCg7Xz2af2NOZJyIwex7h0NB8kVLigFHCyf1MrHZDWM5mT2ZZUo05WBGI0bR7JOtvkxnjHli3ZPLUanW+rKQq/TWPCGLdW058ZaIRlm2DnrB/wABjNIgsmw2ursNCmMvKOxUGjhVKxzvSBaaabqqSEevFuodpoyivo4cptETS2FHfAjXj8q3g4BiPM+yt98J61lrmbn+J7+mtGZe80sVO1mAjAwyUBEBP5xwO/v7aL5nEAO0m39csZzLoxTFZ58KO5m45N8hBjskWvxEBjxXQw/NPcY8seJKWMBZqRl0qbvQyp9G3GuuNdey7DlOkiVcRfShB040aNwFbbVoo02wDw+uCC2ZF7pGx4CpmFBaYj84jrslfSkIul9IIwYtRSt0SJ42ggyr3M5xHRjWJacI/sLC8PPgytVvFcRMHMHsoKA8eKIdlb7YT1EZJzMwvFsq7ST9FLVBK15fposUt/jFYTOmjEsplrso0sqS2fKWi5SgA4OiuijVnSPUDEFl2IkwIih+TvACtmjFt4bZ9OoRjmzviWmWcGzMLLekewdUyz/hkxQjm3ZZ+/2Yi0Yy1jY7hp+/uDghV0JbKsIxbzU1KPZeWctxCSjxR6q5dKjGXZsR8SbjLySFhB+VuouAbTBpjHljwlFPZjKrS901MbGYjGMaztiy4hXLEZx2aVZ6kW8OV9WrenkNnWSs3jxREZDpE7dBcazijJ4pvkZJ3jkk3t2HbsRqmoXDWHsOycR3GYZ30gnlEREBpmA9lpISLaKau7SkZl5FWJJRcoVreyMR0o6iFhCr3k7tFxLSGadnQW3VbE4sR/08PiDLI+KzIZmAGA9tqOgsY52YVsi1bTsxhqk6s+6nnr0IzTA3CAFdqEKlwNNEBJ2/eWFL53ZCUDp1O25+HhWcEysaCSmxmVPpGUH6iiW0kRdnuFtWyNUp5UguLVMUJfgHA2wq3tLbMrMWM6xGhUg19+JFG6Tq18HuR4wKiVUmnK9rjaZ20T1T0l4drOsB0QjBbU5nXBuSD0C3HIl6+QjWpmcuzJ4lH6w2aR1/Z9n2E/6eHhbLpwoZGTqbfOBJCGM+Iv8AoLbuVfbMSOoYlrPqtXHyHdxvs5rutsv+Ndca48LKI8j47Tg3hBlLzLSCYkZPJ2DKgoAgKo3A05Uso7T/ADdn22pleONS5d45CwxsJMI/9rc0g81j2FPM9sxlpSOqZdT7HKULpMuo7FUiDecceX0tx/vJFb8iigGFePpmyp0PCmYk04vBDyc0dp+P2fasipIlIIEpCjLir/zy61pLp4gHxnSBmzneXRqQGeU4TRJNNGr47p4d9LHr+SkZuHqWYk1x8bYjTLwvDH4tIa/q+zrCN/jzesIddsc8P1uWY0mj+rsT92X7b6paEbzEkQ13W3KcWKO5wds2ujJp/pvHP4tJ6+UD2abGCInGV2HrLL1r+aR8GK/LitPI+2IDamszZdh2JtO706NtX6u2caYDsZL7C/1Xjn/FS+PIX7Mn51sOxghBOTybcqcu2plPPQeLIV9oKCnWISsEZl506ZFMjsVSi+MMLTKMRMMBDnxod/1Xj/8AFNf9U7LcOE2iO2XVslLVqkybkqvsDtRp+gN4tff0hUgXOXo/X1d6QGkJ/cWZNhs6PkQoAPd5f/XYgUsXoCI7qLwfZZ6QOCuYGx5sNRPBp/1Kq8eQSuum1RsI/wBiZYHhdJMrsmf6CMVlAdDGO2bLM/j7Ctgz45HeBnj1CdYu02wEamzozfRFZN4kXpNFvzLjf/ks/wAY8sdsEU6gORNfQS5ZN+JDphWBSmZF/EgQEkLN7QluliSFTymGgkKthOM7ZJXy1kGMewRjGXhnb04sI7UInNNx6CUhwcf0Z321aBN0GBqsY6LB+Gc+XFiWBvLK13X2o8ltn4nbO++E9K20zPEvbUj/AH9mYxjGOPPixLB3kla9rzSAT4PhD5VGuXhzMx4sPpDML2yUSnRh+lIvyQ4zny4sCxN5FSva80H0+3ridZQE6zaYaBcnLs4ZqVH74ycAleIjOnb9hjipKOxpcVwrBhXZCVuh4VjhlD/zMv/EABQRAQAAAAAAAAAAAAAAAAAAALD/2gAIAQMBAT8BHE//xAAUEQEAAAAAAAAAAAAAAAAAAACw/9oACAECAQE/ARxP/8QAThAAAQIDAwUKCgcHAwMFAAAAAQIDAAQRBRIhEyIxQVEyYGFxgZGhscHRBhAUICMzQlJy4UNTYqKywvAVJIKS0uLxJTA0FnSwZHODw/L/2gAIAQEABj8C/wDGXKsmy3XEy7asmcicXV6+SJ2QmXlvpaAcbKzUp1EdW+KbmkmjxGTa+M/qvJFqW49iiSYXcJ9+7p5uuLVf1BCEdJ7t8UnZqTmspyq/iOjo64eQRddeYvL43COwxNv/AFsxTmA798RG6RMTd3/4x/aIZYGGWfSKcABPdFnjW4C4eUne1NzJ+haUvmESrr7q3nFqWbyzU7o+daM1WiktEJ+I4Drh6bIzZZrD4lYdVYsuUGpK3KceHYYkpbRkmUI5hvatNVaXm8nzkCLMG1BVzqPnSsoDjMPVPEn/ACIemiM6ZePMMO+JWTGcELZZ/Meve20zremEjkAJizGfdl0fh85mW9mWZHOrHuizpc4FLIKuM4nriZntKEKdf5NCesb27Gkx7RWrqAhCBoSKec8gZwfnAyOKt3qEWhMDNKGSlPGcB1xaM+Ru1BlJ4sT1je3Y8rqRkU866+dNTJwDTSl8wiWWvOyYW8ebvMSskDnTDt48SfmRFntkUW4jLK/ix6qb20DSG3B91vzrROtwBocpi0pum4bS2OU17IlbLbNUt3GOIk1V19EJQkUSkUA3t2i/puKfV03e3zpKWB9a/e5h84mp1zNSt1SyfspH+YnLVdFQ3fex95RoO3m3tlR0DGLVmj9V+JdezzpGWr6pi9/MflEs2M12bbCB/GanorC5tQz5tyv8IwHTXeqqZu5WYWcmw1764syXtSeW+q0GVOOy3sNbqgpyebaDui5LrV92LWf2ltHX504lONwpZHMO2LIsGXzvJmkpuj3zgOjriVk0blhsI8/LTzwR7rYxWviEFEif2dLarh9IeNXdCnbSKnKOUadXpWneYtZz7PsUUGxT36/DDpGKZKUp0f3+ba5BxDeTPLQdsTTvvzJ6Ejzg4s+jdnr6j9m9Xqh+0lirLS1TGPMgdXN5ynpl5DDSdK3DQQtixUZRejypwZo4h3xmh6fnXNen/AhEzaxTNzGkMD1aePbAAFANAG8ubna+kSm63wrOiGMr/wAmZ/eHSdNT8o8I7WOh1/JoPBUn+nzFuLNEIBUYXXdPrbrxk3olT9YtavvU7PNm3/qmlL5hCnE7spUmvGKQudWmjk4uo+AYDt8wF1dCrBKRipXENcX1qYs0KGZlvSvr4mxhzmC7MTLz49nLK0cgwEImJy9IyJxqR6RfEO2MhIsJZTrV7SuM7zbKsBOMtL/vU13c34otCZ0ZNhV3jphDCyKKmHFO9NOzzGpRHrZ99EqniJzvu1iTZHtzFeZJ74slO1m9z4+PLT8wlkHcp0qVxCEy956XKjRLjyKJPTFqLrpauc+HbEtJs+seWECGJVoUaZQEJHAPFlHlhtG0wVopIywFTMTG6pwJ1cvNDjNhjymbOau0Hs7m29UKySXZ2YUardWcE8Z1Ql+bpPTw9pQzEcQ3nPTDputNIK1HgEWl4QzI9NPukIrqQP1T+GMgndTTyW6cGnsiRlfqWUo6PMs6VGLdnS6plfxKwHZFksf+4vqiz2tFyXbT93xBKAHrQdHo2tn2jwR+2fCJ5woczksk0UsflTDL8nJtSrjbwTVtNKg10xZy3D6SYSwk8OFeyJq1HBUS4ybfxHSebr8dZtYcmNKJdGKz3RccVkpWubKtaOXaYRM2telJbSGfpFd0Jl5NhMuyn2Ubz5Wx5b/lWk6G6fZr30iWk2vVsNhAjwcskYpSrLLHL3JPmVOiPCG2Tjl3w038I+VIs6WH1I6VGEpGgCkTE8/uGk1p7x1CJjwgtYZRhC81B3Klah8I8Vm2FLZ77jgcUkbTgkdcSNgS6rzFnNpSsjWund1w45rdmFHoAhTry0tNIFVLWaAQuVsPiM4ofhHbCiyFvKUfSzLpzRxmEvEeVz/16xufhGrzsgXVzTo3Qlheu8sInJNd9lWGIoQdm8qetI50nZicizsvaK/iPN4rXnt03JIySePc/wBUWqw2zcRJO5MO3qhzTj0eO0pitFBkpTxnAdcSqjgp9Snemg6ok2NNHJdHUe3xSFgyxvGoUtI1rVgkfrbEtIs7llATXadZgvPUW+rBliuKz3RPeGts50yuvkbavaWcAeLsjyp6qi+pSr59o64kA+vKzbgU4mWb3Rqo6dkBLpIavejlWdHzMImraqy1pEqk55+LZCJeWaSwyjAIQKDzTMzzwZb1bVHYBrhbErWRkDhdSc9fGeyESsoyp95ehKYZkr193duqGtR3kzk0DR67k2viOjv5Ilr4o/Menc5dHRSJucXuWG1L6InZtKimdtebyKVDSRr/ADc8WqrV5TkwfhHzhT808hhlOlbiqCFrkJpEylBoq5qiWlE7qZf6E/OkSUr9UylHREzPupLiJeY3KdZSmgHOIl7Vn5FlFmvKp5PTOSOsGH7SWDcTfmEhWoblPWIMlZif2haRzaIxSg9p4I/bHhQsuunFMqr82wfZhmyZRaUyksq5eJoi97SjwDviUlrLJXJyTAYDhFMoqpJVy1i7Kt3WRguYc3Cf1sgKQnyic9qZcGPJs85UuxSbtH6oHNR8XdHtzkyr+Vsdghnyued8orVzJAXeIQUSEuGr26WcVK4zvKsjwfRiy2ctMfr4fxRQYCEyiPWzjoRQbBieyPB6yNLVlSapx74gmvWnphuYeUEJWpx9albK6eiHJuavosCTVdaZ0ZQ/rTFsykm2lmWEqk5NGgHM74sGzNKUXSocasehPimHXk5VtDrszQ6N1h0kRaiT7LeUHIaxMSdkuZDLIuvvaLqK7e6AWkZebpnTLgzuTZC5CTcvWk6mhKfoRt44CEJK1qwATiTCpaZbyT6aXkHSKisSjCEhCUNJFEjg81bzziWmkCqlqNAIXJ2KS0zoVNe0r4dkCYeKpaRrUvr0r+HvgS0iyGkaz7SjtJ3llSjRIFSYt233BXKLyTZOzT1XfFZ0hupez05RwcO6/pEeGlrVqAE2e0eNQB6ExYHg3Kf8udbbvgbNnKo9ES0izuWk0J946zHhVO6UpcDAPL/bFozm6blb108WYO2J2crRxKLrfxHARaUwfYZCa8Z+UWk1Z68nYtnIK3n/AK9Y0JHB/nZH7Sl2MowqrK7wNxWulduiC1Jy7dn10uA31ckKRLIU6omrr7hzU8JMBwDymepnTCxo+HZE+nTemsn2RTzMrNuekO4YTu1wlmiskVUak2cf8mG5y2gHXdKZT2U/Ft4oASLqRoA3mT7gNFuJyKf4sOqsSKSKLeGXV/F8qQ6+4aNtpK1HgEeEnhVMbpd+5Xgzj+UckWJKH19r2gXVbSNz3RNPUrL2agoRsF3N6yTEzNOGiGWys8kWjbL/AKx5TkxjrpgOmLQtFzFcw7cCttPmYs+zkndEvrHQO2HbMkDS0bXmMje9xpIzldJ6YmJKXzU3Ut11rUVCp64XZLK8lKuOFxymlejDiwhE3aN6UkdIT9I53CES0oylhlOhKfEnXlLS/wDs8xcpZV2bmxgXdLbfeYW4Ct5RPpZp7cp/WyPRJy02d3MrGceLZvLUpRokCpMJnpn6dxamxSlG72b4rEsRs50w7fUPuj80IbQKJSLoELYQfTTigyBwaT3csSlmJwfmChtQGtRzlR4OyX0VkSWXXxpST+URalouYrfeuXuLE/iiS8G5VYS/OrBeVXcI4f1qiUsiXzQ6UtgfYR86RIShFFpbvL+I4mJzGqWKMjk09NYmbYmvTTi/QysuNNPaPAPnBfnHKgbhpO4RxRL2lPgTM04kOIbUM1vvPjJ2RZ52zBX1nxKmZ15LDQ26TwDbCpSSCpSRVhdTu3OPuhE3bF6Xl9Ilxu18eyES8s0lhlGAQgUG8ybDXr5iks2OFeHfErKo3LLaWxyDxPubpqz0UHGBT8Sj4pSUcWBIWWnKvKUaJB0mv3RFlt2Z+/Jk3MpQJN1xdRhw6ItS05leRcRdYmAMzThcpyQm2hbPkTBRlQ0FqSeAYazHlNoKfcceReQ657eoadWEWNKzai8zIoxJ9xJrj0DxTT6tLrql48Ji5LJuMJPpJhe5R84as2SJck0u0LivaQndK5aRQaPHNue60o9ESZ9xK1fdMKYapOWh9Sk4I+I9ke1MubdDTI7IS+5Scn/rlDBHwjed4O2XpShxU64Ph3PTXxOvL3LaSs8kWxaznrJh67X7x6xFHlZabUMyXRuuXYImp9x/yWTeeJdc0lRrWgHLFuvsA+R2UypIUs1NdZryKhtS/XWjPLdJ4EinWtUWRZslVNkpWmUlj9coUCl8QiyckkJ8mXkEfDd/th+0lD0kyu4k/YT8+qHLMspys1uXX0/R8A4Y/ai0XZVTmSStXtqx0c0NNyiUsuPNJZaCNpGKuuJm1nU5z3omvhGk8/VC5mbeSwwjStUKlfBuzC/T6RxN48dNXLDUh4TyQk8ruJhIoBx8EWqvZKufhhT0o6WHVIKL6dIB2QmctO/KShzqH1rndCJaSYSwynUnXx7z7cnNKJNpEojrPSD4rRVWiloyQ/iwiRsmx0eU2vM1WopFcmVHDjVSkTlr2055Va8wQhCVKqEKOvhNKxKqcFLkuZhfLnRbc+v19pTOSB27fzR4N2O05lGENJS8Ue8SVr7uSJOUZbCJWyZOoQnQkn/9CLLs9Gctay7ToHWYl/B2yHavMt5OYmkaj7QTy64TP2gFN2cDUJ1vfLhiUQygNtNTCQEpFABdVFlWeykpQ00hlIOrDPWYF9WRk5Run64TBvFUrZMudGpA7VGEy0kyGWhs0q4SYmb10PyyS80s6qaRyxKWSVZNppFx1YOc7TRWHVzLQe8nZyiArQFVGO9Bx1e5QkqMTU+v1k7NLdJ/XL4kWNInLBt2866NBVouiA85R+0VjPd93gTFg2Ij6Z2+sctP6onwnNvoDKRxmnVHgbYCRjMfvDg+NWHReieLLaUMSmUCEpGCaZkW5NtyyZovv3M5VKISad0OT5BaGCWkV3CRohFpWmgpkNLbR0vf2wlCAEpTgANUOSc43lGF6oWJFkhxe6dcNVmGbEs03pZty4KaFr1q4hDMjLjNQM5WtatZhyZmXUssNiqlq1QJGRQ4iRvUQyndPHae6LbkrRYT5c3LjJ1xuKvJ7DFpq2MpHTvQtJdaKU3kh/EadsWWzShyAUeM49sf9PeD1XXnMx59s84B2bTFg2QhWWdzHJhzUTeqacFB4p2Z3TNnNlCeMZvWVRZ0kjdTExo4h/dEwsZ0vZTOQR/CLn9UeEM4rUkqP8xPZC3gaKUSa8cN2nardJXS1Lq+k4TwQABQDUPG5klXZqZ9E3wbTzQ7bDyfSPejZrqTrPKeqFzc46GmUc5OwcMNysu0vI3vQyqOtX6wgPvXX7SWM5zUjgT3xlRh5QwlXZ2Ra6vstj8W9CzLOTupubSP1ziEeDPg/VyYV6J11v2fsjtMXRR2dc9c/t4BwRNOaUyqT0JCesxMTS9wy2pw8gi0LTd9bNv6dtPmTFjpVuZRvygjlr+WJ203vWTbxN47B8yYtKXlnbjc2brik6Smpwh60JxOVZllAIaO5Urh81qRRiGEJQB9pWPdEtLOuBTjLQQiXb3asOiEtpTe9xoerZTtPfFEemnFj0swRieAbB4rJe2pcT1d8Wsr7TY/FvQs6Rs5RdmpcUAa0hxR68BF92jtovD0ruz7I8XhFP6akgH4lk9kPoBouZUlkdZ6BFnS1KKS0CrjOJ64nQPowlofyxK+D9lruyjLYQ+8n6ZWunBXniaS+FJmE0CEnChrjWJQkUU+S8eXR0AeabTsxvyjKgX0JUApJGFYylpLEk2cVFSr7hjyeRauA7pZxUs8J8dkn7bnUItNW15I6N54kpI5S1X8EhOOTG3j2RNCcxmJdgqXU1IUqnTifFMO/VtqVzCLSf1reCa8Q+ceD9ijFN7KuDgJ7kmCpRCEJFSToEWhMoN9LswspI1iuEN2pazdX90zLK9jhVw8EJZlyFKtApWEj2STTrxhiXbwQ0gITxD/AGrI+Nz8sTytsz+Ubzspg5OO4MtbTtPBB8IrYq7Ovm+0lzV9s9keFE/70xcHOr5eK1l6D5MsdEXvrH1q6h2RbdqLUPJ5FJQlZ0D2exULs+zlFFnjBbg0vf2xM2nMIyq5dYQyDoB2wVE0A1mJ+21Yy0rg1XmR0VP+3ZHG5+WJg7ZpX4U7zXZ2aOYjQnWs7BC/CG2BWUSr0LJ0KpoHwjph1z3EFUTswdLs0eod/itM7UJTzqEJnD7DTrvLeVSHrOZJuzTt927unTqESaJlN+155VUsD6JH9RMWnKnB1DwWU8lOyDZ7Cv32cFyidKUazy6IYYWKTLnpXviOrk/27I43fyw5/wByrqTvMceeWG2mxeUtWgCKC+1Ykoej+ow2wwgNstpupQnQBFpr92Wc/CYZPvurPTTs8U3wrbH3hEhYjALUqymiwNLqq15oTaFoJC7QIqlB0M/OH7Q3UjIer2YbnpqqHrV8Gs5MxW+1hm10ih0iP214RuZecrebarWh2ni2D/clPJn0NPS5ODmgg07oZkQvKLFVOLHtKO8xvwasg30X6PLToUruENSUuNGK161q1nxWv/2y+qJHjc/GYW66tLbSBVSlGgAjyOUqizW1V4XTt4ostoLyyE/vD2G5pjTq54fuKuzEz6Fvl0nmhpS00mJr0y/yjm3teRSi/wDUZkYU0tp28eyPKppH+ozIqqv0afd7/Ha4/wDTL6ol3nlpbaaLhUtRwAvGE2bZqXPI711Dad0+dp7otBU7dXaL0uqq/ZZwrh3xaj6ljyhCEpAOpOs9AhtpGfZEhpOpQGn+Y9EU0b2X56Y3KBmp1rVqEPeElqekQHKtA6FL7k+ZaKDoMs4MfhMStgy4UprKk5NvS4owJiYCXbTcGcvU39kd8TKQaOTVGE8unorEq9IT6ZdUzLgTKFqKaV0jDSI8mZOUdVnOvEYrO9pmyZJf7gwoi+NH2l9g+cMysui4y0m6lPjJOAEfseyby5W9dUpvS+rYODri1RMMUtGWIRVXsDEK6vFYthjOaZ9K8Ok9A6d7ZYZVSbnPRop7KfaP62wJx5H71Oi/j7KPZHb5irHshRUyTccdb0un3U8HXCZ6eSFWksYJ+pHfF5WbK2onTqqr+4dMFajRKcSdkW34QLGapWSar+tgTvbTLVvSMuq5wXEbrnPXAAwHjVY1jqK0KNx11vS4fdTwQmfn0hdpKGanUyO/xIyCg3PS5vsq0V2pj9jLs7IlQybs4RS8nj0c0MSLZvlOctfvK1ne1PzgNFNtG78WgdMWhaSxnLUGEnpPWPGqx7EUVhRuOPNaXPsphM9PpDloqGCdTI7974bH0z6UnpPZFn7XApw8pgzE7MIl2hrUdPFtgWTYjLqZdw3Td9Y73CEzU3dftJQ0+y1wDv3wLYlxWZaWHW0n2iNXTCLIYklBTWam9LEuJ4IE1bcwuXQdbxvOcidUXJJi6s7t5WK1cZ/8Zn//xAAqEAEAAQIFAwQDAQEBAQAAAAABEQAhMUFRYXGBkaFgscHwECDR4fEwsP/aAAgBAQABPyH/AOWVNT6jvUFkb8ZG3d0i1Oe+LcOXqJADurgj0vQQXdMaXophJirlPURZXi5w9gaQoO1UiHgDpTpl2LqfOXppan9nOmlGGZ3YpyVyvuEpYKOt2PEemiUQd3X4pTw9eFYtH6xZJ/8ATuKvPLk5WFLpMd5AUAAYN4T00N6CG/8Adp0iHvQ+f2tKRrUpfNNlBAduHmufiBtJ7ujD01BD9QDUKtZCd5hP7MQ2KNGV4qAY4/XvKpwMaP1fy9NqrugcvzKDDRPQ/VxpLgUbTQFObCfmxWN3s0Poaem2xtkOReI/Z0ZJeR+KxAk9xA9irVUN2vo2rMphxlQ8h09N5AH4v+5+1yI6WY+JqTrEW7L20mhlvUjoCkT7DZBh6aayIDO1j9llhZmo/wAmk1k70JTp8AoT2fSpN/2BgRlSFpVX3D9iBpCM0f4mhTxpwWausGvKn3PLR+81PoxDRDUmHQxf9qfCBbMIMAmncvR+l6cVcJqOJgjwN9z9sJ6Xcm8muscaEPsK5g5zOC71b/tNKEj94OuFKZnsm9k7OtS75PiIY6wzf0XNRww3ixx6I9utayK6KflfotSH0wZLgrsxOn9L+rQ0RLcAzeFPX2gB4X6k0BSpBLq1mppH5O7z2NRdFlV8buAdAqGJRiD7vjZo4xoBAHouFwgrsPO/A04BOqpB4h1mk3d9ADD9E7wToBLSzCXBuXtUukPl36hosEtyvxSRMQ0zJ7LWJww43fJPqfmYohDICRoF1xUoJEdwoOYzMoJWbAegDgCk8Oiyd7A3dJoFzTLrjL6NTcMhMLYLwqR1zYcHklXtC9yz4NGH5VYkWY4v2VwmRwNP4VW8iHvvy/C0UxPEo0G7VgbUlZXFHWhsa2eZUdU7NS3eheuPVGEH4RhlpcdjV2o/4ckainlVB9uLu/8AjQNXjDMg1ew27VnewuLexd3xUejZm5PkEtQCdeWwNpoeaYkMxL9nejBIkjcE+Z/SfyPMvegV1qwTqeJ81YfAHA/CFITNj9UZ96F8L0JPgDxQdPZkaQs8BvpTAVBcVcqSAA3R8FlCponlHiNdm7UvjXR40r03AbDvp88VhnIKJdVzd2o9HSvksNInuhxNE/CNrBd6405BbnozZ+qf0JkgXWrrwgcjYO7tqbUpa3N8UGCkOlPfhhbrYd1grDATbPANGLcb/i6nwP8ATSu1DTwdEc8eSo3DucPhNW9oIDVaiiXv76vd2qSSlaXV8XbGgpQXsl9E41H6Temy2gUckh2aXMlwQYo19FPPFWa6D8C50o+gj41hmkIkkcLvXE/MByqfeuKjBI/X2AoRtjJ1Sk0yYM8FI4GahYDAdd3WWnaDQQ+CM2ssLnGVOQLGxcilnLUS7u1J8iGQ3Rwu+ad7YCb8rY7z0ijNBjcr2F+KCHVlFR+h3ZYN++SlMdC0drLZ1mn8HAvLobtCkJHCsVGxY6eibmd4Gul6EHm2LzhPHvVkcFc4kHVtQObLxNbHe2ka3e8FHCQaBd6uRMC7ykblYDAk1KfdUzETXIJ81Ou9ncQLFBirgUXBZkE49qJtPkAgXcOxQVWQm01jtO9SEeOdoFg0ddKst5RkUYAkTtqrLOhNA90qmJXcDYnN2VJnyAOQcvneo/RayHNinVf9cVwzjCb2P7LVwkWMRuFo18Ur0xZ5xfphWHolGyPFhhKPRQzCCwGVMuovGfmAdatDA5hKm0bVZ8gCJdKufEEvL3cVkIFYZl1RMnV3aIO/0a9434ArilJMXhHSiBm+aJfCi+I5eraiXK5qZIwvDUGXY6zRdpIJlzbsjrpLotglLlBjT/grIIB3hKsK9MME/ra9EkdVrOJ3I4mTfHimaOATrwceVuaxCKx+6TUeiguSQyKuEDkiZHQGpq1bXlIQquqxKID3OtYJrWzKPoZqLMjwb4q8stWj2CkfGnkfOTYfMoySHc+ZZ6ViKuPnq2E+sOqEqAbCAJgrQF1QAjCPVIB2qbA0lzHV+WhONDqAGTzS0E9ifGiIWCx+Zp/LNb1NjI3aMxxlQuU573aKww4dnk5tluaP4CBQBpHoman8W0mjxm57nSr4T8tx9MqlZWGQS+1CPDpaJAdWh0aikw91UCxBzBGiNItnQLQyRL9B5A96LCBRiDK/XCmzFA0/v21B+AD7IXBQRxAqCQPVbv8AlDJgjvIJPBYpabofYdjyPQzrCb+/Lq7/AIDun0/NhxTbJrFj7be1DBXKeOfYUQY7HQjQ2OtR6KOo0jIMacQIvxoF6GNFIOgg3RfPZUBB9EBBVkwJY/5ShAcZi6ffEpGlyeQh7veowoaTOHveFP8A7RAFnuReN9bw7PAL9c6s8aP1rrUXOhePyUse9fvF13IJ2hNTMU4YdB841Yfls0kt7uHmon8T3gJrMXxJ8P4zl543QxWxTKqvO/MDZ5qDq79Be3tjxQU4so9GSaQAYrYej2VHVbjYKwq7CT0D6o0/FmJkQRObRPmq+PiYsQRcbNajebJa2OrJs0HifdFwOB3o/M+ioL04rhbSrwQkn5lPHFTA6UozMncNKbWiPFvsKxAnMcYdSOSjMIBAFYfjJDx7r6sRfNZFgxF4vLipXZ5v5I8u7WdqcUeLnHjCo9Gx0cYRxdj8OVDTsJfagVbMuvl7VY6ALu0fcPSaN5Kcpy5iMzYq7W1ANk1OXSvElGJEJ+yKNZQNo4pnMB1zmIxw7V5P+Ku0sowSPMuyiQLq0mv5+XOBEo3EWE7glLQcHmB8uiU6pWOoTup5gHXQmMlsHG7sVrUdkNlA7qrNf4kmzcjK0jbOoAdhyrnN0aJZO5SwJ6NxtOB1e1BUyx4tViu76PzmRaL9RrRUfwLfkLwtL0dFtCAaCzAz0pQMlZW6csbDfGpEmT6hfYY6Uq0ZKQf9V22B5ZHUG6kG6TwVB9MqvuUjwd+gSpbVs8ayZyZXwn0gVn2015stS0RvMQAVeZxARHhdgqCQ+bihbqndaCkpIXyRoLPLij2zfMdw0rhQsJCZNAR20qGAmIlatAi2aS0TmxkgxDOJaj0epUOewS0Od/mZMe9JinogEbQRqRN2hQi6lp9o3xfFP1LBnAQXtTBpGWwoeVPDzGyuHqodP4WIhg61dikvMIEZsdlWJ6CTgE64vLRSE4VuOnuokLQ8AMAKtHLIYRGRHJKtkAjLpORsVj7oW69mY2lzrGxBy4xef4VMARsAp9wJrpRPFCYpqrhWMpmvzU/+0P8APpCD+4kPaqnGAP1fml9xNmHZB8NOZdz5oBkPw31CCyCs7jpSpY/dY9xUe4uiIg7qpCN3josoo1/GOKfekWMLr6R9ufGIFiQBYKCPwSTOcblPH5JV5TMTBfiP9UToMeiDNaUPI4OnrZu+HlQ14IZH7TmphOC6ZpL8GpPtT6QJPh0zC3vUFN7TAELZQcHerTZGDF4j5xavDA+3zpUWt0NEfihwld+S9fqtSVSzcTh1j3pAboz57/TatMhCKwdGb61HCVUw5nUFrb0ACLcfo1/dz3hPSmLksKmKMi5tMrcs+MwnviexURz6CIad6irCY70XTkfjej03qWz05wwbBNqgFrC5umur0/DyxwyHivZfML7h3qbYa/vXVOfa5tBJ3WmdWEbinlP+Megp1Q3B5rLYb6o/UIswZixBKSIGF5o6hoPwQY6rQYtxz0zPasPwesAph/wB/v0ctHExhpNaZqydeQg5bkr1W81FT+xdeRpxR1cu1c7s+qE8d5UwkAwBWOqRlzZ0ihyWA20F4ZOcFKjlLLZ6HqomobbEHt+sVH6WD6xTjY7f39GrTawF/wDwn+VODjHJ5GS5MjpE0LkztN7H4ISwCd1nzQ2sM5rFAexuYhxTkQ3WnCngCn1bOgBXAcOWOaWjShpGlSAoN5zkrj4nJ/52n7x6OIAiehBux3n/AGrNPgXlZP8Aq60OAnYCaVnuvHzr8Xuh7A/ml0iL3sHgoEDiHOAfaZYzWn9YsMWTqgNoSru7RjD8jqRHrgbe0culRR88PgIOj/6UiN/0Zy0MqQLFa7Y0Rr6PQ4uf3RkCwKuHEqaIF/DiATHvF8UNDObvdsWxrfSMe9ZB+8cqajN+8007+wpRycFmlKWZLmlR6mUmPgpbZYP/AEXIO5cdXBuQofjxUGITbA6ei5rF/DrDEXru5tUiwMsusX7hH4cfXvrkTUuOTFixVptYIUgOD00HV2OUAAsu+yaGDIW3IeKXVKSSvfLgngu5WoqP/SPRc04YZrG7PNh1OVZBLmsQ5Z7rZUYfiEPu6ghsqE0rU/lIbe2DTTqdkJmNvgh1BNCFljKVWbmhpDgHGruwcJoABAsBl6ZQCYQtxg8vy0RssHYYQcUb8NR+UFCUYKIGxMy7YdjTrWvwCA/VzcVdzF1eLvH3Uz9G1DNvoWdKMSLZHwQyPTKxTyM8MDH1/hQJx5tDXf8AJIAEq5FS8SbxKtyaBkELiiA3mC1FNmKgWF8auAB6aS/ORXg9m3+KOBCKF/lMXJp+QlLAXrFTfXBWvptjcD4riZG/NywN8cI7gqp6iKMAYtJ6VrkG8dCdfTU0kxTLql3zoo2ACAMvwwrEDQuVFroUxwwxnVoW6sjXW5YG8UiUMthqHKYL5IV0R+hGZ4jHE5VdTmHE/wDwNg9NXvGvd8oq+ok8YPfez+BKrAXWsYJ7kbW8tUxytjM1s10ZGut6G/4io9Nq1HTwe6KFAunuseIr+CysGK2KiACB3Y++OlZeTNwfLr2bh6fJU4yICMtxdYqdNLlkrDJx0aVPl7I0wuuOKhuDCfvMiCo9QRUVH/y0P//aAAwDAQACAAMAAAAQ8888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888880088888888888888888888888888888888888888888888888osU88888888888888888888888888888888888888888888w880Ac88888888888888888888888888888888888888888888g88II88888888888888888888888888888888888888888888o88ocg88888888888888888888888888888888888888888888Qc88Ec88888888888888888888888888888888888888888w08wc8sIU888w8888888888888888888888888888888888888gU8AU8MI8808I888888888888888888888888888888888888k0UooY0YswA0k8888888888888888888888888888888888888gI88UsIIUQ4s84888888888888888888888888888888888884sgQEIc4gkM4YA088888888888888888888888888888888888gssMMIwg4c8Q4s88888888888888888888888888888888884Is08YQ0Y0MsEUc88888888888888888888888888888888888sU80YgQkwcIQ88888888888888888888888888888888888888AcEI8E0MU0Yc88888888888888888888888888888888888888UEEMcQYQIYY888888888888888888888888888888888888880EMcMIcs0E8w888888888888888888888888888888888888840AUcIg8888oA8888888888888888888888888888888888888owYwgYU8888ok888888888888888888888888888888888888gI4QE4c8888888888888888888888888888888888888888888I8cIIc88888888888888888888888888888888888888888880kUQUU88888888888888888888888888888888888888888888ko4c8888888888888888888888888888888888888888888888cc88888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888/8QAFBEBAAAAAAAAAAAAAAAAAAAAsP/aAAgBAwEBPxAcT//EABQRAQAAAAAAAAAAAAAAAAAAALD/2gAIAQIBAT8QHE//xAAqEAEAAgIBBAEFAAIDAQEAAAABESEAMUFRYXGBYBAgkaGxwfAw0eHxsP/aAAgBAQABPxD/APLKYqZOUoTrhr5DN6yLUrFjElFMkGGEnCaJqSjJlZyBYLiJwIA+QkCMmbSC6x9CxVYK+h0vM55Disidhy2fpho+QKj2yQeOqkTe6HjMJIBryVMHibdCCDCa+MweMGusPtYEmCLXBiHKsAo9COQr8ACSV0P0MBeV6LZV+f4/GpquLkoNJZREJFitd80+0CVgmEGj3PWaCXqZH8GJGybbV7/tvBEyAGlL9j8aVeyO0wHp4v6JU4H/AEMKD7QEhRtr0eZ6wypGi013b92OZ8vwfqBL4zR8aOPEoco/9PGHVL/m/sv2rs5yXOyD0Rdz+GGdFWgtOSQVv+BL+IgwfGqeFTq2jISAOHQA/n3C7syUyffFmXaVWItHc/Fx2IThyo/LmNfG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";
        $franchisedImage = "data:image/png;base64,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iEPu6ghsqE0rU/lIbe2DTTqdkJmNvgh1BNCFljKVWbmhpDgHGruwcJoABAsBl6ZQCYQtxg8vy0RssHYYQcUb8NR+UFCUYKIGxMy7YdjTrWvwCA/VzcVdzF1eLvH3Uz9G1DNvoWdKMSLZHwQyPTKxTyM8MDH1/hQJx5tDXf8AJIAEq5FS8SbxKtyaBkELiiA3mC1FNmKgWF8auAB6aS/ORXg9m3+KOBCKF/lMXJp+QlLAXrFTfXBWvptjcD4riZG/NywN8cI7gqp6iKMAYtJ6VrkG8dCdfTU0kxTLql3zoo2ACAMvwwrEDQuVFroUxwwxnVoW6sjXW5YG8UiUMthqHKYL5IV0R+hGZ4jHE5VdTmHE/wDwNg9NXvGvd8oq+ok8YPfez+BKrAXWsYJ7kbW8tUxytjM1s10ZGut6G/4io9Nq1HTwe6KFAunuseIr+CysGK2KiACB3Y++OlZeTNwfLr2bh6fJU4yICMtxdYqdNLlkrDJx0aVPl7I0wuuOKhuDCfvMiCo9QRUVH/y0P//aAAwDAQACAAMAAAAQ8888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888880088888888888888888888888888888888888888888888888osU88888888888888888888888888888888888888888888w880Ac88888888888888888888888888888888888888888888g88II88888888888888888888888888888888888888888888o88ocg88888888888888888888888888888888888888888888Qc88Ec88888888888888888888888888888888888888888w08wc8sIU888w8888888888888888888888888888888888888gU8AU8MI8808I888888888888888888888888888888888888k0UooY0YswA0k8888888888888888888888888888888888888gI88UsIIUQ4s84888888888888888888888888888888888884sgQEIc4gkM4YA088888888888888888888888888888888888gssMMIwg4c8Q4s88888888888888888888888888888888884Is08YQ0Y0MsEUc88888888888888888888888888888888888sU80YgQkwcIQ88888888888888888888888888888888888888AcEI8E0MU0Yc88888888888888888888888888888888888888UEEMcQYQIYY888888888888888888888888888888888888880EMcMIcs0E8w888888888888888888888888888888888888840AUcIg8888oA8888888888888888888888888888888888888owYwgYU8888ok888888888888888888888888888888888888gI4QE4c8888888888888888888888888888888888888888888I8cIIc88888888888888888888888888888888888888888880kUQUU88888888888888888888888888888888888888888888ko4c8888888888888888888888888888888888888888888888cc88888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888/8QAFBEBAAAAAAAAAAAAAAAAAAAAsP/aAAgBAwEBPxAcT//EABQRAQAAAAAAAAAAAAAAAAAAALD/2gAIAQIBAT8QHE//xAAqEAEAAgIBBAEFAAIDAQEAAAABESEAMUFRYXGBYBAgkaGxwfAw0eHxsP/aAAgBAQABPxD/APLKYqZOUoTrhr5DN6yLUrFjElFMkGGEnCaJqSjJlZyBYLiJwIA+QkCMmbSC6x9CxVYK+h0vM55Disidhy2fpho+QKj2yQeOqkTe6HjMJIBryVMHibdCCDCa+MweMGusPtYEmCLXBiHKsAo9COQr8ACSV0P0MBeV6LZV+f4/GpquLkoNJZREJFitd80+0CVgmEGj3PWaCXqZH8GJGybbV7/tvBEyAGlL9j8aVeyO0wHp4v6JU4H/AEMKD7QEhRtr0eZ6wypGi013b92OZ8vwfqBL4zR8aOPEoco/9PGHVL/m/sv2rs5yXOyD0Rdz+GGdFWgtOSQVv+BL+IgwfGqeFTq2jISAOHQA/n3C7syUyffFmXaVWItHc/Fx2IThyo/LmNfGVjAV4jX95YNfaetbODNAMyiuWB7eQawUbZyJ0l4nCWgQnTdz40BrFDI+hmg/w/3GKoNsT/bfxxZsfvMAevzZB7J6jfmt08cIFpwGAHgD40JciDw6Bp+Afc0Ckbyz1hHBUiB0z0E/fFDy9EjHoxsGj4oxhmgkM8YMn2LxCU7Ar/M3lI9n7BP7hicvdof1e8FZJZITfmPZmpqbFlPxbjQ+9gxnPlwmL+Fk3JGZCwQtIYLggsZK46wQClbMpIlGy6fYCNRdCF/Mlq2AGj7FTNHipmkGFTFJWTUxyQ8EaBfkmeWRd37mGGlVbI6Ty3SoHKYK/ICB19Fijl2xwLYUsq4qB7slicNX8KYPbEIWj0A9C236cJuZSyHE/C9Ya+sE4IuTaNoOsD+YzRZ71B/uB9lO2PqrC0q9h/RgRoYZGDJ5kzz6Ya+xgxXaec5BjGm2gcMG3jCjqrLYljL0gDBaRC6I5TgC0FVrGN0y40hKyeEJ9AcE/YgFQAaA4wr4Usmztxo5hD/ixgZN+jFXkC+TnO4Jy3APY/phr6lcbXSyegcOEOO6M/yvxjzKf3oP4GGj7JVkfRggnggwryFD95aIpUSQ+F9wwNfR5Nd8khAZ0vntA+jEJ9AScP8AsmAYrA84jyh6LpLvBhgIMkk5Udm5GGXgEgNIm/lUcAZHwtYxREhOYAenaejGrD1QGjOlXFA1K7sK90HnLD6ltFnQz4Cx1TJyWbxNHnDoqFH0WowR7yTiYQNwi3VCCbTIUxucsBdm1JA5ecMcqgdp35xPx+hJEF7SLsOADLbgPug+iWYKaToNpFAV4Mc3Q9EFRAYJnInfGLIyNFJuBFUKEKHF0xPjHoA4lxAYjHq+dtvjHn5DBylwII+GBv5yGt+BxpG/hTseieAyN0wNp9OT9MmKxrpz9ye/qqOOYM/WM3sD2WRdIf8AO/eAOuzoQv7HFjmsattPa0JYHVFiCAiHdKzZ1RspimoooGowuQl3EpbXbjOXDpznrLLOLsBjEOjD7emaHXFDUZF9GlIhF6yVbYnWPx5GiZvC7NtToMlNWP6NPcdRY62wX91I5Ly3KK8uRwI+GtTtLssUcIsepAAIwvdSu64sCCZNSBmGvqMgihgAxxllnMCf0cPDz48ftgcD2DPoCD+ZeKIw3i8sOkzocPg0rMMqSKrJlizvGuvfCGvHkkZ0ZadEtOUeAFCU9QeHxkSxY9DP3gh2g0/aIAwUIyovoxr8PhU5PnDuq2QYIgSqgjCVoJ9KyZD7d4MAMCNfV0Vkz5e3mx3TkRNIZf8A+q8hvQrqQiKM4WfCFZiM8hMS+0Mt27XBhUdMqQL5JGIe83t1y9Z1IUIAbABCWYMn0iXXNhClO4zOBEnCio+X5rgaXSV++FCLO2M4BmopDqvR0wTpwQ7O8aeawP6hIbb3Q8JRaCVCQTQSB9ETQVKtesTu/Pz2eZ6YEsOFvF1Cdho0wYplgp3E6Oy9BQuqg7YMOr/KrMgo5aXrBtdqyrargAg19ZvJgNLQBIS16GtsEuNnKQPm7wuaYpYvVVXBy2i2wAbyO+0Yi/WAvIXmM18IEbmuYWcnecHRYhUE+qDMuQMcPdjdAlTdg94B5xU4vDAQXYkMBLvywmecCZ68PglbeAt4nD5ynZhMMAgwpDDDWKPLJtssj/UYA8ngiTl7C+8df2cBYi8gLDG4rCLJQCLKhmDMksSGGR9OXmFBRTTXLUpoMxPeU89IaYDVw6LMDgKfySVPz63DCZJQMMYp69jhpXCpIKrBEZCWEIejRJGpHUwM4R6TEK9TOwW5WEOX7IHtjvlNnC09JMwtzCHARKsA+/cRbt6lmFYOwgieaG8zOlYQSwhzZJSiZYQJoMCAdPhE3GBAQ9QrvAsTyMDSEAgBoM2b4i4iwolICifkQ82FeJFJCnS/EWVMPgOiCWjEqO9bHaknBlfPWxm3Y8A5jyPDl84AOJaNMckA0o4kRLoEbl+eGGe3FJrAuM0VQD0DUuGajmMnUj2ibYVlConCxonrEygMC0dalUJFLwbxPV5jAAFCSTiYbMGqXIaTIDayvVVbwII+xumxwu0UGKvqUheRr33vxyWoOUy2RuW+A8qsod3ToiRfmo0AViHAg+EsIUhABKvgJx8SMjMeH/pnnKf+4iNSgSSl+Wb2YKnNoCEvL/65IAvDDPklgwXoLThSwDgv2j9iY0GaICmxmw9foyF4XyyxXzIe8E6A1vTOsAuyxgEX9Ay2fDcXd6yGygdAg1SpkYIV1YtTIqO06Ioy+CKMS+QO5JUJk7p0iJUplWwTZA5GZKeUljJxTKuWKEIhE8z/AOOwDYYHQNfVg4BRMgF39m4g7tIjSckyAeS1LALkcxAB2xCg8Hl0CSziBEAGgII4zXwhgxH7wnOul/RnF8qJch3Fg3e4CFS/uR4CzXcEW9CcVjxVQ6qEj0xIFQUWw3UX2cCgYDIIz1Xc7Y1ZHehe6wB1XEXF4QzBvop1j1xrGyCTxlPfbkU4DuE5Tz+Fkdg8MzipofaJYY3+vexoUIcBBADKYlXjVdgNRLtaCesQXN3F3SlnkgQs2lJdptOUq8rkRWzV4x6h5qX8MNfQprACVeMugk8LTCauhV2qOIaoEheWshpz0AvChQqFORs948lyN98CPhL0xIgIqnwY2aB5xtG2J5MmCDG5Gn1OHYmwNw+CAQPQGIspNvsgcMzxyvCiDUo5PSoYVZmpY89FH01izoeLkqPMhOB7uahVJSBKbYOMtalqkmNjlE+qsc7Ewi5F8G8BivQRzUFGeDEruYGrnc+2AWxk8saj1O425XLQE49WBqd6BLotEmIWvAgw3V1+icm+f7asTHfDSjTLwRyngF8GcWJMbUbJ13cLmGHzlmEuf2z5scnrQ+Da8ra23gQQfC5pCiwZR3DMG2OGKTPuJ94/hiHyd1iIdkRiXIRk0ynKAks9MASyA4ksDDFEM4XeW3fSxkLaRPrTMzkoNvJUkIUiQcJQFChhXM8mK+qYcOFgTMTTY7XJ6OnGdSBc43EEjM2s3zihTgINvhau6wWSsK1tMCgqggjvaGwoCgNBhAisnL6294k/4ybyROTobxeFSio7NIfovzBnGzmIRWH+PRMWA8gBa80k0ecrqGBK/wBfDWlZCaGjHRHsw1haH4qG/qWMDAurkg8ziJFwlbdtqbuNwhiCAARkv3C04JRMi6rCSI4FlQAYCMBgD0GQmg2tSumFJWRK+NyI0SpkcBzIwJsIvNr5V5cMe7wEKHjfxZIQE4KCniitTJNUbokRbJwMoEswHRxqI1bD5cxtxIEcZYRLsQeO5lTYQxdA2jRIrQLmuj2bRJdE6e4Yj0uGoQK8XXREpucSNtmP7xhAMAhjmMVQksI6BXcEirI5rWrwpnNLByPJuvlFcj4bN5NZPyyKgO4JxoTvDZoZNo0e/wCs0s1J+lf81CHDKNIAd3apCxVQwOXmmszuDAzk5WUB8yKsJc4RI53UiE3sxjR31wIg5h+rdnIO3SaxRdUJ4cOlyCWKNU4I7RscWrDge2LclbLHLAB5eBChQEhWdHZJE80K2Ojmcez7FJIADxBfc5QQyZfQac2oTwBF9CYmLDvP1fBBWKcUE3g6ShMTJ0xkboCMGyCltcHQrYh9prVBSZBh2CRwIPh0hjwYh36BxrTtyEE9H88lRiAwchc62nqZoMSATC4esFPRZvgeBcEBh4dwpm6S5HZqFAmO36TI+VPSKhcQrwYZdJZtyo39q49XW8KENFzIRpnEeMsRtENlU5YqMNVKQWMnNJ741aHFpUVAFABAGRYwmTwdaAR9MimCZEgMslYSEwApKLiJGdFZEnEK6LoGTi6Bhjz0a4AKDH93gqftWgC1QBWMfhBFMC4N2MhSy2BJUBM4YqVRMRBvKu/yS5Ph7R/SDapj2PWJB3SaG/vJiWmIgFask8Ow9XQTiRTLoQSSyruAIAnIEkBMgYu//wAZilEpGyxI/wBph9phs4nkvxnB+erbZ8cZH0cB0XuI94LcIzNCtrmbbuA8KGAFABwHGAINfSYrcWYJ/gHjo8L6qlkcE6Qt0fGIBvJXCeXiUP4LlyIfi9JUMqqJ0SihFAkxc5J02dhWKgrjTzOGgdBekp/j4g4s5THr+d6MhBhEHSLIjeBEEZHUUk2wlJRo2uSAp6M8xqftecZwSSiVJ7pkSbqVygPR/wDnZOHYrKmHh4OAUDbZrwzXB+uZyaDqfTSphRbfs+RemV8VMyCJMAAAaQYEEfRemNULdpQby4Uq8w2QqewWMtiWsDXATTSh1qSyANBsUgoTcdwugyxKaiM4RgtXWN/r84BDsF8M/wBMPhzBEmMCdBDEY0hPUSkVCNRChb7SxI0ToAMG5jFdl8Dj9YcjkNcwrI+0JwA6BhG4X1PWTce2hOXkCHksU1jau+ymB4iWVCJBMjEVfZlw7bZJX80w+sS5EFiVuQTaKAaiHI/lWNzLSdQ43DrI4CPAxFa1sBAlgJwAQa+jXlWezL/M/wDrJL8OoKBNYZEHQJgzOjldIAxLczzGKWyALdxjAJgwxknBKfrGbLVd3/eXFiA2QaO4+Mt2kEJKqtABKtAYGt46HBdQiHnIS4uiqqU80qq6GNCeVDzih8J2YbMdSIH+ofayZz2wII+sXUpHj/uxECmD1wDXwxBemM6YcmAsJqcXqoNyRdgfJao0ooiJAcEuhQzx3b+DDWM37GCf3gFGjdAW4IPhLTWqSb7slk95Ya6gSRXu1ACFtKVn1pJlyWJhHMQjBLVeCrxmMYFEEHWa6KecNH/FBxZf04MF/wBLM18MbTHqGjG5T8AqhyyZKlxJ8m+6M5el9dh/8MFFnkNmYGshuBHeRPwsGcij/wBoKPvBAHhqaCtKi9TMBhRK3xQBHosASyjOTV4PDdgPrGWNhpYkBpt1pTC+FQyLvZ57Q3gQf8Vuh/gxMw25zNfCyK1RBSjgAcONKlhavVXC6fMsQkUUBgDtH+W8JyshwxD+cMxCt3i78D6f2vTQw+o/JZoeA6olNMVC2SqcOmMz1IOVkgESSEDeKzyMRCpHkSI01WoIqWKHy6DjGhegHgA1v/jpfIudtQRioZSt4wIKCKSNwAZtDOFfClDqfGTccqZmTtRq3PJGSBxUwI9mAC4A4zWTIYY8Nej2mAlxOCJQoDFeFW7gNsJXv7IMN3gATwXW0OpRxkKNtVqPk1njCYcqqLC+iHGKD3xlgR/xoXAAr4UweI74MwajKSELLe8dwK9QhbA+jpOy1nRc9/ogyVcyFysRwkaIc6NdwRBYW0o6gYEToMIJNpRHNCFTMRKBELC4gPSDrisrCxso1sx0w0hw/Q4KAFAdMCCPjFG0GEkd/wAnAJRkvcjJgm3AAdBu4LgQdfooj0gCSlngxj5Dwax9rKaSqmCIPaDgtKv/ALQg4JUkpgjf0r0yBiq/oilcGK1bwr0MlFwMcKpGCWVVdfGITx3cDsW3KxxhGzekSyzg7EFtcplVtVXf1buRuACVXoYHPtjToFphByvQTCq30QnohA2IDCzCNf8Aub3WIhU8Dj05wyAANGsK+MsYqYe6xEF2IcBW75X6agwdh0w19BEAJK6DeDVE8rvUGdKbUY0ploPb7EKHR0S4RgHHWUJeJ39CWLZa+AlR6BOBHChviE8+4cKPjKpgvFWIIZkPgQD/ABwEYgwAaA4MKyKro29Mtmv9yzsp7hhtip5ISGzSlD1cjgp365PieMEIGxNORanKDuoU5bHZKEwuMMwICyzBwaDgFx8aUwXZiv2YxRDCyBFnnYd+FBhAgKNB1cuaz5lZstY5WNjgx1hgpbaSYGjZEuArAjNpyMfrAj400sHh3/unxhOjrAX8qn1kxB64rcGzwKvTKYfAj0m7qTZKwnBF6hRS5S4qbOIE4AP+qwII+PDElgANVCKTwFDOO6hppELiVCxEWmSvWSZ01ielO56xsWEgI8pMTwdjIQ1vAg+PsmdORiOIiMhEcf8A5aH/2Q==";
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

        $categories = $em->getRepository('AppBundle:Category')->findEnabledOnes();

        foreach ($categories as $index => &$category) {
            $categoryScore = $evaluation->getCategoryScore($category->getId());
            $category->setScore($categoryScore);
            foreach ($category->getSubcategories() as $index => &$subcategory) {
                $subCategoryScore = $evaluation->getSubcategoryScore($subcategory->getId());
                $subcategory->setScore($subCategoryScore);
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

//        foreach ($evaluation->getRestaurant()->getEmails() as $index => $email) {
//            $message = (new \Swift_Message('Viste de conformité'))
//                ->setFrom('corporate.barat@gmail.com')
//                ->setTo($email)
//                ->attach(\Swift_Attachment::fromPath($restPath.'/'.$id_evaluation.'/pdf/statistiques-'.$evaluation->getId().'.pdf'))
//                ->attach(\Swift_Attachment::fromPath($restPath.'/'.$id_evaluation.'/pdf/visite-de-conformité-'.$evaluation->getId().'.pdf'))
//                ->setBody(
//                    $this->renderView(
//                        'Email/evaluation-conformite.html.twig',
//                        array(
//                            'evaluation' => $evaluation
//                        )
//                    ),
//                    'text/html'
//                );
//            $this->get('mailer')->send($message);
//
//        }
        return new View("Ok", Response::HTTP_ACCEPTED);
    }

}