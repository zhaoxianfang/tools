<?php

namespace zxf\Tools;

use Exception;
use SimpleXMLElement;

/**
 * Sitemap 站点地图生成器
 */

/**
 * // 创建 SitemapGenerator 实例
 * $sitemapDir       = __DIR__; // 存储 Sitemap 的目录
 * $baseUrl          = 'https://www.example.com'; // 网站的基础 URL
 * $sitemapGenerator = new SiteMapGenerator($sitemapDir, $baseUrl, 'sitemaps');
 *
 * // 设置自定义 XML 头部（可选）
 * $sitemapGenerator->setHeader('<?xml version="1.0" encoding="UTF-8"?>');
 *
 * // 添加 URL
 * $sitemapGenerator->addUrl('/page1', '2024-09-24', 'daily', 0.8); // 页面 1
 * $sitemapGenerator->addUrl('/page2', '2024-09-23', 'weekly', 0.5); // 页面 2
 * $sitemapGenerator->addUrl('/page3', null, 'monthly', 0.3); // 页面 3（没有最后修改时间）
 *
 * // 动态添加 URL
 * $sitemapGenerator->addDynamicUrls(function ($generator) {
 * for ($i = 4; $i <= 60; $i++) {
 * $generator->addUrl("/page$i", date('Y-m-d'), 'weekly', 0.5);
 * }
 * });
 *
 * // 添加新闻条目（可选）
 * $sitemapGenerator->addNewsEntry('/news1', '2024-09-24', '新闻标题1', '关键词1, 关键词2');
 * $sitemapGenerator->addNewsEntry('/news2', '2024-09-24', '新闻标题2', '关键词3, 关键词4');
 *
 * // 为指定 URL 添加图像信息（可选）
 * $sitemapGenerator->addImageToUrl('/page1', 'https://www.example.com/images/image1.jpg', '图像标题1', '图像说明1');
 *
 * // 为指定 URL 添加视频信息（可选）
 * $sitemapGenerator->addVideoToUrl('/page2', 'https://www.example.com/videos/video1.mp4', '视频标题1', '视频描述1',
 * 'https://www.example.com/videos/video1_thumbnail.jpg', 120);
 *
 * // 生成 Sitemap
 * $sitemapGenerator->generateFile();
 *
 *
 * // 打印成功信息
 * echo "Sitemap 生成成功，文件存储在: $sitemapDir\n";
 */
class SiteMapGenerator
{
    private array $urls = []; // 存储 URL 的数组

    private string $sitemapDir; // 存储 Sitemap 文件的基础目录

    private string $mapsDirName; // 存储 Sitemap 索引文件的目录名称

    private int $maxUrls; // 每个 Sitemap 文件的最大 URL 数量

    private int $maxFileSize; // 每个 Sitemap 文件的最大字节大小

    private string $baseUrl; // 基础 URL，用于构建完整 URL

    private ?string $header = null; // 自定义 XML 头部

    private ?string $footer = null; // 自定义 XML 尾部

    private int $sitemapCount = 0; // 当前生成的 Sitemap 文件数量

    private string $dateFolder; // 日期文件夹，用于存储 Sitemap

    private string $indexFilePath; // Sitemap 索引文件路径

