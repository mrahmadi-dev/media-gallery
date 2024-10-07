<?php

namespace App\Entity;

use App\Repository\PostRepository;
use App\Service\SiteHelper;
use App\Service\UploadHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use http\Client\Request;
use Morilog\Jalali\Jalalian;
use PharIo\Manifest\Url;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints as Assert;

enum File_type: string
{
    case IMAGE = "IMAGE";
    case VIDEO = "VIDEO";
}

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

//    #[ORM\Column(type: 'string',enumType: File_type::class)]
    #[ORM\Column(type: 'string')]
//    #[Assert\NotBlank(message: 'انتخاب ')]
    private ?string $file_type = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $alt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file_name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[Assert\NotBlank(message: 'گالری انتخاب نشده است.')]
    private ?Gallery $gallery = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover_image = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'posts')]
    private Collection $tags;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $meta_data = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    public function __construct()
    {
        $datetime = new \DateTime();
        $this->tags = new ArrayCollection();
        $this->created_at = $this->updated_at = $datetime;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->file_type;
    }

    public function setFileType(string $file_type): static
    {
        $this->file_type = $file_type;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): static
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setGallery(?Gallery $gallery): static
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getImagePath()
    {
        return '/uploads/posts/'.$this->getFileName();
    }

    public function getCoverImagePath()
    {
        $h = new SiteHelper();
        if ($this->getCoverImage()) {
            return $h->getBaseUrl().'/uploads/posts/'.$this->getCoverImage();
        }else{
            return $h->getBaseUrl().'/images/play-video-cover.png';
        }
        
    }

    public function getThumbnailPath()
    {
        if ($this->getFileType() == 'IMAGE') {
            $image = $this->getFileNamePath();
            $converted = $this->resizeImage($image);
            return $converted;
        }else{
            return $this->getCoverImagePath();
        }
    }

    protected function resizeImage($img)
    {

        $filename = $img;

        $width = 100;
        $height = 100;

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

    public function getCoverImage(): ?string
    {
        return $this->cover_image;
    }

    public function setCoverImage(?string $cover_image): static
    {
        $this->cover_image = $cover_image;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getMetaData(): ?string
    {
        return $this->meta_data;
    }

    public function setMetaData(?string $meta_data): static
    {
        $this->meta_data = $meta_data;

        return $this;
    }

    public function getGalleriesArray()
    {
        $res = [];
        foreach ($this->getGallery() as $item) {
            $res[] = $item->getId();
        }
        return $res;
    }

    public function getTagsArray()
    {
        $res = [];
        foreach ($this->getTags() as $item) {
            $res[] = $item->getId();
        }
        return $res;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function datetime($format = 'Y-m-d H:i')
    {
        $datetime = Jalalian::forge($this->getUpdatedAt())->format($format);
        return $datetime;
    }

    public function fileTypeFa()
    {
        if ($this->getFileType() == 'IMAGE'){
            return 'تصویر';
        }else{
            return 'ویدیو';
        }
    }

    public function getFileNamePath()
    {
        $h = new SiteHelper();
        return $h->getBaseUrl().'/uploads/posts/'.$this->getFileName();

    }

    public function viewPostLink()
    {
        $h = new SiteHelper();
        return $h->getBaseUrl().'/post/view/'.$this->getId();
    }

    public function galleryUrl()
    {
        $h = new SiteHelper();
        return $h->getBaseUrl().'/discovery/gallery/'.$this->gallery->getSlug().'/slide';
    }

    public function jsonSerialize(): mixed
    {
        $h = new SiteHelper();
        return [
            'title'          => $this->getTitle(),
            'fileType' => $this->getFileType(),
            'alt'     => $this->getAlt(),
            'fileName'   => $this->getFileName(),
            'link' => $this->getLink(),
            'fileNamePath' => $this->getFileNamePath(),
            'coverImagePath' => $this->getCoverImagePath(),
            'metadata' => $this->getMetaData(),
            'gallery_id' => $this->getGallery()->getId(),
            'fileTypeFa' => $this->fileTypeFa(),
            'getThumbnailPath' => $h->getBaseUrl().'/post/thumb/'.$this->getId().'/60/60',
            'viewPostLink' => $this->viewPostLink(),
            'galleryTitle' => $this->getGallery()->getTitle(),
            'galleryUrl' => $this->galleryUrl(),
            'deleteUrl'=> '/post/delete/'.$this->getId(),
            'editUrl'=> '/post/edit/'.$this->getId(),
        ];
    }
}
