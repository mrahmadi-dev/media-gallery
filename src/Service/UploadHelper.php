<?php

namespace App\Service;


use App\Entity\File_type;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\AsciiSlugger;

class UploadHelper
{


    public function upload($file, $upload_directory, $sub_dir = '')
    {
        $slugger = new AsciiSlugger();
        $extension = $file->guessExtension();
        $allow_types = [
            'jpg','png','jpeg','gif','mp4','avi','wmv','webm','mkv'
        ];
        if (!in_array($extension,$allow_types)){
            echo json_encode([
                'code' => -1,
                'message' => 'پسوند فایل انتخابی مجاز نمی باشد.'
            ]);
            die();
        }
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;

        // Move the file to the directory where brochures are stored
        try {
            $file->move(
                $upload_directory,
                $newFilename
            );
            if (in_array($extension,['mp4','avi','wmv','webm','mkv'])){
                $file_type = File_type::VIDEO->value;
            }else{
                $file_type = File_type::IMAGE->value;
            }
            return [
                'file_name' => $sub_dir.$newFilename,
                'file_type' => $file_type
            ];
        } catch (FileException $e) {
            return "";
            // ... handle exception if something happens during file upload
        }
    }

    public function remove($directory, $filename)
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($directory.$filename)) {
            $filesystem->remove($directory.$filename);
        }

    }


}