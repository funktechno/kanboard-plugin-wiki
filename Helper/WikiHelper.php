<?php

namespace Kanboard\Plugin\Wiki\Helper;

use Kanboard\Core\Base;
use Kanboard\Model\Wiki;


class WikiHelper extends Base
{
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
    public function renderChildren($children, $parent_id, $project, $selected_wiki_id, $not_editable, $indent = 0) {
        $html = '<ul data-parent-id="' . $parent_id . '" class="hideMe"' . ($parent_id == 0 ? ' id="wikiroot"' : '') . '>';
        if (isset($children) && (count($children) > 0)) {
            foreach ($children as $item) {
                $is_selected = ($selected_wiki_id == $item['id']) ? ' selected' : '';
                $has_children = isset($item['children']) && (count($item['children']) > 0);
                $html .= '<li class="wikipage' . $is_selected . '" data-project-id="' . $project['id'] . '" data-page-id="' . $item['id'] . '" data-page-order="' . $item['ordercolumn'] . '">';
                if (!$not_editable) {
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
                $html .= str_repeat('&nbsp;', 2 * ($indent + 1));
                if ($has_children){
                    $html .= '<button class="branch actionBigger" title="' . t('Expand/Collapse Subpages') . '"><a><i class="fa fa-plus-square-o"></i></a></button>';
                    $wikipage_icon = 'folder-o';
                } else {
                    $html .= '<button class="indent actionBigger"><a><i class="fa fa-square-o"></i></a></button>';
                    $wikipage_icon = 'file-word-o';
                }
                $html .= '<span class="wikibranch">';
                if (!$not_editable) {
                    $html .= $this->helper->url->icon(
                        $wikipage_icon, $item['title'], 'WikiController', 'detail', array('plugin' => 'wiki', 'project_id' => $project['id'], 'wiki_id' => $item['id']), false, 'wikilink' . $is_selected
                    );
                } else {
                    $html .= $this->helper->url->icon(
                        $wikipage_icon, $item['title'], 'WikiController', 'detail_readonly', array('plugin' => 'wiki', 'token' => $project['token'], 'wiki_id' => $item['id']), false, 'wikilink' . $is_selected
                    );
                }
                $html .= '</span>';
                $html .= $this->renderChildren($item['children'], $item['id'], $project, $selected_wiki_id, $not_editable, $indent + 1);
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
    public function generateIndentedChildren($children, $use_full_pages = false, $parent_id = 0, $exclude_wiki_id = 0, $indent = 0, $only_immediate_subchildren = false) {
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
                        $nestedChildren = $this->generateIndentedChildren($item['children'], $use_full_pages, $only_immediate_subchildren ? $parent_id : $item['id'], $exclude_wiki_id, $indent + 1, $only_immediate_subchildren);
                        $indentedChildren += $nestedChildren;
                    }
                } elseif ($has_children) {
                    $nestedChildren = $this->generateIndentedChildren($item['children'], $use_full_pages, $parent_id, $exclude_wiki_id, $indent, $only_immediate_subchildren);
                    $indentedChildren += $nestedChildren;
                }
            }
        }
        return $indentedChildren;
    }
}
