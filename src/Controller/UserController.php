<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'users')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findUsers();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }
    #[Route('/user/new',name: 'new_user')]
    public function new(): Response
    {
        return $this->render('user/new.html.twig', [
        ]);
    }

    #[Route('user/save',name: 'save_user',methods: ['POST'])]
    public function save(\Symfony\Component\HttpFoundation\Request $request,EntityManagerInterface $entityManager,
                         UserPasswordHasherInterface $passwordHasher,ValidatorInterface $validator,
                        SluggerInterface $slugger, UploadHelper $uploadHelper): Response
    {
        $_data = $request->request->all();
        if ($_data['id'] == ""){
            $user = new User();
        }else{
            $user = $entityManager->getRepository(User::class)->find($_data['id']);
            $previous_photo = $user->getPhoto();
        }

        $password = $_data['password'];

        if($_data['id'] != "" && $password == ""){
            $password = $user->getPassword();
        }

        $hashed_password = $passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setFname($_data['fname']);
        $user->setLname($_data['lname']);
        $user->setPassword($hashed_password);
        $user->setMobile($_data['mobile']);
        $user->setRoles([$_data['role']]);

        if (!isset($_data['current_photo'])) {
            $_data['current_photo'] = '';
        }

        $photo = $request->files->get('photo');
        if ($photo){
            $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $photo->move(
                    $this->getParameter('user_profile_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $user->setPhoto($newFilename);
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0){
            $this->addFlash('notice',$errors);
            return $this->redirectToRoute('new_user');
        }

        if ($_data['id'] != ""){
            if ($_data['current_photo'] == "" && $previous_photo){
                $uploadHelper->remove('./uploads/users/',$previous_photo);
            }
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('users');
    }

    #[Route('/user/edit/{id}',name: 'edit_user')]
    public function edit(User $user): Response
    {
        return $this->render('user/new.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user/delete/{id}', name: 'delete_user')]
    public function delete(int $id, EntityManagerInterface $entityManager, UploadHelper $uploadHelper): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        $user->setDeleted(1);
        $entityManager->flush();

        if ($user->getPhoto() != "") {
            $uploadHelper->remove('./uploads/users/', $user->getPhotoPath());
        }

        return $this->redirectToRoute('users');
    }

    #[Route('/test')]
    public function test()
    {
        $date = new \DateTime();
        dd($date->format('m'));
    }
}
