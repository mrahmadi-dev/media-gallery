<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(): Response
    {
        return $this->render('auth/login.html.twig', [
            'controller_name' => 'AuthController',
        ]);
    }

    #[Route('/auth', name: 'auth')]
    public function auth(Request $request, Security $security, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $mobile = $request->get('mobile');
        $password = $request->get('password');

        $repository = $entityManager->getRepository(User::class);

        $user = $repository->findOneBy([
            'mobile'=> $mobile,
            'deleted' => 0
        ]);
        if($user) {
            $is_valid_password = $passwordHasher->isPasswordValid(
                $user,
                $password
            );
            if ($user && $is_valid_password) {
                $redirectResponse = $security->login($user);
                return $redirectResponse;
            }
        }

        $this->addFlash('notice','شماره موبایل یا کلمه عبور صحیح نیست.');
        return $this->redirectToRoute('app_login');
    }
}
