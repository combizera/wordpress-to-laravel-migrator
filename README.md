# 📝 WP Migrations

![Packagist Version](https://img.shields.io/packagist/v/combizera/wordpress-to-laravel-migrator)
![Downloads](https://img.shields.io/packagist/dt/combizera/wordpress-to-laravel-migrator)
![License](https://img.shields.io/github/license/combizera/wordpress-to-laravel-migrator)
![PHP Version](https://img.shields.io/packagist/php-v/combizera/wordpress-to-laravel-migrator)

The **WP Migrations** is a Laravel package for migrating WordPress posts to Laravel in a simple and efficient way.

---

## 🚀 Installation

To install via **Composer**, run the following command:

```bash
composer require combizera/wordpress-to-laravel-migrator
```

After installation, publish the configuration file to customize the package behavior:

```bash
php artisan vendor:publish --tag=wp-migration-config
```

---

## 📌 What is Migrated

🔹 **What is migrated?**
- ✅ **WordPress Posts**
- ✅ **Images** (downloaded and converted to Trix format)
- ❌ **WordPress Pages** (only posts are currently supported)

🔹 **Required Requirements:**
- The **Post Model** must be named **`Post`** (or configured in `wp-migration.php`).
- The Model **must contain the following fields** in the database:
    - `category_id` (integer)
    - `title` (string)
    - `slug` (string)
    - `content` (text)
    - `is_published` (boolean)
    - `created_at` (timestamp)
    - `updated_at` (timestamp)
- **Fillable Attributes:** Make sure your `Post` Model has the above fields in `$fillable`.
- Have a **`.xml`** file exported from WordPress with the posts.

🔹 **Image Configuration (Optional):**
- **Image Download:** By default, the package will download images from WordPress and store them locally.
- To disable image download, add `'download_images' => false` in the `wp-migration.php` file.
- **Storage Configuration:**
    - `image_storage_disk`: Storage disk (default: 'public')
    - `image_storage_path`: Base path for images (default: 'images')
    - `image_max_size`: Maximum image size in bytes (default: 5MB)
    - `image_allowed_extensions`: Allowed extensions (default: ['jpg', 'jpeg', 'png', 'gif'])
- **Image Validations:**
    - Validates if the image URL is valid
    - Validates if the image size exceeds the configured limit
    - Validates if the image extension is allowed
    - If any validation fails, keeps the original image URL

---

## 📚 How to Use

1️⃣ **Export XML from WordPress**
To export your posts, go to **Tools > Export** in the WordPress dashboard and generate an `.xml` file.

2️⃣ **Run the migration:**
After installing the package and configuring your `Post` Model, just run the command:

```bash
php artisan wp:migrate database/migration.xml
```

This will process the file and create the posts in your database.

---

## ⚙️ Optional Configuration

The package allows you to customize all available settings by publishing the `wp-migration.php` configuration file:

```php
return [
    // Post Model Configuration
    'post_model' => App\Models\Post::class,         // Post Model
    'post_columns' => [
        'title' => 'title',
        'slug' => 'slug',
        'content' => 'content',
        'is_published' => 'is_published',
    ],

    // Category Model Configuration
    'category_model' => App\Models\Category::class, // Category Model

    // User Configuration
    'default_user_id' => 1,                         // Default user ID for posts

    // Image Configuration
    'download_images' => true,                       // Enable/disable image download
    'image_storage_disk' => 'public',                // Storage disk for images
    'image_storage_path' => 'images',                // Base path for images
    'image_max_size' => 5242880,                     // Maximum image size (5MB)
    'image_allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'], // Allowed image extensions
];
```

---

## 🤝 Contributions

If you want to help **improve** this package:

1. Fork the repository.
2. Create a branch for your feature:
   ```bash
   git checkout -b feat/#issue-number-feature-name
   # Example: git checkout -b feat/#42-upgrade-config-file
   ```
3. Make your changes and commit:
   ```bash
   git commit -m "feat(File): Add new feature"
   ```
4. Submit a **pull request** and wait!

Feel free to **open issues** if you have any questions or suggestions! 🚀

---

## 📝 License

This project is licensed under the **MIT License**. Feel free to use and modify it as needed.

Live **Open Source**! 🎉
