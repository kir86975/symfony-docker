<?php


namespace App\Tests\Contoller;


use App\Controller\BookController;
use App\Repository\BookRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class BookControllerTest extends TestCase {

    public function testSearchSetNullQueryGetNullQueryError() {
        $stubRequest = $this->createMock(Request::class);
        $stubRequest->method('get')
            ->with('query')
            ->willReturn(null);

        $bookController = new BookController();

        $response = $bookController->search($stubRequest);

        $this->assertEquals('Не указана строка поиска', $response->getContent());
    }

    public function testSearchBookQueryGetNoBookError() {
        $queryString = 'Мир';

        $stubRequest = $this->createMock(Request::class);
        $stubRequest->method('get')
            ->with('query')
            ->willReturn($queryString);

        $stubRepository = $this->createMock(BookRepository::class);
        $stubRepository->method('search')
            ->with($queryString)
            ->willReturn([]);

        $bookController = new BookController();
        $bookController->setBookRepository($stubRepository);

        $response = $bookController->search($stubRequest);

        $this->assertEquals('Книги не найдены', $response->getContent());
    }

    public function testSearchBookQueryGetJsonAnswer() {
        $stubRequest = $this->createMock(Request::class);
        $stubRequest->method('get')
            ->with('query')
            ->willReturn('Мир');

        $booksData = [
            [
                "id" => 1,
                "name" => "War and Peace|Война и мир",
                "author" => [
                    "id" => 1,
                    "name" => "Лев Толстой"
                ]
            ]
        ];

        $stubRepository = $this->createMock(BookRepository::class);
        $stubRepository->method('search')
            ->with('Мир')
            ->willReturn($booksData);

        $bookController = new BookController();
        $bookController->setBookRepository($stubRepository);
        $bookController->setJsonResponseMethod(
            function($data, int $status = 200, array $headers = [], array $context = []) {
                return new JsonResponse($data, $status, $headers);
            }
        );

        $response = $bookController->search($stubRequest);

        $this->assertJsonStringEqualsJsonString(json_encode($booksData), $response->getContent());
    }
}
