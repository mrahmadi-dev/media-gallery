<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Gallery;
use App\Repository\CategoryRepository;
use App\Repository\GalleryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/new',name: 'new_category')]
    public function new(): Response
    {
        return $this->render('category/new.html.twig', [
        ]);
    }

    #[Route('/category/save', name: 'save_category')]
    public function save(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $_data = $request->request->all();

        if ($_data['id'] == ""){
            $category = new Category();
            $category->setView(0);
        }else{
            $category = $entityManager->getRepository(Category::class)->find($_data['id']);
        }

        $category->setTitle($_data['title']);
        $category->setDescription($_data['description']);

        $errors = $validator->validate($category);
        if (count($errors) > 0){
            $this->addFlash('notice',$errors);
            return $this->redirectToRoute('new_category');
        }
        $entityManager->persist($category);
        $entityManager->flush();

        return $this->redirectToRoute('category');
    }

    #[Route('/category/delete/{id}',name: 'delete_category')]
    public function delete(int $id, EntityManagerInterface $entityManager)
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('category');
    }

    #[Route('/category/edit/{id}',name: 'edit_category')]
    public function edit(Category $category): Response
    {
        return $this->render('category/new.html.twig', [
            'category' => $category
        ]);
    }

}
