<?php

require_once 'vendor/autoload.php';

//filectime

use League\CommonMark\CommonMarkConverter;
use Icamys\SitemapGenerator\SitemapGenerator;
use League\CommonMark\Exception\CommonMarkException;

/**
 *
 */
class generate
{
    /**
     * @var array
     */
    private array $blogPosts = [];

    /**
     * @var CommonMarkConverter
     */
    private CommonMarkConverter $converter;

    /**
     * @var SitemapGenerator
     */
    private SitemapGenerator $sitemapGenerator;

    /**
     *
     */
    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'allow_unsafe_links' => false,
        ]);

        $this->sitemapGenerator = new SitemapGenerator('https://toonvd.github.io', '../docs');
    }

    /**
     * @return void
     * @throws CommonMarkException
     */
    public function start(): void
    {
        // gather all files and parse contents
        $files = glob('blogposts/*.md');
        $this->addUrlToSitemap('/');
        foreach ($files as $file) {
            if ($content = file_get_contents($file)) {
                $baseName = basename($file, '.md');
                $html = $this->converter->convert($content)->getContent();

                $this->addUrlToSitemap('/blogposts/' . $baseName . '.html');

                if (!file_exists('../docs/blogposts/images/' . $baseName . '.jpg')) {
                    $imageBaseUrl = 'https://source.unsplash.com/random/185x185/?code,programming';
                    $imageHeaders = @get_headers($imageBaseUrl, 1);
                    $image = file_get_contents($imageHeaders['Location']);
                    file_put_contents('../docs/blogposts/images/' . $baseName . '.jpg', $image);
                }

                $this->generateBlogPost($baseName, $html);

                // get title + first paragraph for blogposts array
                preg_match('/^(.*?<\/p>)/ms', $html, $matches);
                $this->blogPosts[] = '<div class="row"><div class="col s3">
					        <img src="blogposts/images/' . $baseName . '.jpg" alt="' . $baseName . '">
					    </div><div class="col s9">' . $matches[1] . '<a href="blogposts/' . $baseName . '.html">Read more →</a></div></div>';
            }
        }

        $this->generateIndex($this->blogPosts);
        $this->sitemapGenerator->flush();
        $this->sitemapGenerator->finalize();
        $this->sitemapGenerator->updateRobots();
        $this->sitemapGenerator->submitSitemap();
    }

    /**
     * @param string $baseName
     * @param string $html
     * @return void
     */
    private function generateBlogPost(string $baseName, string $html): void
    {
        $html = preg_replace('/^.+\n/', '', $html);
        $title = ucfirst(str_replace('_', ' ', $baseName));
        $blogPostSkeleton = file_get_contents('layout/blogpost.html');
        $finalContents = str_replace(['{{contents}}', '{{title}}', '{{image}}', '{{url}}'],
            [
                $html,
                $title,
                'https://toonvd.github.io/blogposts/images/' . $baseName . '.jpg',
                'https://toonvd.github.io/blogposts/' . $baseName . '.html'
            ],
            $blogPostSkeleton);
        file_put_contents('../docs/blogposts/' . $baseName . '.html', $finalContents);
    }

    /**
     * @param array $blogPosts
     * @return void
     */
    private function generateIndex(array $blogPosts): void
    {
        $indexSkeleton = file_get_contents('layout/index.html');
        $finalContents = str_replace('{{data}}', addslashes(json_encode($blogPosts)), $indexSkeleton);
        file_put_contents('../docs/index.html', $finalContents);
    }

    /**
     * @param string $url
     * @return void
     */
    private function addUrlToSitemap(string $url): void
    {
        $this->sitemapGenerator->addURL($url, new DateTime(), 'always', 0.5);
    }
}

$generate = new generate;
try {
    $generate->start();
} catch (CommonMarkException $e) {
    die($e->getMessage());
}