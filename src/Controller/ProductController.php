<?php

namespace App\Controller;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\HotelReservationType;
use App\Repository\HotelReservationRepository;
#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            ]);
    }
    #[Route('/api', name: 'hotel_reservation_api_index', methods: ['GET'])]
   
    public function index2(EntityManagerInterface $entityManager): Response
    {
        $hotelReservations = $entityManager->getRepository(Product::class)->findAll();

        return $this->json($hotelReservations);
    }
    #[Route('/api', name:'post',  methods: ['POST'])]
    public function createProduct(ManagerRegistry $doctrine,Request $request): Response
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
            'product'=>"Saved",
        ]);
    }
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
    #[Route('/api/{id}', name: 'put', methods: ['PUT'])]
    public function update(Request $request, Product $product, ManagerRegistry $doctrine): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if(isset($data['name'])) {
            $product->setName($data['name']);
        }
        
        if(isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        
        if(isset($data['price'])) {
            $product->setPrice($data['price']);
        }
        
        $entityManager = $doctrine->getManager();
        $entityManager->flush();
    
        return $this->json(['status' => 'Product updated!']);
    }
    

    #[Route('/api/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function delete1(Product $product, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json(['status' => 'Product deleted!']);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
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
    

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
