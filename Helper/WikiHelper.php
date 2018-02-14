<?php

namespace Kanboard\Helper;

use Kanboard\Core\Base;

class MyHelper extends Base
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
        return $this->wiki->getWikipages($project['id']);
        // return $this->db->table(self::WIKITABLE)->eq('project_id', $project_id)->desc('order')->findAll();
    }

    // public function doSomething()
    // {
    //     return 'foobar';
    // }
}