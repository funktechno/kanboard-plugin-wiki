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
        // return $this->wiki->getWikipages($project['id']);
        // return $this->db->table(self::WIKITABLE)->eq('project_id', $project_id)->desc('order')->findAll();
    }

    public function renderChildren($children, $project, $not_editable){
        $html = '<ul>';
        foreach ($children as $item) {
            $html .= '<li>';
            if(!$not_editable){
                $html .= $this->helper->url->link(
                    t($item['title']), 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $item['id'])
                );
                $html .= $this->helper->modal->confirm('trash-o', t(''), 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $item['id']));
            } else {
                $html .= $this->helper->url->link(
                    t($item['title']), 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $item['id'])
                );
            }
            if(count($item['children']) > 0){
                $html .= $this->renderChildren($item['children'], $project, $not_editable);
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