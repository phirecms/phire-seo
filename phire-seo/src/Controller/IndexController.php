<?php

namespace Phire\Seo\Controller;

use Phire\Seo\Model;
use Phire\Controller\AbstractController;

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
            $seo->save($this->request->getPost());
            $this->sess->setRequestValue('saved', true);
            $this->redirect(BASE_PATH . APP_URI . '/seo');
        } else {
            $this->view->title     = 'SEO';
            $this->view->seoConfig = $seo->getConfig();
        }

        $this->send();
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
