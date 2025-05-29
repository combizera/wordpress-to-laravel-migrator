<?php

namespace Combizera\WpMigration\Console;

use App\Models\Category;
use Combizera\WpMigration\WpXmlParser;
use Illuminate\Console\Command;

class MigrateWpXmlCommand extends Command
{
    protected $signature = 'wp:migrate {file}';

    protected $description = 'Migrate posts from a WordPress XML to the database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $file = $this->argument('file');

        try {
            $parser = new WpXmlParser($file);
            $posts = $parser->getPosts();

            $totalItems = count($parser->xml->channel->item ?? []);
            $totalPosts = count($posts);
            $totalPublished = count(array_filter($posts, fn ($post) => $post->isPublished === 1));
            $totalUnpublished = $totalPosts - $totalPublished;

            $this->info("📄 XML file: {$file}");
            $this->info("🔍 Total items: {$totalItems}");
            $this->info("📝 Total posts: {$totalPosts}");
            $this->info("📢 Published: {$totalPublished} | ⏳ Draft: {$totalUnpublished}");
            $this->info('📂 Loading categories...');

            $postModel = app(config('wp-migration.post_model'));

            $bar = $this->output->createProgressBar($totalPosts);
            $bar->start();

            foreach ($posts as $post) {
                $categoryId = ! empty($post->categories) ? $post->categories[0] : $this->getDefaultCategoryId();
                $slug = $parser->parseSlug($post->title, $categoryId);

                $postModel::query()->create([
                    'user_id' => $post->userId,
                    'category_id' => $categoryId,
                    config('wp-migration.post_columns.title') => $post->title,
                    config('wp-migration.post_columns.slug') => $slug,
                    config('wp-migration.post_columns.content') => $post->content,
                    config('wp-migration.post_columns.is_published') => $post->isPublished,
                    'created_at' => $post->createdAt,
                    'updated_at' => $post->updatedAt,
                ]);

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("🎉 {$totalPosts} posts successfully migrated!");
        } catch (\Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());
        }
    }

    /**
     * Get the default category ID
     */
    private function getDefaultCategoryId(): int
    {
        $defaultCategory = Category::query()
            ->firstOrCreate(
                ['slug' => 'uncategorized'],
                ['name' => 'Uncategorized']
            );

        return $defaultCategory->id;
    }
}
