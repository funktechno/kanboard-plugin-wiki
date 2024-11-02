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
    public function renderChildren($children, $parent_id, $project, $selected_wiki_id, $not_editable) {
        $html = '<ul' . ($parent_id == 0 ? ' id="wikiroot"' : '').' data-parent-id="'.$parent_id.'">';
        if (isset($children) && (count($children) > 0)) {
            foreach ($children as $item) {
                $is_active = ($selected_wiki_id == $item['id']) ? ' active' : '';
                $has_children = isset($item['children']) && (count($item['children']) > 0);
                $html .= '<li class="wikipage' . $is_active . '" data-project-id="' . $project['id'] . '" data-page-id="' . $item['id'] . '" data-page-order="' . $item['ordercolumn'] . '">';
                if(!$not_editable) {
                    $html .= '<div style="float: right">';
                    $html .= '<button class="handle sortable-handle" hidden><a><i class="fa fa-arrows"></i></a></button>';
                    $html .= '<button class="action" title="' . t('Edit Page') . '">';
                    $html .= $this->helper->modal->medium('edit', '', 'WikiController', 'edit', array('plugin' => 'wiki', 'wiki_id' => $item['id']));
                    $html .= '</button>';
                    $html .= '<button class="action" title="' . t('Remove Page') . '">';
                    $html .= $this->helper->modal->confirm('trash-o', '', 'WikiController', 'confirm', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $item['id']));
                    $html .= '</button>';
                    $html .= '</div>';
                }
                if($has_children){
                    $html .= '<button class="branch actionBigger" title="' . t('Expand/Collapse Subpages') . '"><a><i class="fa fa-minus-square-o"></i></a></button>';
                    $wikipage_icon = 'folder-o';
                } else {
                    $html .= '<button class="indent actionBigger"><i class="fa fa-square-o"></i></button>';
                    $wikipage_icon = 'file-word-o';
                }
                $html .= '<span class="wikibranch">';
                if (!$not_editable) {
                    $html .= $this->helper->url->icon(
                        $wikipage_icon, $item['title'], 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $item['id']), false, 'wikilink' . $is_active
                    );
                } else {
                    $html .= $this->helper->url->icon(
                        $wikipage_icon, $item['title'], 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $item['id']), false, 'wikilink' . $is_active
                    );
                }
                $html .= '</span>';
                $html .= $this->renderChildren($item['children'], $item['id'], $project, $selected_wiki_id, $not_editable);
                $html .= '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * generate indented sublist of children
     * @param mixed $children
     * @param mixed $parent_id
     */
    public function generateIndentedChildren($children, $use_full_pages = false, $parent_id = 0, $exclude_wiki_id = 0, $indent = 0) {
        $indentedChildren = array();
        if ($parent_id == 0) {
            $indentedChildren[''] = t('(root)');
        }
        foreach ($children as $item) {
            if ($exclude_wiki_id != $item['id']) {
                $has_children = isset($item['children']) && (count($item['children']) > 0);
                if ($parent_id == 0 || $parent_id == $item['parent_id']) {
                    if ($use_full_pages) {
                        $item['title'] = str_repeat('&nbsp;', ($indent + 1) * 4) . ' ' . $item['title'];
                        $indentedChildren[$item['id']] = $item;
                    } else {
                        $indentedChildren[$item['id']] = str_repeat('&nbsp;', ($indent + 1) * 4) . ' ' . $item['title'];
                    }
                    if ($has_children) {
                        $nestedChildren = $this->generateIndentedChildren($item['children'], $use_full_pages, $item['id'], $exclude_wiki_id, $indent + 1);
                        $indentedChildren += $nestedChildren;
                    }
                } elseif ($has_children) {
                    $nestedChildren = $this->generateIndentedChildren($item['children'], $use_full_pages, $parent_id, $exclude_wiki_id, $indent);
                    $indentedChildren += $nestedChildren;
                }
            }
        }
        return $indentedChildren;
    }
}