    /**
     * SitemapGenerator 构造函数
     *
     * @param  string  $baseDir  存储 Sitemap 文件的基础目录 eg: __DIR__
     * @param  string  $mapsDir  存储 Sitemap 索引文件的文件夹名称 eg: 'sitemaps'
     * @param  string  $baseUrl  基础 URL，用于构建完整 URL eg:'https://www.example.com'
     * @param  int  $maxUrls  每个 Sitemap 文件的最大 URL 数量，默认为 50000
     * @param  int  $maxFileSize  每个 Sitemap 文件的最大字节大小，默认为 10485760 (10MB)
     */
    public function __construct(string $baseDir, string $baseUrl, string $mapsDir = 'sitemaps', int $maxUrls = 50000, int $maxFileSize = 10485760)
    {
        $this->baseUrl = rtrim($baseUrl, '/').'/'; // 确保基础 URL 以 '/' 结尾
        $this->maxUrls = $maxUrls; // 设置最大 URL 数量
        $this->maxFileSize = $maxFileSize; // 设置最大文件大小
        $this->mapsDirName = $mapsDir; // 存储 Sitemap 索引文件的文件夹名称

        // 创建按日期命名的文件夹
        $this->dateFolder = date('Y-m-d'); // 使用当前日期
        $this->sitemapDir = rtrim($baseDir, '/').'/'.$this->mapsDirName.'/'.$this->dateFolder.'/'; // 生成完整的路径

        // 创建 Sitemap 目录（如果不存在）
        if (! is_dir($this->sitemapDir)) {
            mkdir($this->sitemapDir, 0755, true); // 创建目录并设置权限
        }

        // 设置索引文件路径
        $this->indexFilePath = rtrim($baseDir, '/').'/sitemap.xml'; // 索引文件路径
    }

    /**
     * 设置自定义 XML 头部
     *
     * @param  string  $header  自定义的 XML 头部内容 eg: '<?xml version="1.0" encoding="UTF-8"?>'
     */
    public function setHeader(string $header): void
    {
        $this->header = $header; // 设置自定义 XML 头部
    }

    /**
     * 设置自定义 XML 尾部
     *
     * @param  string  $footer  自定义的 XML 尾部内容
     */
    public function setFooter(string $footer): void
    {
        $this->footer = $footer; // 设置自定义 XML 尾部
    }

    /**
     * 添加单个 URL 到 Sitemap
     *
     * @param  string  $url  URL 地址
     * @param  string|null  $lastModified  最后修改时间，格式为 'Y-m-d'（可选）
     * @param  string  $changeFrequency  更新频率，默认为 'weekly'
     * @param  float  $priority  优先级，默认为 0.5
     * @param  array  $customTags  自定义标签数组（可选）
     *
     *  eg: ->addUrl('/page1', '2024-09-24', 'daily', 0.8); // 页面 1
     *      ->addUrl('/page2', '2024-09-23', 'weekly', 0.5); // 页面 2
     *      ->addUrl('/page3', null, 'monthly', 0.3); // 页面 3（没有最后修改时间）
     *
     * 可选参数说明：
     * - $lastModified: URL 的最后修改日期，用于告诉搜索引擎何时最后更新。
     * - $changeFrequency: 更新频率，可以是 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'。
     * - $priority: 优先级，取值范围为 0.0 至 1.0，默认为 0.5。
     * - $customTags: 可以添加自定义标签以满足特定需求。
     */
    public function addUrl(string $url, ?string $lastModified = null, string $changeFrequency = 'weekly', float $priority = 0.5, array $customTags = []): void
    {
        $url = $this->baseUrl.ltrim($url, '/'); // 构建完整的 URL
        $urlItem = [
            'loc' => $url, // URL
            'lastmod' => $lastModified, // 最后修改时间
            'changefreq' => $changeFrequency, // 更新频率
            'priority' => $priority, // 优先级
        ];
        if (! empty($customTags)) {
            $urlItem = array_merge($customTags, $urlItem);
        }
        $this->urls[] = $urlItem;

        // 检查是否达到最大 URL 数量或文件大小
        if (count($this->urls) >= $this->maxUrls || $this->getCurrentFileSize() >= $this->maxFileSize) {
            $this->generateSitemap(); // 生成 Sitemap 文件
        }
    }

    /**
     * 动态添加 URL
     *
     * @param  callable  $callback  回调函数，用于添加 URL
     */
    public function addDynamicUrls(callable $callback): void
    {
        $callback($this); // 执行回调函数
    }

