<?php

namespace Combizera\WpMigration\Console;

use Illuminate\Console\Command;
use Combizera\WpMigration\WordPressXmlParser;
use App\Models\Post;

class MigrateWpXmlCommand extends Command
{
    protected $signature = 'wp:migrate {file}';
    protected $description = 'Migrate posts from a WordPress XML to the database.';

    public function handle(): void
    {
        $file = $this->argument('file');

        try {
            $parser = new WordPressXmlParser($file);
            $posts = $parser->getPosts();

            foreach ($posts as $post) {
                Post::query()
                    ->create([
                        'title' => $post->title,
                        'slug' => \Str::slug($post->title),
                        'content' => $post->content,
                        //'published_at' => $post->publishedAt,
                ]);
            }

            $this->info(count($posts) . ' posts migrate with success!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
