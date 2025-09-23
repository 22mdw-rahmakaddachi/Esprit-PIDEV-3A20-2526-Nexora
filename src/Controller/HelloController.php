<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelloController{

    #[Route('/hello', name: 'app_hello')]
    function afficher() : Response{
        return new Response("hello 3A20")  ;
    }
}

?>