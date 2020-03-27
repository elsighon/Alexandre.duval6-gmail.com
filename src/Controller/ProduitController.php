<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="produit")
     */
    public function index(Request $request, TranslatorInterface $translator)
    {
        $pdo = $this->getDoctrine()->getManager();

        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            // Le formulaire a été envoyé, on le sauvegarde
            // On récupère le fichier du formulaire
            $fichier = $form->get('imageUpload')->getData();
            // Si un fichier a été uploadé
            if($fichier){
                $nomFichier = uniqid() .'.'. $fichier->guessExtension();

                try{
                    $fichier->move(
                        $nomFichier
                    );
                }
                catch(FileException $e){
                    $this->addFlash(
                        "danger", 
                        $translator->trans('file.error')
                    );
                    return $this->redirectToRoute('home');
                }

                $produit->setPhoto($nomFichier);
            }

            $pdo->persist($produit);
            $pdo->flush();

            $this->addFlash("success", "Produit ajouté");
        }

        $produits = $pdo->getRepository(Produit::class)->findAll();
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'produit' => $produit,

            'form_produit_new' => $form->createView()
        ]);
        }
    }

    /**
     * @Route("/produit/{id}", name="un_produit")
     */
    public function produit(Produit $produit=null, Request $request, TranslatorInterface $translator){

        if($produit != null){
            // Produit exsite, on l'affiche
            $panier = new Panier($produit);
            $form = $this->createForm(ProduitType::class, $produit);

            // Analyse la requête HTTP
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                if ($panier->getQuantite() <= $produit->getQuantite()) {
                // Le formulaire a été envoyé, on le sauvegarde
                $pdo = $this->getDoctrine()->getManager();
                $pdo->persist($panier); // prepare
                $pdo->flush();           // execute
                }

                $this->addFlash("success", "Produit mis à jour");
            }

            return $this->render('produit/produit.html.twig', [
                'produit' => $produit,
                'form_ajout_panier' => $form->createView()
            ]);
    }
    else {
        return $this->redirectToRoute('produit');
    }
}

        /**
     * @Route("/delete/{id}", name="delete_produit")
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

