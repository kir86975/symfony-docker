<?php

namespace App\Controller;

use App\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
            return $this->json(['success' => 'Сохранен новый автор с id '.$author->getId()]);
        } else {
            return $this->json(['error' => 'Не указано имя автора'], 400);
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

        if (count($authors)) {
            return $this->json($authors, 200, [], ['groups' => 'book']);
        } else {
            return $this->json(['error' => 'Авторы не найдены'], 400);
        }
    }

}
