<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ninsw;
use App\Form\NinswType;
use Symfony\Component\HttpFoundation\Request;


class NinswController extends AbstractController
{
    /**
     * @Route("/ninsw/all",name="ninsw_all")
     */
    public function allAction()
    {
        $em = $this->getDoctrine()->getManager();

        $ninsw = $em->getRepository(Ninsw::class)->findAll();

        return $this->render('ninsw/all.html.twig', array(
            'ninsw' => $ninsw,
        ));
    }

    /**
     * @Route("/ninsw/admin",name="ninsw_admin")
     */
    public function adminAction()
    {
        $em = $this->getDoctrine()->getManager();

        $ninsw = $em->getRepository(Ninsw::class)->findAll();

        return $this->render('ninsw/admin.html.twig', array(
            'ninsw' => $ninsw,
        ));
    }
    /**
     * @Route("/ninsw/details/{id}", methods="GET", name="ninsw_details")
     */
    public function detailsAction($id): Response
    {
        $ninsw = $this->getDoctrine()->getRepository(Ninsw::class)->find($id);

        if (!$ninsw) {
            throw $this->createNotFoundException('Ninsw not found');
        }
        $form   = $this->createFormBuilder([

            'method' => 'GET'
        ])
            ->getForm();

        return $this->render('ninsw/details.html.twig', [
            'form'     =>  $form->createView(),
            'ninsw'  => $ninsw,
        ]);
    }


/**
 * @Route("/ninsw/edit/{id}", name="ninsw_edit")
 */
public function editAction($id, Request $request)
{
    $entityManager = $this->getDoctrine()->getManager();
    $ninsw = $entityManager->getRepository(Ninsw::class)->find($id);

    $form = $this->createForm(NinswType::class, $ninsw);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Get the submitted image file
        $imageFile = $form->get('image')->getData();

        // Check if a new image file has been uploaded
        if ($imageFile) {
            // Generate a unique name for the file
            $fileName = md5(uniqid()).'.'.$imageFile->guessExtension();

            // Move the file to the directory where images are stored
            try {
                $imageFile->move(
                    $this->getParameter('image_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // Handle if file upload fails
                // For example, log the error message
                $this->logger->error('Failed to upload image: '.$e->getMessage());
            }

            // Set the image path to the product entity
            $ninsw->setImage($fileName);
        }

        // Lưu thay đổi vào cơ sở dữ liệu
        $entityManager->flush();

        $this->addFlash(
            'notice',
            'Ninsw Edited'
        );

        return $this->redirectToRoute('ninsw_all');
    }

    return $this->render('ninsw/edit.html.twig', [
        'form' => $form->createView()
    ]);
}

/**
 * Creates a new ninsw entity.
 *
 * @Route("/ninsw/new", methods={"GET", "POST"}, name="ninsw_new")
 */
public function newAction(Request $request)
{
    $ninsw = new Ninsw();
    $form = $this->createForm(NinswType::class, $ninsw);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Get the submitted image file
        $imageFile = $form->get('image')->getData();

        // Check if an image file has been uploaded
        if ($imageFile) {
            // Generate a unique name for the file
            $fileName = md5(uniqid()).'.'.$imageFile->guessExtension();

            // Move the file to the directory where images are stored
            try {
                $imageFile->move(
                    $this->getParameter('image_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // Handle if file upload fails
                // For example, log the error message
                $this->logger->error('Failed to upload image: '.$e->getMessage());

                // Alternatively, you can display an error message to the user
                // return $this->render('error.html.twig', ['error' => 'Failed to upload image']);
            }

            // Set the image path to the product entity
            $ninsw->setImage($fileName);
        }

        // Save the product to the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($ninsw);
        $entityManager->flush();

        return $this->redirectToRoute('ninsw_all');
    }

    return $this->render('ninsw/new.html.twig', [
        'ninsw' => $ninsw,
        'form' => $form->createView(),
    ]);
}
 

}


