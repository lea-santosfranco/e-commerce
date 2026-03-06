<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\Product;
use App\Form\AddProductHistoryType;
use App\Form\ProductType;
use App\Repository\AddProductHistoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/product')]
#[IsGranted('ROLE_ADMIN')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

        if ($image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeImageName = $slugger->slug($originalName);
            $newFileImageName = $safeImageName.'-'.uniqid().'.'.$image->guessExtension();

            try{
                $image->move(
                    $this->getParameter('image_directory'),
                    $newFileImageName
                );
            } catch (FileException $exception) {}
            $product->setImage($newFileImageName);
        }
            $entityManager->persist($product);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
 #[Route('/add/product/{id}/stock', name: 'app_product_stock_add', methods: ['POST', 'GET'])]
    public function stockAdd($id, EntityManagerInterface $entityManager, Request $request, ProductRepository $productRepository): Response
    {
        $stockAdd = new AddProductHistory();
        $form = $this->createForm(AddProductHistoryType::class, $stockAdd);
        $form->handleRequest($request);
        $product = $productRepository->find($id);

        if ($form->isSubmitted() && $form->isValid()) {
            
            if ($stockAdd->getQuantity() > 0) {
                $newQuantity = $product->getStock() + $stockAdd->getQuantity();
                $product->setStock($newQuantity);
                $stockAdd->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));
                $stockAdd->setProduct($product);
                
                $entityManager->persist($stockAdd);
                $entityManager->flush();
                
                $this->addFlash('info', 'Votre stock a bien été mis à jour');
                return $this->redirectToRoute('app_product_index');
            }else {
                $this->addFlash('danger', "Le stock du produit ne doit pas être inférieur à zéro");
                return $this->redirectToRoute('app_product_stock_add', ['id'=>$product->getId()]);
            }
        }
        return $this->render('product/addStock.html.twig', ['form' => $form->createView(), 'product' => $product,]);
    }
     #[Route('/add/product/{id}/stock/history', name: 'app_product_stock_add_history', methods: ['GET'])]
    public function showHistoryProductStock($id, ProductRepository $productRepository, AddProductHistoryRepository $addProductHistoryRepository):Response
    {
        $product = $productRepository->find($id);
        $productAddHistory = $addProductHistoryRepository->findBy(['product'=>$product],['id'=>'DESC']);
        
        return $this->render('product/addedHistoryStockShow.html.twig',[
            "productsAdded"=>$productAddHistory
        ]);
    }   
}
