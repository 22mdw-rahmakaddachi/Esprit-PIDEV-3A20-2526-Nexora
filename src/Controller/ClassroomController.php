<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ClassroomRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClassroomController extends AbstractController
{
    #[Route('/classroom', name: 'app_classroom')]
    public function index(): Response
    {
        return $this->render('classroom/index.html.twig', [
            'controller_name' => 'ClassroomController',
        ]);
    }

      #[Route('/ListClassroom', name: 'ListClassroom')]
          //1- injection dedependance :  UserRepository
    public function ListClassroom(ClassroomRepository $repo): Response
    {
        //2- recuperer la liste des utilisateurs
        $ListClassroom=$repo->findAll();
        return $this->render('classroom/list.html.twig', [
            //3- passer la liste des utilisateurs à la vue user.html.twig
            'classroom' => $ListClassroom,
        ]);
}

  #[Route('/addUC/{id}', name: 'add_user_classroom')]
    public function addUserToClassroom(int $id,ClassroomRepository $repo,Request $request,ManagerRegistry $mr): Response
    {
        //1- recuperer classroom par son id
        $classroom=$repo->find($id);
        //2- crrer l'instance de user
        $user=new User();
        //3- creation formulaire
        $form=$this->createForm(UserType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted()){
            $em=$mr->getManager();
            $user->setClassroom($classroom);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('ListClassroom');
        }
        return $this->render('classroom/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
