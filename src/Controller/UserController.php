<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    
    #[Route('/user', name: 'app_user')]
    #[IsGranted('ROLE_USER')] // Restrict access to logged-in users only
    public function index(): Response
    {
         // Retrieve the currently logged-in user
         $user = $this->getUser();

         // If user is not logged in, redirect to login page (optional)
         if (!$user) {
             return $this->redirectToRoute('app_login');
         }
 
         // Render the user page template with user data
         return $this->render('user/index.html.twig', [
             'user' => $user,
         ]);
       
    }

    #[Route("/user/profil/modifier", name:"user_profil_modifier")]
    
    public function editProfile(Request $request,
    EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditeProfileType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Profil mis à jour');
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/editprofile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route("/user/pass/modifier", name:"user_pass_modifier")]

    public function editPass(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if($request->isMethod('POST')){
            $em = $this->getDoctrine()->getManager();

            $user = $this->getUser();

            // On vérifie si les 2 mots de passe sont identiques
            if($request->request->get('pass') == $request->request->get('pass2')){
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('pass')));
                $em->flush();
                $this->addFlash('message', 'Mot de passe mis à jour avec succès');

                return $this->redirectToRoute('user');
            }else{
                $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
            }
        }

        return $this->render('users/editpass.html.twig');
    }
}
