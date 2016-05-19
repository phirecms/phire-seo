<?php

namespace Phire\Seo\Model;

use Phire\Model\AbstractModel;
use Phire\Table;
use Pop\Dom\Child;
use Pop\File\Dir;
use Pop\Http\Client\Curl;

class Seo extends AbstractModel
{

    /**
     * Constructor
     *
     * Instantiate a model object
     *
     * @param  array $data
     * @return self
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $config = Table\Config::findById('seo_config');

        if (isset($config->value) && !empty($config->value) && ($config->value != '')) {
            $cfg        = unserialize($config->value);
            $this->data = array_merge($this->data, $cfg);
        }
    }

    /**
     * Get seo config
     *
     * @return array
     */
    public function getConfig()
    {
        $config = Table\Config::findById('seo_config');

        if (isset($config->value) && !empty($config->value) && ($config->value != '')) {
            $cfg = unserialize($config->value);
        } else {
            $cfg = [
                'tracking' => '',
                'robots'   => 'User-agent: *' . PHP_EOL .
                    'Disallow: ' . BASE_PATH . APP_URI . PHP_EOL .
                    'Disallow: ' . BASE_PATH . APP_PATH . PHP_EOL,
                'meta'   => []
            ];
        }

        return $cfg;
    }

    /**
     * Get seo analysis
     *
     * @return array
     */
    public function getAnalysis()
    {
        $analysis = Table\Config::findById('seo_analysis');

        if (isset($analysis->value) && !empty($analysis->value) && ($analysis->value != '')) {
            $a = unserialize($analysis->value);
        } else {
            $a = [];
        }

        return $a;
    }

    /**
     * Save seo config
     *
     * @param  array $post
     * @return void
     */
    public function saveConfig(array $post)
    {
        $cfg = [
            'tracking' => (!empty($post['seo_tracking'])) ? html_entity_decode($post['seo_tracking'], ENT_QUOTES, 'UTF-8') : '',
            'robots'   => (!empty($post['seo_robots'])) ? html_entity_decode($post['seo_robots'], ENT_QUOTES, 'UTF-8') : '',
            'meta'     => []
        ];

        foreach ($post as $key => $value) {
            if ((substr($key, 0, 10) == 'meta_name_') && !empty($value) && ($value != '')) {
                $id = substr($key, 10);
                if (!empty($post['meta_content_' . $id]) && ($post['meta_content_' . $id] != '')) {
                    $cfg['meta'][] = [
                        'name'    => html_entity_decode($value, ENT_QUOTES, 'UTF-8'),
                        'content' => html_entity_decode($post['meta_content_' . $id], ENT_QUOTES, 'UTF-8')
                    ];
                }
            }
        }

        $config = Table\Config::findById('seo_config');
        $config->value = serialize($cfg);
        $config->save();
    }

    /**
     * Process and save seo config
     *
     * @param  array $exclude
     * @return void
     */
    public function saveAnalysis(array $exclude = [])
    {
        $config = Table\Config::findById('seo_config');
        $cfg    = (isset($config->value) && !empty($config->value) && ($config->value != '')) ?
            unserialize($config->value) : [];

        $analysis = [
            'tracking'    => false,
            'sitemap'     => false,
            'robots'      => false,
            'caching'     => false,
            'site-verify' => false,
            'content' =>  [
                'good' => [],
                'bad'  => []
            ]
        ];

        if (!empty($cfg['tracking']) && ($cfg['tracking'] != '')) {
            $analysis['tracking'] = true;
        }

        $sitemap = \Phire\Table\Modules::findBy(['name' => 'phire-sitemap']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/sitemap.xml') ||
            (isset($sitemap->id) && ($sitemap->active))) {
            $analysis['sitemap'] = true;
        }

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/robots.txt') ||
            (isset($cfg['robots']) && !empty($cfg['robots']) && ($cfg['robots'] != ''))) {
            $analysis['robots'] = true;
        }

