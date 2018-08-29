<?php
namespace Manychois\Wpx;

/**
 * Represents a gallery in WordPress.
 */
class Gallery
{
    /**
     * Attributes defined by the editor.
     * @var array
     */
    public $attrs;
    /**
     * A collection of images in the gallery.
     * @var GalleryItem[]
     */
    public $items = [];
}