<?php
namespace Manychois\Wpx;

/**
 * Represents an image in a WordPress gallery.
 */
class GalleryItem
{
    /**
     * The associated attachment Id.
     * @var int
     */
    public $id;
    /**
     * The link of the attachment post.
     * @var string
     */
    public $postUrl;
    /**
     * The link of the original image file.
     * @var string
     */
    public $url;
    /**
     * The width of the original image file.
     * @var int
     */
    public $width;
    /**
     * The height of the original image file.
     * @var mixed
     */
    public $height;
    /**
     * The title of the image.
     * @var string
     */
    public $title;
    /**
     * The alt property of the image.
     * @var string
     */
    public $alt;
    /**
     * The description of the image.
     * @var string
     */
    public $description;
    /**
     * The caption of the image.
     * @var string
     */
    public $caption;
    /**
     * A list of all pre-defined sizes of image.
     * @var array
     */
    public $sizes;
}