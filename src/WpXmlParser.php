<?php

namespace Combizera\WpMigration;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class WpXmlParser
{
    public SimpleXMLElement $xml;
    public string $postModel;
    public string $categoryModel;
    public int $defaultUserId;
    public array $postColumns;

    /**
     * Load the XML file and initialize the parser
     *
     * @throws Exception
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception("XML File not found.");
        }

        $this->postModel = config('wp-migration.post_model', 'App\\Models\\Post');
        $this->categoryModel = config('wp-migration.category_model', 'App\\Models\\Category');
        $this->defaultUserId = config('wp-migration.default_user_id', 1);
        $this->postColumns = config('wp-migration.post_columns', [
            'title' => 'title',
            'slug' => 'slug',
            'content' => 'content',
            'is_published' => 'is_published',
        ]);

        $this->xml = simplexml_load_file($filePath, "SimpleXMLElement", LIBXML_NOCDATA);

        if ($this->xml === false) {
            throw new Exception("Error loading XML file.");
        }
    }


    /**
     * Extracts and returns an array of posts from the XML file
     *
     * @throws Exception
     * @return array
     */
    public function getPosts(): array
    {
        if (!isset($this->xml->channel) || !isset($this->xml->channel->item)) {
            throw new Exception("Invalid XML structure. Channel or items not found.");
        }

        $posts = [];

        foreach ($this->xml->channel->item as $item) {
            $post = $this->parsePost($item);
            if ($post) {
                $posts[] = $post;
            }
        }

        return $posts;
    }

    /**
     * Parses a single post item from the XML
     *
     * @param SimpleXMLElement $item
     * @return Post|null
     */
    private function parsePost(SimpleXMLElement $item): ?Post
    {
        $namespaces = $item->getNamespaces(true);

        if (!$this->isValidPost($item, $namespaces)) {
            return null;
        }

        $wpData = $item->children($namespaces['wp']);

        $categories = $this->parseCategories($item);
        $content = isset($namespaces['content'])
            ? $this->parseContent($item->children($namespaces['content'])->encoded)
            : '';

        if (empty(trim($content))) {
            return null;
        }

        return new Post(
            $this->defaultUserId,
            $categories,
            $this->parseTitle($item),
            (string) $item->link,
            $content,
            $this->parsePublish($item),
            isset($item->pubDate) ? $this->parseDate((string) $item->pubDate) : Carbon::now(),
            isset($wpData->post_modified) ? $this->parseDate((string) $wpData->post_modified) : Carbon::now()
        );
    }

    /**
     * Validates if an XML item is a valid WordPress post
     *
     * @param SimpleXMLElement $item
     * @param array $namespaces
     * @return bool
     */
    private function isValidPost(SimpleXMLElement $item, array $namespaces): bool
    {
        if (!isset($namespaces['wp'])) {
            return false;
        }

        $wpData = $item->children($namespaces['wp']);

        return isset($wpData->post_type) && (string) $wpData->post_type === 'post';
    }

    /**
     * Parses the post title safely
     *
     * @param SimpleXMLElement $item
     * @return string
     */
    private function parseTitle(SimpleXMLElement $item): string
    {
        return isset($item->title) ? trim((string) $item->title) : 'Untitled Post';
    }

    /**
     * Generate a unique slug for the post.
     * If the slug already exists in the same category, append a number (ex: slug-1, slug-2).
     *
     * @param string $title
     * @param int $categoryId
     * @return string
     */
    public function parseSlug(string $title, int $categoryId): string
    {
        $baseSlug = Str::slug($title);

        $postModel = app($this->postModel);

        if (!$postModel::query()->where($this->postColumns['slug'], $baseSlug)->exists()) {
            return $baseSlug;
        }

        $counter = 1;
        $newSlug = "{$baseSlug}-{$counter}";

        while ($postModel::query()->where($this->postColumns['slug'], $newSlug)->exists()) {
            $counter++;
            $newSlug = "{$baseSlug}-{$counter}";
        }

        return $newSlug;
    }

    /**
     * Cleans and format the XML content.
     *
     * @param SimpleXMLElement|null $content
     * @return string
     */
    private function parseContent(?SimpleXMLElement $content): string
    {
        if (!$content) {
            return '';
        }

        $content = (string) $content;

        if (config('wp-migration.import_images', true)) {
            $content = $this->processWpContentWithImageConversion($content);
        }

        $content = preg_replace([
            '/<!--(.*?)-->/',
            '/\s*class="wp-[^"]*"/',
            '/\s+/'
        ], ['', '', ' '], $content);

        $content = trim(preg_replace("/[\r\n]+/", "\n", $content));

        return $content;
    }

    /**
     * Process WordPress content and convert image blocks to Trix format
     *
     * @param string $html
     * @return string
     */
    private function processWpContentWithImageConversion(string $html): string
    {
        $pattern = '/<!-- wp:image .*?-->\s*<figure[^>]*>.*?<img[^>]+src="([^"]+)"[^>]*>.*?<\/figure>\s*<!-- \/wp:image -->/s';

        return preg_replace_callback($pattern, function ($match) {
            $originalBlock = $match[0];
            $originalSrc = $match[1];

            try {
                $response = Http::get($originalSrc);
                if (!$response->successful()) {
                    return $originalBlock; 
                }

                $extension = pathinfo($originalSrc, PATHINFO_EXTENSION) ?: 'png';
                $filename = uniqid('img_') . '.' . $extension;
                $storagePath = "images/{$filename}";
                Storage::disk('public')->put($storagePath, $response->body());

                $url = Storage::url($storagePath);
                $fullUrl = asset($url);

                return <<<HTML
                <figure>
                    <img src="{$fullUrl}" alt="Image">
                </figure>
                HTML;

            } catch (\Throwable $e) {
                return $originalBlock; 
            }

        }, $html);
    }

    /**
     * Parse categories from the XML file
     *
     * @param SimpleXMLElement $item
     * @return array<int> List of category IDs
     */
    private function parseCategories(SimpleXMLElement $item): array
    {
        $categories = [];

        $namespaces = $item->getNamespaces(true);
        if (!isset($namespaces['wp'])) {
            return [];
        }

        $wpData = $item->children($namespaces['wp']);
        if (!isset($wpData->post_type) || (string) $wpData->post_type !== 'post') {
            return [];
        }

        foreach ($item->category as $category) {
            $categoryName = trim((string) $category);
            $categoryDomain = (string) $category['domain'];

            if (empty($categoryName) || is_numeric($categoryName)) {
                continue;
            }

            if (!preg_match('/^[\p{L}0-9\s\-]+$/u', $categoryName)) {
                continue;
            }

            if ($categoryDomain !== 'category') {
                continue;
            }

            $existingCategory = Category::query()->firstOrCreate(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName)]
            );

            $categories[] = $existingCategory->id;
        }

        return $categories;
    }

    /**
     * Determine if the post should be published.
     * Returns 1 if the status is "publish", otherwise 0.
     *
     * @param SimpleXMLElement $item
     * @return int
     */
    private function parsePublish(SimpleXMLElement $item): int
    {
        $namespaces = $item->getNamespaces(true);

        if (!isset($namespaces['wp'])) {
            return 0;
        }

        $wpData = $item->children($namespaces['wp']);

        return isset($wpData->status) && (string) $wpData->status === 'publish' ? 1 : 0;
    }

    /**
     * Convert date format to Laravel `created_at` format
     *
     * @param string $date
     * @return string
     */
    private function parseDate(string $date): string
    {
        if (empty($date)) {
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date)) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
            }

            return Carbon::createFromFormat(DATE_RSS, $date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}
