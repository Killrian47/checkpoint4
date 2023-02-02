<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Form\ShopType;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/shop')]
class ShopController extends AbstractController
{
    #[Route('/', name: 'app_shop_index', methods: ['GET'])]
    public function index(ShopRepository $shopRepository): Response
    {
        return $this->render('shop/index.html.twig', [
            'items' => $shopRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_shop_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ShopRepository $shopRepository): Response
    {
        $shop = new Shop();
        $form = $this->createForm(ShopType::class, $shop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shopRepository->save($shop, true);

            return $this->redirectToRoute('app_shop_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('shop/new.html.twig', [
            'shop' => $shop,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_shop_show', methods: ['GET'])]
    public function show(Shop $shop): Response
    {
        return $this->render('shop/show.html.twig', [
            'shop' => $shop,
        ]);
    }

    #[Route('/{id}/buy', name: 'app_item_bought')]
    public function someOnBuyIt(Shop $shop, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if ($user->getPoints() < $shop->getPrice()) {
            $this->addFlash('danger', 'You don\'t have much money to buy it');
            return $this->redirectToRoute('app_shop_show', [
                'id' => $shop->getId()
            ]);
        } else {
            $user->setPoints($user->getPoints() - $shop->getPrice());
            $user->setGoldenTickets($user->getGoldenTickets() + 1);
            $shop->setStock($shop->getStock() - 1);
            $manager->persist($user);
            $manager->persist($shop);
            $manager->flush();
        }

        return $this->redirectToRoute('app_shop_show', [
            'id' => $shop->getId()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_shop_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Shop $shop, ShopRepository $shopRepository): Response
    {
        $form = $this->createForm(ShopType::class, $shop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shopRepository->save($shop, true);

            return $this->redirectToRoute('app_shop_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('shop/edit.html.twig', [
            'shop' => $shop,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_shop_delete', methods: ['POST'])]
    public function delete(Request $request, Shop $shop, ShopRepository $shopRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $shop->getId(), $request->request->get('_token'))) {
            $shopRepository->remove($shop, true);
        }

        return $this->redirectToRoute('app_shop_index', [], Response::HTTP_SEE_OTHER);
    }
}
