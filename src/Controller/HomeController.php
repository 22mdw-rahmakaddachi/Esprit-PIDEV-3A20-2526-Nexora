<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ClassroomRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
       #[Route('/show', name: 'app_show')]
    public function show(): Response
    {
        return new Response('Welcome to the Home Page!');
    }

        #[Route('/show2', name: 'app_show2')]
    public function show2(): Response
    {
        return new Response('<h1>Welcome to the Home Page!</h1>');
    }

        #[Route('/showJson', name: 'app_showJson')]
    public function showJson(): Response
    {
        return new JsonResponse('Welcome to the Home Page!');
    }

          #[Route('/msg', name: 'msg')]
    public function msg(): Response
    {
        $title = "Hello from Symfony!";
          return $this->render('home/index.html.twig', [
            't' => $title,
        ]);
    }
          #[Route('/msg2/{name}', name: 'msg2')] // ajouter un paramètre dans le route
    public function msg2(string $name): Response   // ajouter un paramètre dans la méthode
    {
          return $this->render('home/index.html.twig', [
            'n' => $name,  // passer le paramètre à la vue
          ]
        );
    }
          #[Route('/user', name: 'user')]
    public function user(): Response
    {
        $user= array(
            array('id'=> 1 , 'name' => 'amir' , 'age' => 30, 'image' => 'images/img1.jpg'),
            array('id'=> 2 , 'name' => 'sara' , 'age' => 25 , 'image' => 'images/img2.jpg'),
            array('id'=> 3 , 'name' => 'ali' , 'age' => 35, 'image' => 'images/img3.jpg'),
        );
          return $this->render('home/list.html.twig',[
            'user' => $user,
          ]);
    }
      #[Route('/det/{name}', name: 'det')]
    public function details(string $name): Response
    {
   return $this->render('home/details.html.twig', [
            'controller_name' => 'HomeController',
            'n' => $name,
        ]);    }

          #[Route('/Listuser', name: 'list_User')]
          //1- injection dedependance :  UserRepository
    public function listUser(UserRepository $repo): Response
    {
        //2- recuperer la liste des utilisateurs
        $listUser=$repo->findAll();
        return $this->render('home/user.html.twig', [
            //3- passer la liste des utilisateurs à la vue user.html.twig
            'userBD' => $listUser,
        ]);
    }
     #[Route('/add', name: 'add')]
     //1- injection dedependance :  ManagerRegistry
    public function addUser(ManagerRegistry $mr, ClassroomRepository $repo,Request $request): Response
    {
        //1- récupération de l'objet classroom  avec ref=1
        $c = $repo->find(1);
        //2- creation de l'instance
        $user = new User();
        //3- creation du formulaire et le lier à l'objet user
       $form= $this->createForm(UserType::class, $user);
       //3-1  traiter la requete
       $form->handleRequest($request);
         //3-2  vérifier si le formulaire est soumis
         if ($form-> isSubmitted()){
 //4- recuperer l'entity manager
        $em = $mr->getManager();
        //5-informer doctrine  pour ajouter un objet  persist
        $em->persist($user);
        //6-envoyer  flush
        $em->flush();
        //7- redirection vers la liste des utilisateurs
        return $this->redirectToRoute('list_User');
         }
       
        //3
        return $this->render('home/add.html.twig', [
            'f' => $form->createView(),
        ]);
    }
     #[Route('/update/{id}', name: 'update_User')]
     //1- injection dedependance :  ManagerRegistry
    public function updateUser(ManagerRegistry $mr, int $id, UserRepository $repo, Request $request): Response
    {
        //2- récupération de l'objet à modifier
        $user= $repo->find($id);
       //3- creation du formulaire et le lier à l'objet user
       $form= $this->createForm(UserType::class, $user);
       //3-1  traiter la requete
       $form->handleRequest($request);
         //3-2  vérifier si le formulaire est soumis
         if ($form-> isSubmitted()){
 //4- recuperer l'entity manager
        $em = $mr->getManager();
        //6-envoyer  flush
        $em->flush();
        //7- redirection vers la liste des utilisateurs
        return $this->redirectToRoute('list_User');
         }
       
        //3
        return $this->render('home/add.html.twig', [
            'f' => $form->createView(),
        ]);
    }
  #[Route('/remove/{id}', name: 'remove_User')]
     //1- injection dedependance :  ManagerRegistry
    public function removeUser(ManagerRegistry $mr, int $id, UserRepository $repo): Response
    {
        //2- récupération de l'objet à supprimer
        $user= $repo->find($id);
        //4- recuperer l'entity manager
        $em = $mr->getManager();
        //5-informer doctrine  pour supprimer un objet  
        $em->remove($user);
        //6-envoyer  flush
        $em->flush();
        //7- redirection vers la liste des utilisateurs
        return $this->redirectToRoute('list_User');
    }
}
