<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Categorie; 
use App\Form\CategorieType;
use App\Form\IngredientType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CategorieController extends AbstractController
{
      /**
     *@Route("/",name="ingredient_list")
     */
  public function home()
  {
    //récupérer tous les ingredient de la table article de la BD
    // et les mettre dans le tableau $ingredient
    $ingredients= $this->getDoctrine()->getRepository(Ingredient::class)->findAll();
    return  $this->render('ingredients/index.html.twig',['ingredients' => $ingredients]);  
  }

   /**
      * @Route("/ingredient/save")
      */
     public function save() {
       $entityManager = $this->getDoctrine()->getManager();

       $ingredient = new Ingredient();
       $ingredient->setName('ingredient 3');
       $ingredient->setPrix(3000);
      
       $entityManager->persist($ingredient);
       $entityManager->flush();

       return new Response('ingredient enregisté avec id   '.$ingredient->getId());
     }


    /**
     * @Route("/ingredient/new", name="new_ingredient")
     * Method({"GET", "POST"})
     */
    public function new(Request $request) {
        $article = new Ingredient();
      
        $form = $this->createForm(IngredientType::class,$article);

        $form->handleRequest($request);
  
        if($form->isSubmitted() && $form->isValid()) {
          $article = $form->getData();
  
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($article);
          $entityManager->flush();
  
          return $this->redirectToRoute('ingredient_list');
        }
        return $this->render('ingredients/new.html.twig',['form' => $form->createView()]);
    }

      

      /**
     * @Route("/ingredient/{id}", name="ingredient_show")
     */
    public function show($id) {
        $ingredient = $this->getDoctrine()->getRepository(Ingredient::class)->find($id);
  
        return $this->render('ingredients/show.html.twig', array('ingredient' => $ingredient));
      }


    /**
     * @Route("/ingredient/edit/{id}", name="edit_ingredient")
     * Method({"GET", "POST"})
     */
    public function edit(Request $request, $id) {
        $ingredient = new Ingredient();
        $ingredient = $this->getDoctrine()->getRepository(Ingredient::class)->find($id);
  
        $form = $this->createForm(IngredientType::class,$ingredient);
  
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
  
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->flush();
  
          return $this->redirectToRoute('ingredient_list');
        }
  
        return $this->render('ingredients/edit.html.twig', ['form' => $form->createView()]);
      }

   /**
     * @Route("/ingredient/delete/{id}",name="delete_article")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id) {
        $ingredient = $this->getDoctrine()->getRepository(Ingredient::class)->find($id);
  
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($ingredient);
        $entityManager->flush();
  
        $response = new Response();
        $response->send();

        return $this->redirectToRoute('ingredient_list');
      }


    /**
     * @Route("/categorie/newCat", name="new_categorie")
     * Method({"GET", "POST"})
     */
    public function newCategorie(Request $request) {
      $categorie = new Categorie();
    
      $form = $this->createForm(CategorieType::class,$categorie);

      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()) {
        $article = $form->getData();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($categorie);
        $entityManager->flush();
      }
      return $this->render('ingredients/newCategorie.html.twig',['form' => $form->createView()]);
  }
  
  public function index(IngredientRepository $IngredientRepository)
    {

         /**
        * @Route("/api/ingredient/view", name="api_Ingredient_index", methods={"GET"})
        */
        $Ingredient = $IngredientRepository->findAll();
        $response = $this->json($Ingredient, 200, ['groups' => 'post:read']);
        return $response;

        //return $this->json($IngredientRepository->findAll(),200, [],['groups' => 'post:read']);

        
        
        //se bout de code permet d'aller récupérer le tableau de donnée ingredient
        // transformer des données en tableau =  normalisation

        

        //Tableau associatif en Json
        // On améliore le code on mettant un Serializer qui va utiliser le normalizer interface

        //$IngredientNormalises = $normalizer->normalize($Ingredient, null,['groups' => 'post:read']);
       // dans le $json on passe notre tableau $ingredient normalize en json
      
 
    
    }
        /**
        * @Route("/api/ingredient/nouveau", name="api_Ingredient_store", methods={"POST"})
        */

        public function store(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator ){

            // Ceci fonction très bien 
          
           $jsonReceive = $request-> getContent();
          try {
  
          
          $Ingredient = $serializer->deserialize($jsonReceive, Ingredient::class,'json');
  
          $errors = $validator->validate($Ingredient);
  
          if(count($errors) > 0 ){
              return $this->json($errors, 400);
          }
  
          $em->persist($Ingredient);
          $em->flush();
          
          return $this->json($Ingredient, 201,['groups' => 'post:read']);
          }catch(NotEncodableValueException $e){
              return $this ->json([
                  'status' => 400,
                  'message' => $e ->getMessage()
              ], 400);
          }
      }

}