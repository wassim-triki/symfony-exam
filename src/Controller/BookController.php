<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[Route('/showbooks', name: 'showbooks')]
    public function showBooks(BookRepository $bookRepository): Response
    {

        $books = $bookRepository->findAll();
        return $this->render('book/showbooks.html.twig', [
            'books' => $books
        ]);
    }

    #[Route('/addbook', name: 'addbook')]
    public function addBook(ManagerRegistry $managerRegistry, Request $req): Response
    {
        $em = $managerRegistry->getManager();
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()) {
            $em->persist($book);
            $em->flush();
            return $this->redirect('showbooks');
        }
        return $this->renderForm('book/addbook.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/deletebook/{ref}', name: 'deletebook')]
    public function deleteBook($ref,BookRepository $bookRepository,ManagerRegistry $managerRegistry): Response
    {
        $book = $bookRepository->find($ref);
        $em = $managerRegistry->getManager();
        $em->remove($book);
        $em->flush();
        return $this->redirectToRoute('showbooks');
    }

    #[Route('/editbook/{ref}', name: 'editbook')]
    public function editBook($ref,BookRepository $bookRepository,ManagerRegistry $managerRegistry,Request $req): Response
    {
        $em = $managerRegistry->getManager();
        $book = $bookRepository->find($ref);
        $form = $this->createForm(BookType::class,$book);
        $form->handleRequest($req);
        if( $form->isSubmitted()&& $form->isValid()){
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('showbooks');
        }

        return $this->renderForm('book/addbook.html.twig', [
            'form'=>$form
        ]);
    }

    #[Route('/showbook/{ref}', name: 'showbook')]
    public function showBook($ref,BookRepository $bookRepository,ManagerRegistry $managerRegistry): Response
    {
        $book = $bookRepository->find($ref);
        return $this->renderForm('book/showbook.html.twig', [
            'book'=>$book
        ]);
    }
}