    /**
     * 获取当前 Sitemap 文件大小
     *
     * @return int 当前 Sitemap 文件的字节大小
     */
    private function getCurrentFileSize(): int
    {
        $lastFile = $this->sitemapDir.'sitemap_'.$this->sitemapCount.'.xml';

        return file_exists($lastFile) ? filesize($lastFile) : 0; // 如果文件存在，则返回大小
    }

    /**
     * 添加新闻条目
     *
     * @param  string  $url  新闻条目的 URL
     * @param  string  $publicationDate  出版日期，格式为 'Y-m-d'
     * @param  string  $title  新闻标题
     * @param  string  $keywords  新闻关键词（以逗号分隔）
     *
     *      eg:   ->addNewsEntry('/news1', '2024-09-24', '新闻标题1', '关键词1, 关键词2');
     *
     * 可选参数说明：
     * - $publicationDate: 新闻出版日期。
     * - $title: 新闻的标题。
     * - $keywords: 新闻相关的关键词，便于搜索引擎索引。
     */
    public function addNewsEntry(string $url, string $publicationDate, string $title, string $keywords): void
    {
        $this->urls[] = [
            'loc' => $this->baseUrl.ltrim($url, '/'), // 新闻条目 URL
            'news' => [
                [
                    'publication_date' => $publicationDate, // 出版日期
                    'title' => $title, // 新闻标题
                    'keywords' => $keywords, // 新闻关键词
                ],
            ],
        ];
    }

    /**
     * 为指定 URL 添加图像信息
     *
     * @param  string  $url  URL 地址
     * @param  string  $imageUrl  图像 URL
     * @param  string  $title  图像标题
     * @param  string  $caption  图像说明
     *
     *    eg :  ->addImageToUrl('/page1', 'https://www.example.com/images/image1.jpg', '图像标题1', '图像说明1');
     */
    public function addImageToUrl(string $url, string $imageUrl, string $title, string $caption): void
    {
        foreach ($this->urls as &$entry) {
            if ($entry['loc'] === $this->baseUrl.ltrim($url, '/')) {
                // 如果找到了对应的 URL，添加图像信息
                $entry['images'][] = [
                    'url' => $imageUrl, // 图像 URL
                    'title' => $title, // 图像标题
                    'caption' => $caption, // 图像说明
                ];
            }
        }
    }

    /**
     * 为指定 URL 添加视频信息
     *
     * @param  string  $url  URL 地址
     * @param  string  $videoUrl  视频 URL
     * @param  string  $title  视频标题
     * @param  string  $description  视频描述
     * @param  string  $thumbnailUrl  缩略图 URL
     * @param  int  $duration  视频时长（单位：秒）
     *
     *                             eg: >addVideoToUrl('/page2', 'https://www.example.com/videos/video1.mp4', '视频标题1',
     *                             '视频描述1', 'https://www.example.com/videos/video1_thumbnail.jpg', 120);
     */
    public function addVideoToUrl(string $url, string $videoUrl, string $title, string $description, string $thumbnailUrl, int $duration): void
    {
        foreach ($this->urls as &$entry) {
            if ($entry['loc'] === $this->baseUrl.ltrim($url, '/')) {
                // 如果找到了对应的 URL，添加视频信息
                $entry['videos'][] = [
                    'url' => $videoUrl, // 视频 URL
                    'title' => $title, // 视频标题
                    'description' => $description, // 视频描述
                    'thumbnail_loc' => $thumbnailUrl, // 缩略图 URL
                    'duration' => $duration, // 视频时长
                ];
            }
        }
    }

