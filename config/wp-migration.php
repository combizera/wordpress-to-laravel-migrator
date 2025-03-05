<?php

use App\Models\Post;
use App\Models\Category;

return [

    /*
    |--------------------------------------------------------------------------
    | Post Model
    |--------------------------------------------------------------------------
    |
    | Define which model should be used for storing posts.
    | This allows customization in case the user has a different model.
    |
    */
    'post_model' => Post::class,

    /*
    |--------------------------------------------------------------------------
    | Category Model
    |--------------------------------------------------------------------------
    |
    | Define which model should be used for storing categories.
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
];
