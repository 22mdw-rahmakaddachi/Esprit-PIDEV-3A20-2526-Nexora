<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
}
