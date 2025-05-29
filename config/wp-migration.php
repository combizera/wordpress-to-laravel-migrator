<?php

use App\Models\Post;
use App\Models\Category;

return [

    /*
    |--------------------------------------------------------------------------
    | Post Model Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can customize everything related to the Post model, such as
    | column names for title, slug, content, and is_published.
    |
    */
    'post_model' => Post::class,
    'post_columns' => [
        'title' => 'title',
        'slug' => 'slug',
        'content' => 'content',
        'is_published' => 'is_published',
    ],

    /*
    |--------------------------------------------------------------------------
    | Category Model Configuration
    |--------------------------------------------------------------------------
    |
    | Defines which model should be used for storing categories.
    | The default is 'App\Models\Category', but it can be changed if necessary.
    |
    */
    'category_model' => Category::class,

    /*
    |--------------------------------------------------------------------------
    | Default User ID
    |--------------------------------------------------------------------------
    |
    | Defines the default user who will be assigned as the author of the posts.
    | If not provided, it will default to user ID 1.
    |
    */
    'default_user_id' => 1,

    /*
    |--------------------------------------------------------------------------
    | Import Images Configuration
    |--------------------------------------------------------------------------
    |
    | Controls whether images from WordPress posts should be imported and converted.
    | If set to false, images will be kept in their original format.
    |
    */
    'import_images' => true,
];