    /**
     * 生成 Sitemap 文件
     */
    private function generateSitemap(): void
    {
        if (empty($this->urls)) {
            return; // 如果没有 URL，则返回
        }

        // 定义 Sitemap 文件名
        $sitemapFile = $this->sitemapDir.'sitemap_'.++$this->sitemapCount.'.xml';
        $sitemapContent = $this->generateSitemapXml($this->urls); // 生成 Sitemap XML 内容

        // 写入文件
        $contentToWrite = ''; // 初始化内容

        $contentToWrite .= $sitemapContent; // 添加生成的 XML 内容
        if ($this->footer) {
            $contentToWrite .= trim($this->footer); // 添加 footer（如果存在）
        }

        // 确保没有多余的空白字符
        $contentToWrite = rtrim($contentToWrite)."\n"; // 去除尾部空白

        // 写入文件
        file_put_contents($sitemapFile, $contentToWrite);
        $this->urls = []; // 清空 URL 数组
    }

    /**
     * 生成 Sitemap XML 内容
     *
     * @param  array  $urls  存储 URL 的数组
     * @return string 生成的 XML 内容
     *
     * @throws Exception
     */
    private function generateSitemapXml(array $urls): string
    {
        // 自定义头部
        $customHeader = ! empty($this->header) ? (trim($this->header)."\n") : ('<?xml version="1.0" encoding="UTF-8"?>'."\n");

        // 创建 XML 对象
        $xml = new SimpleXMLElement(
            $customHeader. // 自定义头部
            '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9" '. // 基本的站点地图规范
            'xmlns:video="https://www.google.com/schemas/sitemap-video/1.1" '. // 用于视频扩展
            'xmlns:image="https://www.google.com/schemas/sitemap-image/1.1" '. // 用于图片扩展
            'xmlns:news="https://www.google.com/schemas/sitemap-news/0.9"'. // 用于新闻扩展
            '/>'
        );

        foreach ($urls as $url) {
            $urlEntry = $xml->addChild('url'); // 添加 URL 节点
            $urlEntry->addChild('loc', htmlspecialchars($url['loc'])); // 添加 URL

            if (isset($url['lastmod'])) {
                $urlEntry->addChild('lastmod', $url['lastmod']); // 添加最后修改时间
            }

            // 检查是否存在 changefreq，避免未定义索引的警告
            if (isset($url['changefreq'])) {
                $urlEntry->addChild('changefreq', $url['changefreq']); // 添加更新频率
            }
            if (isset($url['priority'])) {
                $urlEntry->addChild('priority', (string) $url['priority']); // 添加优先级
            }
            // 添加自定义标签
            if (! empty($url['custom'])) {
                foreach ($url['custom'] as $tag => $value) {
                    $urlEntry->addChild($tag, htmlspecialchars($value)); // 添加自定义标签
                }
            }
            // 添加图片信息
            if (isset($url['images'])) {
                foreach ($url['images'] as $image) {
                    // 创建 image:image 节点
                    $imageElement = $urlEntry->addChild('image:image');
                    // 图片 URL
                    ! empty($image['url']) && $imageElement->addChild('image:loc', htmlspecialchars($image['url']));
                    // 如果图片有标题，添加 image:title 标签
                    ! empty($image['title']) && $imageElement->addChild('image:title', htmlspecialchars($image['title']));
                    // 如果图片有描述，添加 image:caption 标签
                    ! empty($image['caption']) && $imageElement->addChild('image:caption', htmlspecialchars($image['caption']));
                    // 位置
                    ! empty($image['geo_location']) && $imageElement->addChild('image:geo_location', htmlspecialchars($image['geo_location']));
                    // 版权
                    ! empty($image['license']) && $imageElement->addChild('image:license', htmlspecialchars($image['license']));
                }
            }
            // 添加视频信息
            if (isset($url['videos'])) {
                foreach ($url['videos'] as $video) {
                    $videoElement = $urlEntry->addChild('video:video');
                    // 视频标题
                    ! empty($video['title']) && $videoElement->addChild('video:title', htmlspecialchars($video['title']));
                    // 视频描述
                    ! empty($video['description']) && $videoElement->addChild('video:description', htmlspecialchars($video['description']));
                    // 视频缩略图
                    ! empty($video['thumbnail_loc']) && $videoElement->addChild('video:thumbnail_loc', htmlspecialchars($video['thumbnail_loc']));
                    // 视频实际地址
                    ! empty($video['url']) && $videoElement->addChild('video:content_loc', htmlspecialchars($video['url']));
                    // 视频时长（秒）
                    ! empty($video['duration']) && $videoElement->addChild('video:duration', $video['duration']);
                    // 视频发布日期
                    ! empty($video['publication_date']) && $videoElement->addChild('video:publication_date', $video['publication_date']);
                    // 添加视频播放器链接
                    ! empty($video['player_loc']) && $videoElement->addChild('video:player_loc', htmlspecialchars($video['player_loc']));
                    // 添加视频过期日期
                    ! empty($video['expiration_date']) && $videoElement->addChild('video:expiration_date', $video['expiration_date']);
                    // 添加视频评分
                    ! empty($video['rating']) && $videoElement->addChild('video:rating', $video['rating']);
                    // 添加观看次数
                    ! empty($video['view_count']) && $videoElement->addChild('video:view_count', $video['view_count']);
                    // 添加是否适合家庭观看标志
                    ! empty($video['family_friendly']) && $videoElement->addChild('video:family_friendly', $video['family_friendly']);
                }
            }

            // 添加新闻数据
            if (isset($url['news'])) {
                foreach ($url['news'] as $news) {
                    $newsElement = $urlEntry->addChild('news:news'); // 添加 news:news 节点
                    if (! empty($news['publication'])) {
                        $publicationElement = $newsElement->addChild('news:publication'); // 添加 news:publication 节点
                        // 添加发布机构名称
                        ! empty($news['publication']['name']) && $publicationElement->addChild('news:name', $news['publication']['name']);
                        // 添加发布语言
                        $publicationElement->addChild('news:language', $news['publication']['language'] ?? 'zh_CN');
                    }
                    // 新闻标题
                    ! empty($news['title']) && $newsElement->addChild('news:title', htmlspecialchars($news['title']));
                    // 发布日期
                    ! empty($news['publication_date']) && $newsElement->addChild('news:publication_date', $news['publication_date']);
                    // 新闻关键词
                    ! empty($news['keywords']) && $newsElement->addChild('news:keywords', htmlspecialchars($news['keywords']));
                    // 新闻分类
                    ! empty($news['genres']) && $newsElement->addChild('news:genres', htmlspecialchars($news['genres']));
                }
            }
        }

        return $xml->asXML(); // 返回生成的 XML
    }

