<?php

require_once 'vendor/autoload.php';

//filectime

use League\CommonMark\CommonMarkConverter;

class generate {
    private $blogPosts = [];

    private $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'allow_unsafe_links' => false,
        ]);
    }

    public function start() {
        // gather all files and parse contents
        $files = glob('blogposts/*.md');
        foreach($files as $file) {
            if($content = file_get_contents($file)) {
                $baseName = basename($file, '.md');
                $html = $this->converter->convert($content)->getContent();

                $this->generateBlogPost($baseName, $html);


                if(!file_exists('../docs/blogposts/images/' . $baseName . '.jpg')) {
                    $imageBaseUrl = 'https://source.unsplash.com/random/185x185/?code,programming';
                    $imageHeaders = @get_headers($imageBaseUrl, 1);
                    $image = file_get_contents($imageHeaders['Location']);
                    file_put_contents('../docs/blogposts/images/' . $baseName . '.jpg', $image);
                }
                // get title + first paragraph for blogposts array
                preg_match('/^(.*?<\/p>)/ms', $html, $matches);
                $this->blogPosts[] = '<div class="row"><div class="col s3">
					        <img src="blogposts/images/' . $baseName . '.jpg" alt="' . $baseName . '">
					    </div><div class="col s9">' . $matches[1] . '<a href="blogposts/' . $baseName . '.html">Read more →</a></div></div>';
            }
        }

        $this->generateIndex($this->blogPosts);
    }

    private function generateBlogPost($baseName, $html) {
        $html = preg_replace('/^.+\n/', '', $html);
        $title = ucfirst(str_replace('_', ' ', $baseName));
        $blogPostSkeleton = file_get_contents('layout/blogpost.html');
        $finalContents = str_replace(['{{contents}}', '{{title}}'], [$html, $title], $blogPostSkeleton);
        file_put_contents('../docs/blogposts/' . $baseName . '.html', $finalContents);
    }

    private function generateIndex($blogPosts) {
        $indexSkeleton = file_get_contents('layout/index.html');
        $finalContents = str_replace('{{data}}', addslashes(json_encode($blogPosts)), $indexSkeleton);
        file_put_contents('../docs/index.html', $finalContents);
    }
}

$generate = new generate;
$generate->start();