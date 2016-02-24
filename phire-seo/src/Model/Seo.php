<?php

namespace Phire\Seo\Model;

use Phire\Model\AbstractModel;
use Phire\Table;
use Pop\Dom\Child;

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
                'ga'   => '',
                'meta' => []
            ];
        }

        return $cfg;
    }

    /**
     * Save seo config
     *
     * @param  array $post
     * @return void
     */
    public function save(array $post)
    {
        $cfg = [
            'ga'   => (!empty($post['seo_ga'])) ? html_entity_decode($post['seo_ga'], ENT_QUOTES, 'UTF-8') : '',
            'meta' => []
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
     * Build social nav
     *
     * @param  string $description
     * @param  string $keywords
     * @return mixed
     */
    public function buildMetaTags($description = null, $keywords = null)
    {
        $metas  = null;
        $config = Table\Config::findById('seo_config');

        if (isset($config->value) && !empty($config->value) && ($config->value != '')) {
            $cfg = unserialize($config->value);
            if (count($cfg['meta']) > 0) {
                foreach ($cfg['meta'] as $meta) {
                    $content = $meta['content'];
                    if (($meta['name'] == 'description') && (null !== $description) && ($description != '')) {
                        $content = $description;
                    }
                    if (($meta['name'] == 'keywords') && (null !== $keywords) && ($keywords != '')) {
                        $content = $keywords;
                    }
                    $m = new Child('meta');
                    $m->setAttributes([
                        'name'    => $meta['name'],
                        'content' => htmlentities($content, ENT_QUOTES, 'UTF-8')
                    ]);
                    $metas .= '    ' . (string)$m;
                }
            }
        }

        return $metas;
    }

}
