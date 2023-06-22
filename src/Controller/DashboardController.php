<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/api", name="api_")
 */

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DashboardController.php',
        ]);
    }
    #[Route('/api', name: 'hotel_reservation_api_index', methods: ['GET'])]

    public function index2(EntityManagerInterface $entityManager): Response
    {
        $hotelReservations = $entityManager->getRepository(Product::class)->findAll();

        return $this->json($hotelReservations);
    }
    #[Route('/api', name: 'post',  methods: ['POST'])]
    public function createProduct(ManagerRegistry $doctrine, Request $request): Response
    {
        $product = new Product();
        $data = json_decode($request->getContent(), true);


        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($product);
        $entityManager->flush($product);

        return $this->json([
            'product' => "Saved",
        ]);
    }
    #[Route('/api/{id}', name: 'patch', methods: ['PATCH'])]
    public function apiUpdate(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        foreach ($data as $key => $value) {
            if ($value !== null) {
                $method = 'set' . ucfirst($key);
                if (method_exists($product, $method)) {
                    $product->$method($value);
                }
            }
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Product updated',
        ]);
    }
    #[Route('/api/{id}', name: 'put', methods: ['PUT'])]
    public function updateProduct(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product->setName($data['name'] ?? $product->getName());
        $product->setDescription($data['description'] ?? $product->getDescription());
        $product->setPrice($data['price'] ?? $product->getPrice());

        $entityManager->flush();

        return $this->json([
            'message' => 'Product updated',
        ]);
    }
}
