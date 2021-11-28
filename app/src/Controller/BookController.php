<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BookController extends AbstractController
{
    /** @var ObjectRepository */
    public $repository;

    /** @var callable */
    private $jsonResponseMethod;

    /**
     * @Route("/book", name="book")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/BookController.php',
        ]);
    }

    /**
     * @Route("{_locale}/book/{id}", name="book_by_id")
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function book(Request $request, TranslatorInterface $translator): Response {
        $bookRepository = $this->getBookRepository();

        $book = $bookRepository->find($request->get('id'));
        if (!$book) {
            return $this->json(['error' => 'Книги с указанным идентификатором не найдено'], 400);
        }

        $bookName = explode('|', $book->getName());

        $book->setName($translator->trans($bookName[0]) . '|' . $bookName[1]);
        return $this->json($book, 200, [], ['groups' => 'book']);
    }

    /**
     * @Route("/book/create", name="book_create", methods={"POST"})
     * @param $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $book = new Book();
        $name = $request->query->get('name');
        $authorId = $request->query->get('authorId');

        /** @var Author $author */
        $author = null;
        if (!isset($authorId)) {
            return $this->json(['error' => 'Не указано id автора'], 400);
        } else {
            $author = $entityManager->getRepository(Author::class)->find($authorId);
            if (!isset($author)) {
                return $this->json(['error' => 'Указанный автор не найден'], 400);
            }
        }

        if (isset($name)) {
            $book->setName($name);
            $book->setAuthor($author);
            $entityManager->persist($book);
            $entityManager->flush();
            return $this->json(['success' => 'Сохранена новая книга с id '.$book->getId()]);
        } else {
            return $this->json(['error' => 'Не указано название книги'], 400);
        }
    }

    /**
     * @Route("/book/all", name="get_all_books", methods={"GET"})
     * @return Response
     */
    public function getAllBooks(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $books = $entityManager->getRepository(Book::class)->findAll();


        if (count($books)) {
            return $this->json($books, 200, [], ['groups' => 'book']);
        } else {
            return $this->json(['error' => 'Книги не найдены'], 400);
        }
    }

    /**
     * @Route("/book/search", name="book_search", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function search(Request $request): Response
    {
        $query = $request->get('query');

        if (!isset($query)) {
            return $this->getJsonResponse(['error' => 'Не указана строка поиска'], 400);
        }

        $page = $request->get('page', 1);
        $offset = $request->get('offset', 100);

        $bookRepository = $this->getBookRepository();
        $books = $bookRepository->search($query, $page, $offset);

        if (isset($books) && count($books)) {
            return $this->getJsonResponse($books, 200, [], ['groups' => 'book']);
        } else {
            return $this->getJsonResponse(['error' => 'Книги не найдены'], 400);
        }
    }

    /**
     * @return ObjectRepository
     */
    public function getBookRepository() {
        if (!isset($this->repository)) {
            $entityManager = $this->getDoctrine()->getManager();

            $this->repository = $entityManager->getRepository(Book::class);
        }

        return $this->repository;
    }

    /**
     * @param $repository
     */
    public function setBookRepository($repository) {
        $this->repository = $repository;
    }

    public function getJsonResponse($data, int $status = 200, array $headers = [], array $context = []) {
        if (!$this->jsonResponseMethod) {
            return $this->json($data, $status, $headers, $context);
        }

        return call_user_func($this->jsonResponseMethod, $data, $status, $headers, $context);
    }

    public function setJsonResponseMethod(callable $jsonResponser = null) {
        if (isset($jsonResponser)) {
            $this->jsonResponseMethod = $jsonResponser;
        }
    }
}
