<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Serializer\Encoder\JsonEncoder;
//use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
//use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
//use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
//use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
//use Symfony\Component\Serializer\Serializer;

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
            return new Response('Не указано id автора', 400);
        } else {
            $author = $entityManager->getRepository(Author::class)->find($authorId);
            if (!isset($author)) {
                return new Response('Указанный автор не найден', 400);
            }
        }

        if (isset($name)) {
            $book->setName($name);
            $book->setAuthor($author);
            $entityManager->persist($book);
            $entityManager->flush();
            return new Response('Сохранена новая книга с id '.$book->getId());
        } else {
            return new Response('Не указано название книги', 400);
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

//        $defaultContext = [
//            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
//                /** @var Author $object */
//                return ['id' => $object->getId(), 'name' => $object->getName()];
//            },
//        ];
//
//        try {
//            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//            $encoders = [new JsonEncoder()];
//            $normalizers = [new ObjectNormalizer($classMetadataFactory, null, null, null, null, null, $defaultContext)];
//
//            $serializer = new Serializer($normalizers, $encoders);
//            $jsonBooks = $serializer->serialize($books, 'json', ['groups' => 'book']);
//        } catch (\Exception $e) {
//            return new Response('Ошибка сериализации данных о книгах: ' . $e->getMessage(), 400);
//        }

        if (count($books)) {
//            return new Response($jsonBooks);
            return $this->json($books, 200, [], ['groups' => 'book']);
        } else {
            return new Response('Книги не найдены', 400);
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
            return new Response('Не указана строка поиска', 400);
        }

        $bookRepository = $this->getBookRepository();
        $books = $bookRepository->search($query);

        if (isset($books) && count($books)) {
            return $this->getJsonResponse($books, 200, [], ['groups' => 'book']);
        } else {
            return new Response('Книги не найдены', 400);
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
