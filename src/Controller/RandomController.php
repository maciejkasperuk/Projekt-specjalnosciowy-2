<?php

namespace App\Controller;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
class RandomController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @Route("/random", name="app_random", methods={"GET","POST"})
     */
    public function index(ManagerRegistry $doctrine,Request $request): Response
    {
        try {
            // Wykonanie zapytania GET do API
            $response = $this->httpClient->request('GET', 'https://baconipsum.com/api/?type=meat-and-filler&paras=1&format=json');
           
            // Pobranie danych z odpowiedzi
            $data = $response->getContent();
            $data = trim($data, "[]");
            $data = str_replace('"', '', $data);
            $product = new Product();
            $product->setName(substr($data, 0, 10));
            $product->setDescription(substr($data, 0, 200));
            $product->setPrice(rand(3,150000));
            
            $entityManager = $doctrine->getManager();
            $entityManager->persist($product);
            $entityManager->flush($product);
            // Możesz tutaj przetworzyć dane według potrzeb

            // Renderowanie szablonu Twig z danymi
            return $this->render('random.html.twig', [
                'data' => $data,
            ]);
        } catch (TransportExceptionInterface $e) {
            // Obsługa błędu, jeśli wystąpił problem z pobraniem danych z API
            // Możesz tutaj zwrócić odpowiednią odpowiedź HTTP w przypadku błędu
            return $this->render('error.html.twig', [
                'error' => 'Wystąpił błąd podczas pobierania danych z API.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

