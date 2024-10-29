<?php

namespace Kanboard\Plugin\Wiki\Helper;

use Kanboard\Core\Base;
use Kanboard\Model\Wiki;


class WikiHelper extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    const WIKITABLE = 'wikipage';
    /**
     * Get all Wiki Pages by order for a project
     *
     * @access public
     * @param  integer   $project_id
     * @return array
     */
    public function getWikipages($project_id)
    {
        return null;
        // return wiki::getWikipages($project['id']);
        // return $this->wikiModel->getWikipages($project['id']);
        // return $this->db->table(self::WIKITABLE)->eq('project_id', $project_id)->desc('order')->findAll();
    }
    /**
     * Add a Javascript asset
     *
     * @param  string $filepath Filepath
     * @param  bool   $async
     * @return string
     */
    public function js($filepath, $async = false)
    {
        return '<script '.($async ? 'async' : '').' defer type="text/javascript" src="'.$this->helper->url->dir().$filepath.'?'.filemtime($filepath).'"></script>';
    }
    /**
     * render wiki page html children recursively
     * @param mixed $children
     * @param mixed $parent_id
     * @param mixed $project
     * @param mixed $selected_wiki_id
     * @param mixed $not_editable
     * @return string
     */
    public function renderChildren($children, $parent_id, $project, $selected_wiki_id, $not_editable){
        $html = '<ul data-parent-id="'.$parent_id.'">';
        foreach ($children as $item) {
            $is_active = ($selected_wiki_id == $item['id']) ? ' active' : '';
            $html .= '<li class="wikipage'.$is_active.'" data-project-id="'.$project['id'].'" data-page-id="'.$item['id'].'" data-page-order="'.$item['ordercolumn'].'">';
            if(count($item['children']) > 0){
                $html .= '<button class="branch"><a><i class="fa fa-minus-square"></i></a></button>';
            } else {
                $html .= '<button class="ident"><a><i class="fa fa-square"></i></a></button>';
            }
            $html .= '<button class="ident"></button>';
            if(!$not_editable){
                $html .= $this->helper->url->link(
                    t($item['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $item['id']), false, 'wikilink'.$is_active
                );
                $html .= '<button class="ident"></button>';
                $html .= $this->helper->modal->confirm('trash-o', '', 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $item['id']));
            } else {
                $html .= $this->helper->url->link(
                    t($item['title']), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $item['id']), false, 'wikilink'.$is_active
                );
            }
            if(count($item['children']) > 0){
                $html .= $this->renderChildren($item['children'], $item['id'], $project, $selected_wiki_id, $not_editable);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    // public function doSomething()
    // {
    //     return 'foobar';
    // }
}
