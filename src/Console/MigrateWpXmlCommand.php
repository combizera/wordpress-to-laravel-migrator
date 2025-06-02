<?php

namespace Combizera\WpMigration\Console;

use App\Models\Category;
use Combizera\WpMigration\WpXmlParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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
            $configPath = config_path('wp-migration.php');
            $importImages = null;

            if (!File::exists($configPath)) {
                $this->warn('⚠️  Configuration file not published.');
                $importImages = $this->confirm('🖼️ Do you want to import and copy images from WordPress to local storage?', true);

                if ($importImages) {
                    $this->info('✅ Images will be downloaded and stored locally.');
                } else {
                    $this->info('ℹ️  Images will remain as external URLs.');
                }
                $this->newLine();
            }

            $parser = new WpXmlParser($file, $importImages);
            $posts = $parser->getPosts();

            $totalItems = count($parser->xml->channel->item ?? []);
            $totalPosts = count($posts);
            $totalPublished = count(array_filter($posts, fn ($post) => $post->isPublished === 1));
            $totalUnpublished = $totalPosts - $totalPublished;

            $this->info("📄 XML file: {$file}");
            $this->info("🔍 Total items: {$totalItems}");
            $this->info("📝 Total posts: {$totalPosts}");
            $this->info("📢 Published: {$totalPublished} | ⏳ Draft: {$totalUnpublished}");

            if ($parser->importImages) {
                $processedImages = $parser->getProcessedImagesCount();
                $processedPdfs = $parser->getProcessedPdfsCount();

                if ($processedImages > 0 || $processedPdfs > 0) {
                    $mediaInfo = [];
                    if ($processedImages > 0) {
                        $mediaInfo[] = "{$processedImages} images";
                    }
                    if ($processedPdfs > 0) {
                        $mediaInfo[] = "{$processedPdfs} PDF files";
                    }
                    $this->info("🖼️" . implode(' and ', $mediaInfo) . " will be downloaded and stored locally.");
                }
            }

            $this->info('📂 Loading categories...');

            $postModel = app(config('wp-migration.post_model'));

            $bar = $this->output->createProgressBar($totalPosts);
            $bar->setFormat('debug');
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
            $this->newLine(2);

            $this->info("🎉 {$totalPosts} posts successfully migrated!");

            if ($parser->importImages) {
                $processedImages = $parser->getProcessedImagesCount();
                $processedPdfs = $parser->getProcessedPdfsCount();

                if ($processedImages === 0 && $processedPdfs === 0) {
                    $this->comment("ℹ️ No images or PDF files found to download.");
                }
            } else {
                $this->comment("ℹ️ Images and files were kept as external URLs.");
            }

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