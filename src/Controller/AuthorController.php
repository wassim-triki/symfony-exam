<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorFilterType;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }


    #[Route('/showauthors', name: 'showauthors')]
    public function showAuthors(AuthorRepository $authorRepository, Request $request): Response
    {
        $form = $this->createForm(AuthorFilterType::class);
        $form->handleRequest($request);

        $min = null;
        $max = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $min = $form->get('min')->getData();
            $max = $form->get('max')->getData();
        }

        $authors = $authorRepository->findByBookCountRange($min, $max);

        return $this->renderForm('author/showauthors.html.twig', [
            'authors' => $authors,
            'form' => $form
        ]);
    }


    #[Route('/addauthor', name: 'addauthor')]
    public function addAuthor(ManagerRegistry $managerRegistry, Request $req): Response
    {
        $em = $managerRegistry->getManager();
        $author = new Author();
        $author->setNbBooks(0);
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()) {
            $em->persist($author);
            $em->flush();
            return $this->redirect('showauthors');
        }
        return $this->renderForm('author/addauthor.html.twig', [
            'form' => $form
        ]);
    }


    #[Route('/editauthor/{id}', name: 'editauthor')]
    public function editAuthor($id, AuthorRepository
                               $authorRepository, ManagerRegistry $managerRegistry, Request $req): Response
    {
        // var_dump($id) . die();
        $em = $managerRegistry->getManager();
        $author = $authorRepository->find($id);
        //var_dump($dataid) . die();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()) {
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('showauthors');
        }

        return $this->renderForm('author/addauthor.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/deletauthor/{id}', name: 'deletauthor')]
    public function deleteAuthor($id, ManagerRegistry $managerRegistry, AuthorRepository $repo): Response
    {
        $em = $managerRegistry->getManager();
        $id = $repo->find($id);
        $em->remove($id);
        $em->flush();
        return $this->redirectToRoute('showauthors');
    }


    #[Route('/delete-authors-with-no-books', name: 'delete_authors_with_no_books')]
    public function deleteAuthorsWithNoBooks(ManagerRegistry $managerRegistry): Response
    {
        $em = $managerRegistry->getManager();

        // Use DQL to delete authors with no books
        $query = $em->createQuery(
            'DELETE FROM App\Entity\Author a WHERE a.nb_books = 0'
        );
        $query->execute();

        return $this->redirectToRoute('showauthors');
    }



}
