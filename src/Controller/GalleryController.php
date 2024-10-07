<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Gallery;
use App\Entity\Tag;
use App\Repository\CategoryRepository;
use App\Repository\GalleryRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GalleryController extends AbstractController
{
    #[Route('/gallery', name: 'gallery')]
    public function index(GalleryRepository $galleryRepository): Response
    {
        $galleries = $galleryRepository->findAll();
        return $this->render('gallery/index.html.twig', [
            'galleries' => $galleries,
        ]);
    }

    #[Route('/gallery/new',name: 'new_gallery')]
    public function new(CategoryRepository $categoryRepository,TagRepository $tagRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        return $this->render('gallery/new.html.twig', [
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    #[Route('/gallery/save', name: 'save_gallery')]
    public function save(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $_data = $request->request->all();
        if ($_data['id'] == ''){
            $gallery = new Gallery();
        }else{
            $gallery = $entityManager->getRepository(Gallery::class)->find($_data['id']);
        }

        $gallery->setTitle($_data['title']);
        $gallery->setDescription($_data['description']);
        $slug = str_replace(' ','-',$_data['slug']);
        if ($_data['id'] == ""){
            $gallery->setView(0);
            if ($_data['slug'] != ""){
                $gallery->setSlug($slug);
            }
        }else{
            $gallery->setSlug($slug);
            foreach ($gallery->getTags() as $tag){
                $gallery->removeTag($tag);
            }
            foreach ($gallery->getTags() as $tag){
                $gallery->removeTag($tag);
            }
        }

        if (isset($_data['categories'])){
            foreach ($_data['categories'] as $category){
                $category = $entityManager->getRepository(Category::class)->find($category);
                $gallery->addCategory($category);
            }
        }
        if (isset($_data['tags'])){
            foreach ($_data['tags'] as $tag){
                $tag = $entityManager->getRepository(Tag::class)->find($tag);
                $gallery->addTag($tag);
            }
        }
        $errors = $validator->validate($gallery);
        if (count($errors) > 0){
            $this->addFlash('notice',$errors);
            return $this->redirectToRoute('new_gallery');
        }
        $entityManager->persist($gallery);
        $entityManager->flush();

        if ($_data['id'] == ""){
            if ($slug == ""){
                $gallery->setSlug($gallery->getId());
            }
            $entityManager->persist($gallery);
            $entityManager->flush();
        }
        return $this->redirectToRoute('gallery');
    }

    #[Route('/gallery/delete/{id}',name: 'delete_gallery')]
    public function delete(int $id, EntityManagerInterface $entityManager)
    {
        $gallery = $entityManager->getRepository(Gallery::class)->find($id);
        $entityManager->remove($gallery);
        $entityManager->flush();

        return $this->redirectToRoute('gallery');
    }

    #[Route('/gallery/edit/{id}',name: 'edit_gallery',methods: ['GET'])]
    public function edit(int $id, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository,TagRepository $tagRepository)
    {
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();

        $gallery = $entityManager->getRepository(Gallery::class)->find($id);
        return $this->render('gallery/new.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
            'gallery' => $gallery
        ]);
    }

    #[Route('/gallery/search',name: 'search_gallery')]
    public function search(Request $request, EntityManagerInterface $entityManager)
    {
        $search_value = $request->get('value');

        $galleries = $entityManager->getRepository(Gallery::class)->createQueryBuilder('g')
            ->where('g.title LIKE :value')
            ->setParameter('value','%'.$search_value.'%')->getQuery()->getResult();
        return $this->render('gallery/index.html.twig', [
            'galleries' => $galleries,
        ]);
    }

}
