<?php
namespace Manychois\Wpx\Tests\Integration;

use Manychois\Wpx\Utility;

class UtilityGetTestTest extends IntegrationTestCase
{
	private $minimizeHeadArgs;
    private $postId;
    private $attachmentId1;
    private $attachmentId2;

	public function setUp()
	{
        parent::setUp();

		$this->set_permalink_structure('/%postname%/');
        $this->postId = $this->factory->post->create(['post_title' => 'Gallery Post', 'post_status' => 'publish', 'post_name' => 'gallery-post']);

        $attachmentId = $this->factory->post->create([
            'post_parent' => $this->postId,
            'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image/png',
            'post_title' => 'Image One', 'post_name' => 'img-one', 'post_excerpt' => 'Image One Caption', 'post_content' => 'Image One Description'
        ]);
        add_post_meta($attachmentId, '_wp_attached_file', '2018/09/img-1.png');
        add_post_meta($attachmentId, '_wp_attachment_image_alt', 'Image One Alt Text');
        add_post_meta($attachmentId, '_wp_attachment_metadata', [
            'width' => 1024, 'height' => 768, 'file' => '2018/09/img-1.png',
            'sizes' => [
                'thumbnail' => ['file' => 'img-1-120x80.png', 'width' => 120, 'height' => 80, 'mime-type' => 'image/png'],
                'theme-thumbnail' => ['file' => 'img-1-480x360.png', 'width' => 480, 'height' => 360, 'mime-type' => 'image/png']
            ],
            'image_meta' => []
        ]);
        $this->attachmentId1 = $attachmentId;

        $attachmentId = $this->factory->post->create([
            'post_parent' => $this->postId,
            'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image/jpeg',
            'post_title' => 'Image Two', 'post_name' => 'img-two', 'post_excerpt' => 'Image Two Caption', 'post_content' => 'Image Two Description'
        ]);
        add_post_meta($attachmentId, '_wp_attached_file', '2018/09/img-2.jpg');
        add_post_meta($attachmentId, '_wp_attachment_image_alt', 'Image Two Alt Text');
        add_post_meta($attachmentId, '_wp_attachment_metadata', [
            'width' => 1600, 'height' => 1200, 'file' => '2018/09/img-2.jpg',
            'sizes' => [
                'thumbnail' => ['file' => 'img-2-120x80.jpg', 'width' => 120, 'height' => 80, 'mime-type' => 'image/jpeg'],
                'theme-thumbnail' => ['file' => 'img-2-480x360.jpg', 'width' => 480, 'height' => 360, 'mime-type' => 'image/jpeg']
            ],
            'image_meta' => []
        ]);
        $this->attachmentId2 = $attachmentId;
	}

    public function test_default()
    {
        $id1 = $this->attachmentId1;
        $id2 = $this->attachmentId2;

        $attrs = [
            'id' => $this->postId
        ];

        $u = new Utility($this->wp());
        $gallery = $u->getGallery($attrs, '');

        $this->assertSame('ASC', $gallery->attrs['order']);
        $this->assertSame('menu_order ID', $gallery->attrs['orderby']);
        $this->assertSame(3, $gallery->attrs['columns']);
        $this->assertSame($this->postId, $gallery->attrs['id']);
        $this->assertSame('thumbnail', $gallery->attrs['size']);
        $this->assertSame(2, count($gallery->items));

        $gi = $gallery->items[0];
        $this->assertSame($id1, $gi->id);
        $this->assertSame('http://example.org/gallery-post/img-one/', $gi->postUrl);
        $this->assertSame('http://example.org/wp-content/uploads/2018/09/img-1.png', $gi->url);
        $this->assertSame(1024, $gi->width);
        $this->assertSame(768, $gi->height);
        $this->assertSame('Image One', $gi->title);
        $this->assertSame('Image One Alt Text', $gi->alt);
        $this->assertSame('Image One Description', $gi->description);
        $this->assertSame('Image One Caption', $gi->caption);

        $gs = $gi->sizes['thumbnail'];
        $this->assertSame(120, $gs['width']);
        $this->assertSame(80, $gs['height']);
        $this->assertSame('image/png', $gs['mime-type']);
        $this->assertSame('http://example.org/wp-content/uploads/2018/09/img-1-120x80.png', $gs['url']);
        $gs = $gi->sizes['theme-thumbnail'];
        $this->assertSame(480, $gs['width']);
        $this->assertSame(360, $gs['height']);
        $this->assertSame('image/png', $gs['mime-type']);
        $this->assertSame('http://example.org/wp-content/uploads/2018/09/img-1-480x360.png', $gs['url']);

        $this->assertSame($id2, $gallery->items[1]->id);
    }

