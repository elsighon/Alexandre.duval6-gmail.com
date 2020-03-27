<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class PanierController extends AbstractController
{
    /**
     * @Route("/", name="panier")
     */
    public function index(Request $request, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $panier = $em->getRepository(Panier::class)->findAll();
        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
            'controller_name' => 'PanierController',
        ]);
    }

           /**
     * @Route("/delete/{id}", name="delete_produit_panier")
     */

    public function delete(produit $produit=null, TranslatorInterface $translator){
        if($produit != null){
            $pdo = $this->getDoctrine()->getManager();
            $pdo->remove($produit); // Suppression
            $pdo->flush();

            $this->addFlash("success", "Produit supprimé");
        }
        else{
            $this->addFlash("danger", "Produit introuvable");
        }
        // Dans tous les cas, on redirige vers les catégories
        return $this->redirectToRoute('produit');
    }
}
