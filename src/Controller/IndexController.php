<?php
/**
 * Phire SEO Module
 *
 * @link       https://github.com/phirecms/phire-seo
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Seo\Controller;

use Phire\Seo\Model;
use Phire\Controller\AbstractController;

/**
 * SEO Index Controller class
 *
 * @category   Phire\Seo
 * @package    Phire\Seo
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView('seo/index.phtml');
        $seo = new Model\Seo();

        if ($this->request->isPost()) {
            $seo->saveConfig($this->request->getPost());
            $this->sess->setRequestValue('saved', true);
            $this->redirect(BASE_PATH . APP_URI . '/seo');
        } else {
            $this->view->title     = 'SEO';
            $this->view->seoConfig = $seo->getConfig();
        }

        $this->send();
    }

    /**
     * Analysis action method
     *
     * @return void
     */
    public function analysis()
    {
        $this->prepareView('seo/analysis.phtml');
        $seo = new Model\Seo();

        if ($this->request->getQuery('run') == '1') {
            $seo->saveAnalysis($this->application->module('phire-seo')['exclude']);
            $this->sess->setRequestValue('saved', true);
            $this->redirect(BASE_PATH . APP_URI . '/seo/analysis');
        } else {
            $this->view->title       = 'SEO Analysis';
            $this->view->seoAnalysis = $seo->getAnalysis();
        }

        $this->send();
    }

    /**
     * JSON action method
     *
     * @return void
     */
    public function json()
    {
        $json = [
            'seo_title'   => '',
            'description' => '',
            'keywords'    => ''
        ];

        $seoTitle    = \Phire\Fields\Table\Fields::findBy(['name' => 'seo_title']);
        $description = \Phire\Fields\Table\Fields::findBy(['name' => 'description']);
        $keywords    = \Phire\Fields\Table\Fields::findBy(['name' => 'keywords']);

        if (isset($seoTitle->id)) {
            $json['seo_title'] = $seoTitle->id;
        }
        if (isset($description->id)) {
            $json['description'] = $description->id;
        }
        if (isset($keywords->id)) {
            $json['keywords'] = $keywords->id;
        }

        $body = json_encode($json, JSON_PRETTY_PRINT);
        $this->send(200, ['Content-Type' => 'application/json'], $body);

    }

    /**
     * Robots action method
     *
     * @return void
     */
    public function robots()
    {
        $seo = new Model\Seo();
        if (!empty($seo->robots) && ($seo->robots != '')) {
            $this->send(200, ['Content-Type' => 'text/plain'], $seo->robots);
        } else {
            $this->send(404, ['Content-Type' => 'text/html'], '<html>' . PHP_EOL . '<head>' . PHP_EOL .
                '<title>404 - Page Not Found</title>' . PHP_EOL . '</head>' . PHP_EOL . '<body>' . PHP_EOL .
                '<h1>Page Not Found</h1>' . PHP_EOL . '</body>' . PHP_EOL . '</html>' . PHP_EOL);
        }
    }

    /**
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->viewPath = __DIR__ . '/../../view';
        parent::prepareView($template);
    }

}
