<?php

add_filter('acf/settings/rest_api_format', function () {
  return 'standard';
});

function my_acf_google_map_api($api)
{
  $api['key'] = 'AIzaSyCy9rbU4Ig0hOSkxafTyavI8eP_B9pvN6w';
  return $api;
}

add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');

function coffee_shop_setup()
{
  add_theme_support('post-thumbnails');
}

add_action('after_setup_theme', 'coffee_shop_setup');

function coffee_shop_api_init()
{
  register_rest_field(
    array('page', 'post', 'product'),
    'featured_images',
    array('get_callback' => 'get_featured_image')
  );

  register_rest_field(
    array('post'),
    'category_details',
    array('get_callback' => 'get_post_categories')
  );

  register_rest_field(
    array('page'),
    'gallery',
    array('get_callback' => 'get_gallery_images')
  );
}

add_action('rest_api_init', 'coffee_shop_api_init');

function get_featured_image($post)
{
  if (!$post['featured_media']) {
    return false;
  }

  $image_sizes = get_intermediate_image_sizes();

  $images = array();

  foreach ($image_sizes as $size) {
    if ($size === '2048x2048') continue;

    $image = wp_get_attachment_image_src($post['featured_media'], $size);

    $images[$size === '1536x1536' ? 'full' : $size] = array(
      'url' => $image[0],
      'width' => $image[1],
      'height' => $image[2]
    );
  }

  return $images;
}

function get_post_categories($post)
{
  return array_map(
    function ($category_id) {
      $cat = get_category($category_id, 'ARRAY_A');
      return [
        'id' => $cat['term_id'],
        'name' => $cat['name'],
        'slug' => $cat['slug'],
      ];
    },
    $post['categories']
  );
}

function get_gallery_images($post)
{
  if ($post['slug'] !== 'galeria') return [];

  $gallery = get_post_gallery($post['id'], false);
  $gallery_ids = array_map('intval', explode(',', $gallery['ids']));

  return array_map(
    function ($image_id) {
      $large_image = wp_get_attachment_image_src($image_id, 'large');
      $full_image = wp_get_attachment_image_src($image_id, 'full');

      return [
        'large' => [
          'url' => $large_image[0],
          'width' => $large_image[1],
          'height' => $large_image[2],
        ],

        'full' => [
          'url' => $full_image[0],
          'width' => $full_image[1],
          'height' => $full_image[2],
        ],
      ];
    },
    $gallery_ids
  );
}