        $cacheDetect = false;
        $curl        = new Curl('http://' . $_SERVER['HTTP_HOST'] . BASE_PATH);
        if (($curl->getCode() == 200) &&
            (null !== $curl->getHeader('Cache-Control')) && (null !== $curl->getHeader('Expires')) &&
            (null !== $curl->getHeader('Last-Modified')) && (null !== $curl->getHeader('Etag'))) {
            $cacheDetect = true;
        }

        $cache = \Phire\Table\Modules::findBy(['name' => 'phire-cache']);
        if (($cacheDetect) || (isset($cache->id) && ($cache->active))) {
            $analysis['caching'] = true;
        }

        $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH, ['relative' => true, 'filesOnly' => true]);

        $googleFileDetect = false;
        foreach ($dir->getFiles() as $file) {
            if ((substr($file, 0, 6) == 'google') &&
                (strpos(file_get_contents($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/' . $file), 'google-site-verification') !== false)) {
                $googleFileDetect = true;
            }
        }

        $googleMetaDetect = false;
        foreach ($cfg['meta'] as $meta) {
            if ($meta['name'] == 'google-site-verification') {
                $googleMetaDetect = true;
            }
        }

        if (($googleFileDetect) || ($googleMetaDetect)) {
            $analysis['site-verify'] = true;
        } else if (function_exists('dns_get_record') && ($_SERVER['HTTP_HOST'] != 'localhost')) {
            $dns = dns_get_record($_SERVER['HTTP_HOST'], DNS_TXT);
            if (count($dns) > 0) {
                foreach ($dns as $record) {
                    if (isset($record['txt']) && (strpos($record['txt'], 'google-site-verification') !== false)) {
                        $analysis['site-verify'] = true;
                    }
                }
            }
        }

        $fields = [
            'seo_title'   => '',
            'description' => '',
            'keywords'    => ''
        ];

        $seoTitle    = \Phire\Fields\Table\Fields::findBy(['name' => 'seo_title']);
        $description = \Phire\Fields\Table\Fields::findBy(['name' => 'description']);
        $keywords    = \Phire\Fields\Table\Fields::findBy(['name' => 'keywords']);

        if (isset($seoTitle->id)) {
            $fields['seo_title'] = $seoTitle->id;
        }
        if (isset($description->id)) {
            $fields['description'] = $description->id;
        }
        if (isset($keywords->id)) {
            $fields['keywords'] = $keywords->id;
        }

        $content = \Phire\Content\Table\Content::findAll();
        foreach ($content->rows() as $c) {
            if (!in_array($c->type_id, $exclude)) {
                $seoTitle = '';
                $metaDesc = '';
                $metaKeys = '';
                if ($fields['seo_title'] != '') {
                    $seoTitleField = \Phire\Fields\Table\FieldValues::findById([$fields['seo_title'], $c->id, "Phire\\Content\\Model\\Content"]);
                    if (isset($seoTitleField->field_id)) {
                        $seoTitle = json_decode($seoTitleField->value);
                    }
                }
                if ($fields['description'] != '') {
                    $descriptionField = \Phire\Fields\Table\FieldValues::findById([$fields['description'], $c->id, "Phire\\Content\\Model\\Content"]);
                    if (isset($descriptionField->field_id)) {
                        $metaDesc = json_decode($descriptionField->value);
                    }
                }
                if ($fields['keywords'] != '') {
                    $keywordsField = \Phire\Fields\Table\FieldValues::findById([$fields['keywords'], $c->id, "Phire\\Content\\Model\\Content"]);
                    if (isset($keywordsField->field_id)) {
                        $metaKeys = json_decode($keywordsField->value);
                    }
                }

                if ((strlen($seoTitle) > 0) && (strlen($seoTitle) <= 60) &&
                    (strlen($metaDesc) > 0) && (strlen($metaDesc) <= 160) &&
                    (strlen($metaKeys) > 0) && (strlen($metaKeys) <= 255)) {
                    $analysis['content']['good'][$c->id] = [
                        'title' => $c->title,
                        'uri'   => $c->uri
                    ];
                } else {
                    $analysis['content']['bad'][$c->id] = [
                        'title'   => $c->title,
                        'type_id' => $c->type_id,
                        'uri'     => $c->uri,
                        'issues'  => []
                    ];

                    if (strlen($seoTitle) == 0) {
                        $analysis['content']['bad'][$c->id]['issues'][] = 'No SEO Title';
                    } else if (strlen($seoTitle) > 60) {
                        $analysis['content']['bad'][$c->id]['issues'][] = 'SEO Title is too long';
                    }

                    if (strlen($metaDesc) == 0) {
                        $analysis['content']['bad'][$c->id]['issues'][] = 'No description meta tag';
                    } else if (strlen($metaDesc) > 160) {
                        $analysis['content']['bad'][$c->id]['issues'][] = 'Description meta tag is too long';
                    }

                    if (strlen($metaKeys) == 0) {
                        $analysis['content']['bad'][$c->id]['issues'][] = 'No keywords meta tag';
                    } else if (strlen($metaKeys) > 255) {
                        $analysis['content']['bad'][$c->id]['issues'][] = 'Keywords meta tag is too long';
                    }
                }
            }
        }

        $config = Table\Config::findById('seo_analysis');
        $config->value = serialize($analysis);
        $config->save();
    }

    /**
     * Build social nav
     *
     * @param  string $description
     * @param  string $keywords
     * @return mixed
     */
    public function buildMetaTags($description = null, $keywords = null)
    {
        $metas   = null;
        $hasDesc = false;
        $hasKeys = false;
        $config  = Table\Config::findById('seo_config');

        if (isset($config->value) && !empty($config->value) && ($config->value != '')) {
            $cfg = unserialize($config->value);
            if (count($cfg['meta']) > 0) {
                foreach ($cfg['meta'] as $meta) {
                    $content = $meta['content'];
                    if ($meta['name'] == 'description') {
                        $hasDesc = true;
                        if ((null !== $description) && ($description != '')) {
                            $content = $description;
                        }
                    }
                    if ($meta['name'] == 'keywords') {
                        $hasKeys = true;
                        if ((null !== $keywords) && ($keywords != '')) {
                            $content = $keywords;
                        }
                    }

                    $m = new Child('meta');
                    $m->setAttributes([
                        'name'    => $meta['name'],
                        'content' => htmlentities($content, ENT_QUOTES, 'UTF-8')
                    ]);
                    $metas .= '    ' . (string)$m;
                }

                if ((!$hasDesc) && (null !== $description) && ($description != '')) {
                    $m = new Child('meta');
                    $m->setAttributes([
                        'name'    => 'description',
                        'content' => htmlentities($description, ENT_QUOTES, 'UTF-8')
                    ]);
                    $metas .= '    ' . (string)$m;
                }

                if ((!$hasKeys) && (null !== $keywords) && ($keywords != '')) {
                    $m = new Child('meta');
                    $m->setAttributes([
                        'name'    => 'keywords',
                        'content' => htmlentities($keywords, ENT_QUOTES, 'UTF-8')
                    ]);
                    $metas .= '    ' . (string)$m;
                }

            }
        }

        if (null === $metas) {
            if ((null !== $description) && ($description != '')) {
                $m = new Child('meta');
                $m->setAttributes([
                    'name'    => 'description',
                    'content' => htmlentities($description, ENT_QUOTES, 'UTF-8')
                ]);
                $metas .= '    ' . (string)$m;
            }
            if ((null !== $keywords) && ($keywords != '')) {
                $m = new Child('meta');
                $m->setAttributes([
                    'name'    => 'keywords',
                    'content' => htmlentities($keywords, ENT_QUOTES, 'UTF-8')
                ]);
                $metas .= '    ' . (string)$m;
            }
        }

        return $metas;
    }

}
