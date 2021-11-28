<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Serializer\Encoder\JsonEncoder;
//use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
//use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
//use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
//use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
//use Symfony\Component\Serializer\Serializer;

class AuthorController extends AbstractController
{
    /**
     * @Route("/author", name="author")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AuthorController.php',
        ]);
    }

    /**
     * @Route("/author/create", name="author_create", methods={"POST"})
     * @param $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $author = new Author();
        $name = $request->query->get('name');
        if (isset($name)) {
            $author->setName($name);
            $entityManager->persist($author);
            $entityManager->flush();
            return new Response('Сохранен новый автор с id '.$author->getId());
        } else {
            return new Response('Не указано имя автора', 400);
        }
    }

    /**
     * @Route("/author/all", name="get_all_authors", methods={"GET"})
     * @return Response
     */
    public function getAllAuthors(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $authors = $entityManager->getRepository(Author::class)->findAll();

//        $defaultContext = [
//            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
//                /** @var Book $object */
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
//            $jsonBooks = $serializer->serialize($authors, 'json', ['groups' => 'author']);
//        } catch (\Exception $e) {
//            return new Response('Ошибка сериализации данных об авторах: ' . $e->getMessage(), 400);
//        }

        if (count($authors)) {
            return $this->json($authors, 200, [], ['groups' => 'book']);

//            return new Response($jsonBooks);
        } else {
            return new Response('Авторы не найдены', 400);
        }
    }

}
