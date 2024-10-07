<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Routing\Annotation\Route;

class DiscoveryController extends AbstractController
{
    #[Route('/', name: 'discovery')]
    public function index(PostRepository $postRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $has_user = $userRepository->findBy(['mobile'=>'09123456789']);
        if (!$has_user) {
            $factory = new PasswordHasherFactory([
                'common' => ['algorithm' => 'bcrypt'],
                'sodium' => ['algorithm' => 'sodium'],
            ]);
            $hasher = $factory->getPasswordHasher('common');

            $user = new User();
            $user->setFname("Admin");
            $user->setLname("Admin");
            $user->setMobile("09123456789");
            $user->setDeleted(0);
            $user->setRoles([ "ROLE_ADMIN", "ROLE_USER"]);
            $user->setPassword($hasher->hash('1234'));
            $entityManager->persist($user);
            $entityManager->flush();
        }

        $posts = $postRepository->findAll();
        foreach ($posts as $post) {
            if ($post->getFileType() == 'IMAGE'){
            }else{
                if ($post->getCoverImage() == "") {
                    $post->setCoverImage('/images/no-image-icon-23485.png');
                }
            }

            if ($post->getFileName() != ""){
                $post->setLink($post->getImagePath());
            }
        }

        return $this->render('discovery/grid.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/discovery/gallery/{slug}/{view}',name: 'app_discovery_gallery')]
    public function gallery(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Gallery $gallery,
        PostRepository $postRepository,
        string $view)
    {
        $posts = $postRepository->findBy([
            'gallery' => $gallery->getId()
        ]);
        foreach ($posts as $post) {
            if ($post->getFileType() == 'IMAGE'){
            }else{
                if ($post->getCoverImage() == "") {
                    $post->setCoverImage('/images/no-image-icon-23485.png');
                }
            }

            if ($post->getFileName() != ""){
                $post->setLink($post->getImagePath());
            }
        }

        return $this->render('discovery/'.$view.'.html.twig', [
            'posts' => $posts,
        ]);
    }
}
