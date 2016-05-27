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
namespace Phire\Seo\Event;

use Phire\Seo\Model;
use Phire\Controller\AbstractController;
use Pop\Application;

/**
 * SEO Event class
 *
 * @category   Phire\Seo
 * @package    Phire\Seo
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class Seo
{

    /**
     * Bootstrap the module
     *
     * @param  Application $application
     * @return void
     */
    public static function bootstrap(Application $application)
    {
        if (($application->isRegistered('phire-content')) && ($application->isRegistered('phire-fields'))) {
            $fields = \Phire\Fields\Table\Fields::findBy(['models' => "%Phire\\\\Content\\\\Model\\\\Content%"]);
            $names  = [];
            foreach ($fields->rows() as $field) {
                $names[] = $field->name;
            }

            if (!in_array('seo_title', $names)) {
                $field = new \Phire\Fields\Table\Fields([
                    'group_id'       => null,
                    'storage'        => 'eav',
                    'type'           => 'text',
                    'name'           => 'seo_title',
                    'label'          => 'SEO Title',
                    'values'         => null,
                    'default_values' => null,
                    'attributes'     => 'size="80" style="width: 99.5%;"',
                    'validators'     => 'a:0:{}',
                    'encrypt'        => 0,
                    'order'          => -3,
                    'required'       => 0,
                    'prepend'        => 0,
                    'dynamic'        => 0,
                    'editor'         => null,
                    'models'         => 'a:1:{i:0;a:3:{s:5:"model";s:27:"Phire\Content\Model\Content";s:10:"type_field";N;s:10:"type_value";N;}}'
                ]);
                $field->save();
            }
            if (!in_array('description', $names)) {
                $field = new \Phire\Fields\Table\Fields([
                    'group_id'       => null,
                    'storage'        => 'eav',
                    'type'           => 'text',
                    'name'           => 'description',
                    'label'          => 'Description',
                    'values'         => null,
                    'default_values' => null,
                    'attributes'     => 'size="80" style="width: 99.5%;"',
                    'validators'     => 'a:0:{}',
                    'encrypt'        => 0,
                    'order'          => -2,
                    'required'       => 0,
                    'prepend'        => 0,
                    'dynamic'        => 0,
                    'editor'         => null,
                    'models'         => 'a:1:{i:0;a:3:{s:5:"model";s:27:"Phire\Content\Model\Content";s:10:"type_field";N;s:10:"type_value";N;}}'
                ]);
                $field->save();
            }
            if (!in_array('keywords', $names)) {
                $field = new \Phire\Fields\Table\Fields([
                    'group_id'       => null,
                    'storage'        => 'eav',
                    'type'           => 'text',
                    'name'           => 'keywords',
                    'label'          => 'Keywords',
                    'values'         => null,
                    'default_values' => null,
                    'attributes'     => 'size="80" style="width: 99.5%;"',
                    'validators'     => 'a:0:{}',
                    'encrypt'        => 0,
                    'order'          => -1,
                    'required'       => 0,
                    'prepend'        => 0,
                    'dynamic'        => 0,
                    'editor'         => null,
                    'models'         => 'a:1:{i:0;a:3:{s:5:"model";s:27:"Phire\Content\Model\Content";s:10:"type_field";N;s:10:"type_value";N;}}'
                ]);
                $field->save();
            }
        }
    }

    /**
     * Init SEO model
     *
     * @param  AbstractController $controller
     * @param  Application        $application
     * @return void
     */
    public static function init(AbstractController $controller, Application $application)
    {
        if ((!$_POST) && ($controller->hasView()) && (($controller instanceof \Phire\Content\Controller\IndexController) || ($controller instanceof \Phire\Categories\Controller\IndexController))) {
            $controller->view()->phire->seo = new Model\Seo();
        }
    }

    /**
     * Parse SEO
     *
     * @param  AbstractController $controller
     * @param  Application        $application
     * @return void
     */
    public static function parse(AbstractController $controller, Application $application)
    {
        if (($controller->hasView()) && (($controller instanceof \Phire\Content\Controller\IndexController) || ($controller instanceof \Phire\Categories\Controller\IndexController))) {
            $seo  = new Model\Seo();
            $body = $controller->response()->getBody();
            if (strpos($body, '[{seo_meta_tags}]') !== false) {
                $body = str_replace('[{seo_meta_tags}]', $seo->buildMetaTags($controller->view()->description, $controller->view()->keywords), $body);
            }

            if (!empty($seo->tracking)) {
                $body = str_replace('</head>', $seo->tracking . PHP_EOL . PHP_EOL . '</head>', $body);
            }

            $controller->response()->setBody($body);
        }
    }

}