    public function test_include()
    {
        $id1 = $this->attachmentId1;
        $id2 = $this->attachmentId2;

        $attrs = [
            'size' => 'theme-thumbnail',
            'ids' => "$id2,$id1",
            'orderby' => 'post__in',
            'include' => "$id2,$id1"
        ];

        $u = new Utility($this->wp());
        $gallery = $u->getGallery($attrs, '');

        $this->assertSame('ASC', $gallery->attrs['order']);
        $this->assertSame('post__in', $gallery->attrs['orderby']);
        $this->assertSame(3, $gallery->attrs['columns']);
        $this->assertSame('theme-thumbnail', $gallery->attrs['size']);
        $this->assertSame("$id2,$id1", $gallery->attrs['include']);
        $this->assertSame(2, count($gallery->items));

        $gi = $gallery->items[0];
        $this->assertSame($id2, $gi->id);
        $this->assertSame('http://example.org/gallery-post/img-two/', $gi->postUrl);
        $this->assertSame('http://example.org/wp-content/uploads/2018/09/img-2.jpg', $gi->url);
        $this->assertSame(1600, $gi->width);
        $this->assertSame(1200, $gi->height);
        $this->assertSame('Image Two', $gi->title);
        $this->assertSame('Image Two Alt Text', $gi->alt);
        $this->assertSame('Image Two Description', $gi->description);
        $this->assertSame('Image Two Caption', $gi->caption);

        $gs = $gi->sizes['thumbnail'];
        $this->assertSame(120, $gs['width']);
        $this->assertSame(80, $gs['height']);
        $this->assertSame('image/jpeg', $gs['mime-type']);
        $this->assertSame('http://example.org/wp-content/uploads/2018/09/img-2-120x80.jpg', $gs['url']);
        $gs = $gi->sizes['theme-thumbnail'];
        $this->assertSame(480, $gs['width']);
        $this->assertSame(360, $gs['height']);
        $this->assertSame('image/jpeg', $gs['mime-type']);
        $this->assertSame('http://example.org/wp-content/uploads/2018/09/img-2-480x360.jpg', $gs['url']);

        $this->assertSame($id1, $gallery->items[1]->id);
    }

    public function test_exclude()
    {
        $id1 = $this->attachmentId1;
        $id2 = $this->attachmentId2;

        $attrs = [
            'id' => $this->postId,
            'exclude' => "$id2"
        ];

        $u = new Utility($this->wp());
        $gallery = $u->getGallery($attrs, '');

        $this->assertSame('ASC', $gallery->attrs['order']);
        $this->assertSame('menu_order ID', $gallery->attrs['orderby']);
        $this->assertSame(3, $gallery->attrs['columns']);
        $this->assertSame($this->postId, $gallery->attrs['id']);
        $this->assertSame('thumbnail', $gallery->attrs['size']);
        $this->assertSame(1, count($gallery->items));

        $gi = $gallery->items[0];
        $this->assertSame($id1, $gi->id);
        $this->assertSame('http://example.org/gallery-post/img-one/', $gi->postUrl);
        $this->assertSame('http://example.org/wp-content/uploads/2018/09/img-1.png', $gi->url);
        $this->assertSame(1024, $gi->width);
        $this->assertSame(768, $gi->height);
        $this->assertSame('Image One', $gi->title);
        $this->assertSame('Image One Alt Text', $gi->alt);
        $this->assertSame('Image One Description', $gi->description);
        $this->assertSame('Image One Caption', $gi->caption);

        $gs = $gi->sizes['thumbnail'];
        $this->assertSame(120, $gs['width']);
        $this->assertSame(80, $gs['height']);
        $this->assertSame('image/png', $gs['mime-type']);
        $this->assertSame('http://example.org/wp-content/uploads/2018/09/img-1-120x80.png', $gs['url']);
        $gs = $gi->sizes['theme-thumbnail'];
        $this->assertSame(480, $gs['width']);
        $this->assertSame(360, $gs['height']);
        $this->assertSame('image/png', $gs['mime-type']);
        $this->assertSame('http://example.org/wp-content/uploads/2018/09/img-1-480x360.png', $gs['url']);
    }
}
