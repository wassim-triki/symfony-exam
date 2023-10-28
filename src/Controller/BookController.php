<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Form\SearchBookType;
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
    public function showBooks(Request $request, BookRepository $bookRepository): Response
    {
        $form = $this->createForm(SearchBookType::class);
        $form->handleRequest($request);

        $books = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $ref = $form->get('ref')->getData();
            if ($ref) {
                $books = $bookRepository->findByRef($ref);
            } else {
            }
        }else{
            $books = $bookRepository->findOrderedByAuthor();
        }
        $publishedBooksCount = $bookRepository->countPublishedBooks();
        $nonPublishedBooksCount = $bookRepository->countNonPublishedBooks();
        $scienceFictionCount = $bookRepository->countBooksByCategory('Science Fiction');


        $startDate = new \DateTime('2014-01-01');
        $endDate = new \DateTime('2018-12-31');
        $booksByDate = $bookRepository->findBooksPublishedBetween($startDate, $endDate);
        $booksBefore = $bookRepository->findBooksBefore2023ByAuthorsWithMoreThan35Books();
        return $this->renderForm('book/showbooks.html.twig', [
            'books' => $books,
            'form' => $form,
            'publishedBooksCount' => $publishedBooksCount,
            'nonPublishedBooksCount' => $nonPublishedBooksCount,
            'scienceFictionCount' => $scienceFictionCount,
            'booksByDate' =>$booksByDate,
            'booksBefore' => $booksBefore,
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
            $author = $book->getAuthor();
            $author->setNbBooks($author->getNbBooks()+1);
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