    /**
     * 生成 Sitemap 索引文件
     */
    private function generateIndexFile(): void
    {
        $customHeader = '';
        if ($this->header) {
            $customHeader = trim($this->header)."\n"; // 确保 header 在最上面
        }
        $xml = new SimpleXMLElement($customHeader.'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap-image/1.1"/>'); // 创建 XML 对象

        // 添加每个 Sitemap 文件到索引
        for ($i = 1; $i <= $this->sitemapCount; $i++) {
            $sitemapFile = $this->sitemapDir.'sitemap_'.$i.'.xml';
            $sitemapEntry = $xml->addChild('sitemap');
            $sitemapEntry->addChild('loc', htmlspecialchars($this->baseUrl.$this->mapsDirName.'/'.$this->dateFolder.'/sitemap_'.$i.'.xml')); // 添加 Sitemap 位置
            $sitemapEntry->addChild('lastmod', date('Y-m-d')); // 添加最后修改时间
        }

        // 写入 Sitemap 索引文件
        $xml->asXML($this->indexFilePath);
    }

    /**
     * 生成 Sitemap 文件和索引文件
     *
     * @return void
     */
    public function generateFile()
    {
        // 生成 Sitemap
        $this->generateSitemap();

        // 生成 Sitemap 索引文件
        $this->generateIndexFile();
    }
}
