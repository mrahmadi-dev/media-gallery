<?php

namespace App\Controller;

use App\Entity\File_type;
use App\Entity\Gallery;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\GalleryRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use http\Url;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostController extends AbstractController
{
    #[Route('/post/list/{id}', name: 'post')]
    public function index(int $id, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findBy([
            'gallery' => $id
        ]);
        $gallery = $entityManager->getRepository(Gallery::class)->find($id);
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'gallery_id' => $id,
            'gallery_title' => $gallery->getTitle(),
            'search_url' => 'post/list/'.$id.'/search',
        ]);
    }

    #[Route('/post/list/search/{id}', name: 'search')]
    public function search(int $id, PostRepository $postRepository, EntityManagerInterface $entityManager)
    {
        $posts = $entityManager->getRepository(Post::class)->findBy([
            'gallery' => $id
        ]);
        return new Response(json_encode([
           'data' =>$posts
        ]));
    }

    #[Route('/post/new/{id}', name: 'new_post')]
    public function new(int $id, GalleryRepository $galleryRepository, TagRepository $tagRepository): Response
    {
        $galleries = $galleryRepository->findAll();
        $tags = $tagRepository->findAll();
        return $this->render('post/new.html.twig', [
            'galleries' => $galleries,
            'tags' => $tags,
            'gallery_id' => $id
        ]);
    }

    #[Route('/post/save', name: 'save_post')]
    public function save(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator,
                         SluggerInterface $slugger, GalleryRepository $galleryRepository, UploadHelper $uploadHelper): Response
    {
        $date = new \DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $sub_dir = '/'.$year.'/'.$month.'/';
        $upload_directory = $this->getParameter('post_directory').$sub_dir;
        $_data = $request->request->all();
        if ($_data['id'] == "") {
            $post = new Post();
        } else {
            $post = $entityManager->getRepository(Post::class)->find($_data['id']);
            $datetime = new \DateTime();
            $post->setUpdatedAt($datetime);
            if (!isset($_data['current_file'])) {
                $_data['current_file'] = "";
            }
            $pre_file = $post->getFileName();
        }

        $post->setTitle($_data['title']);
        $post->setAlt($_data['alt']);

        $type = $_data['type'];
        if (in_array($type, ['image_upload', 'image_link'])) {
            $post->setFileType('IMAGE');
        } else {
            $post->setFileType('VIDEO');
        }

        if ($type == 'image_upload' || $type == 'video_upload') {
            if ($type == 'image_upload') {
                $file = $request->files->get('file_image');
            } else {
                $file = $request->files->get('file_video');
            }

            if ($file) {
                $uploaded = $uploadHelper->upload($file, $upload_directory, $sub_dir);
                $post->setFileName($uploaded['file_name']);
            } else {
                if ($_data['id'] == "" ||
                    ($_data['id'] != "" && ($_data['current_file'] == ""))
                ) {
                    echo json_encode([
                        'code' => -1,
                        'message' => 'فایلی برای آپلود انتخاب نشده است'
                    ]);
                    die();
                }
            }

        } elseif ($type === 'image_link' || $type == 'video_link') {
            if ($_data['link'] == "") {
                echo json_encode([
                    'code' => -1,
                    'message' => 'لینک فایل وارد نشده است'
                ]);
                die();
            }
            $post->setLink($_data['link']);
        } elseif ($type === 'aparat') {
            if ($_data['meta_data'] == "") {
                echo json_encode([
                    'code' => -1,
                    'message' => 'کد اسکریپت آپارات وارد نشده است'
                ]);
                die();
            }
            $post->setMetaData($_data['meta_data']);
        }
        if (in_array($type, ['video_upload', 'video_link', 'aparat'])) {
            $cover = $request->files->get($type . '_cover_image');
            if ($cover) {
                $uploaded = $uploadHelper->upload($cover, $upload_directory, $sub_dir);
                $post->setCoverImage($uploaded['file_name']);
            }
        }

        if ($_data['id'] != "") {
            foreach ($post->getTags() as $tag) {
                $post->removeTag($tag);
            }
        }

        if (isset($_data['id_gallery'])) {
            $gallery = $galleryRepository->find($_data['id_gallery']);
            $post->setGallery($gallery);
        }
        if (isset($_data['tags'])) {
            foreach ($_data['tags'] as $tag) {
                $tag = $entityManager->getRepository(Tag::class)->find($tag);
                $post->addTag($tag);
            }
        }
        if (isset($_data['meta_data'])) {
            $post->setMetaData($_data['meta_data']);
        }
        $post->setDescription($_data['description']);

        if ($_data['id'] != "" && !in_array($type, ['image_link', 'video_link'])) {
            if ($post->getFileType() == 'VIDEO' && (!isset($_data['current_cover_file']) || $_data['current_cover_file'] == "")) {
                if ($post->getCoverImage() != "") {
                    $uploadHelper->remove('./uploads/posts/', $post->getCoverImage());
                }
            }
        }

        if ($_data['id'] != "" && in_array($_data['type'], ['image_upload', 'video_upload'])) {
            if ($_data['current_file'] == "" && $pre_file) {
                $uploadHelper->remove('./uploads/posts/', $pre_file);
            }
        }

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            $this->addFlash('notice', $errors);
            echo json_encode([
                'code' => -1,
                'message' => (string)$errors,
            ]);
            die();
        }
        $entityManager->persist($post);
        $entityManager->flush();

        echo json_encode([
            'code' => 1,
            'message' => 'فایل با موفقیت ذخیره شد',
            'post' => $post,
        ]);
        die();
    }

    #[Route('/post/delete/{id}', name: 'delete_post')]
    public function delete(int $id, EntityManagerInterface $entityManager, UploadHelper $uploadHelper): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        $entityManager->remove($post);
        $entityManager->flush();

        if ($post->getFileType() == 'VIDEO') {
            if ($post->getCoverImage() != "") {
                $uploadHelper->remove('./uploads/posts/', $post->getCoverImage());
            }
        }

        if ($post->getFileName() != "") {
            $uploadHelper->remove('./uploads/posts/', $post->getFileName());
        }

        return $this->redirectToRoute('post', ['id' => $post->getGallery()->getId()]);
    }

    #[Route('/post/edit/{id}', name: 'edit_post')]
    public function edit(int $id, EntityManagerInterface $entityManager, GalleryRepository $galleryRepository, TagRepository $tagRepository): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        $galleries = $galleryRepository->findAll();
        $tags = $tagRepository->findAll();
        return $this->render('post/new.html.twig', [
            'galleries' => $galleries,
            'tags' => $tags,
            'post' => $post,
            'gallery_id' => $post->getGallery()->getId()
        ]);
    }

    #[Route('/post/tag/save', name: 'save_post_tag')]
    public function save_post_tag(Request $request, EntityManagerInterface $entityManager)
    {
        $_data = $request->request->all();
        $tag = new Tag();
        $tag->setTitle($_data['title']);
        $tag->setDescription($_data['description']);

        $entityManager->persist($tag);
        $entityManager->flush();

        echo json_encode([
            'code' => 1,
            'message' => 'success',
            'id' => $tag->getId(),
            'title' => $tag->getTitle(),
        ]);
        die();
    }

    #[Route('/post/view/{id}', name: 'view_post', methods: ['GET'])]
    public function view(Post $post)
    {

        if ($post->getMetaData() != "") {
            return $this->redirect($post->getMetaData());
        } elseif ($post->getLink() != "") {
            return $this->redirect($post->getLink());
        } else {
            return $this->redirect($post->getFileNamePath());
        }
    }

    #[Route('/post/upload', name: 'multiple_upload', methods: ['POST'])]
    public function multiple_upload(Request $request,  UploadHelper $uploadHelper, EntityManagerInterface $entityManager): Response
    {

        $date = new \DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $sub_dir = '/'.$year.'/'.$month.'/';
        $upload_directory = $this->getParameter('post_directory').$sub_dir;

        $file = $request->files->get('file');

        if ($file) {
            $gallery_id = $request->get('gallery_id');
            $gallery = $entityManager->getRepository(Gallery::class)->find($gallery_id);
            $post = new Post();
            $uploaded = $uploadHelper->upload($file, $upload_directory, $sub_dir);
            $post->setFileName($uploaded['file_name']);
            $post->setFileType($uploaded['file_type']);
            $post->setGallery($gallery);
            $entityManager->persist($post);
            $entityManager->flush();
        }

        return new Response(json_encode([
            'code' => 1
        ]));
    }

    #[Route('/post/thumb/{id}/{w}/{h}', name: 'post_thumbnail', methods: ['GET'])]
    public function getThumbnail(int $id, int $w, int $h, EntityManagerInterface $entityManager)
    {
        header('Content-type: image/jpeg');
        $post = $entityManager->getRepository(Post::class)->find($id);
        $filename = $post->getThumbnailPath();
        $width = 50;
        $height = 0;

        if ($w > 0) {
            $width = $w;
        }
        if ($h > 0) {
            $height = $h;
        }

        list($width_orig, $height_orig) = getimagesize($filename);

        $ratio_orig = $width_orig / $height_orig;

        if ($width / $height > $ratio_orig) {
            $width = $height * $ratio_orig;
        } else {
            $height = $width / $ratio_orig;
        }
        $image_p = imagecreatetruecolor($width, $height);
        $image = imagecreatefromjpeg($filename);
        imagecopyresampled($image_p, $image, 0, 0, 0, 0,
            $width, $height, $width_orig, $height_orig);
        imagejpeg($image_p, null, 100);
    }
}