<?php

namespace Kanboard\Plugin\Wiki\Model;

use DateInterval;
use DateTime;
use SimpleValidator\Validator;
use SimpleValidator\Validators;
use Kanboard\Core\Base;
use Kanboard\Model\SubtaskTimeTrackingModel;
use Kanboard\Model\TaskModel;
use Kanboard\Model\UserModel;
use Kanboard\Model\SubtaskModel;

/**
 * Wiki
 *
 * @package  model
 * @author   Frederic Guillot
 */
class Wiki extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    const TABLE = 'wiki_lines';

    /**
     * Get all wiki lines for a project
     *
     * @access public
     * @param  integer   $project_id
     * @return array
     */
    public function getAll($project_id)
    {
        return $this->db->table(self::TABLE)->eq('project_id', $project_id)->desc('date')->findAll();
    }

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
        return $this->db->table(self::WIKITABLE)->eq('project_id', $project_id)->desc('order')->findAll();
    }

    /**
     * Get the current total of the wiki
     *
     * @access public
     * @param  integer   $project_id
     * @return float
     */
    public function getTotal($project_id)
    {
        $result = $this->db->table(self::TABLE)->columns('SUM(amount) as total')->eq('project_id', $project_id)->findOne();
        return isset($result['total']) ? (float) $result['total'] : 0;
    }

    /**
     * Get breakdown by tasks/subtasks/users
     *
     * @access public
     * @param  integer    $project_id
     * @return \PicoDb\Table
     */
    public function getSubtaskBreakdown($project_id)
    {
        return $this->db
                    ->table(SubtaskTimeTrackingModel::TABLE)
                    ->columns(
                        SubtaskTimeTrackingModel::TABLE.'.id',
                        SubtaskTimeTrackingModel::TABLE.'.user_id',
                        SubtaskTimeTrackingModel::TABLE.'.subtask_id',
                        SubtaskTimeTrackingModel::TABLE.'.start',
                        SubtaskTimeTrackingModel::TABLE.'.time_spent',
                        SubtaskModel::TABLE.'.task_id',
                        SubtaskModel::TABLE.'.title AS subtask_title',
                        TaskModel::TABLE.'.title AS task_title',
                        TaskModel::TABLE.'.project_id',
                        UserModel::TABLE.'.username',
                        UserModel::TABLE.'.name'
                    )
                    ->join(SubtaskModel::TABLE, 'id', 'subtask_id')
                    ->join(TaskModel::TABLE, 'id', 'task_id', SubtaskModel::TABLE)
                    ->join(UserModel::TABLE, 'id', 'user_id')
                    ->eq(TaskModel::TABLE.'.project_id', $project_id)
                    ->callback(array($this, 'applyUserRate'));
    }

    /**
     * Gather necessary information to display the wiki graph
     *
     * @access public
     * @param  integer  $project_id
     * @return array
     */
    public function getDailyWikiBreakdown($project_id)
    {
        $out = array();
        $in = $this->db->hashtable(self::TABLE)->eq('project_id', $project_id)->gt('amount', 0)->asc('date')->getAll('date', 'amount');
        $time_slots = $this->getSubtaskBreakdown($project_id)->findAll();

        foreach ($time_slots as $slot) {
            $date = date('Y-m-d', $slot['start']);

            if (! isset($out[$date])) {
                $out[$date] = 0;
            }

            $out[$date] += $slot['cost'];
        }

        $start = key($in) ?: key($out);
        $end = new DateTime;
        $left = 0;
        $serie = array();

        for ($today = new DateTime($start); $today <= $end; $today->add(new DateInterval('P1D'))) {

            $date = $today->format('Y-m-d');
            $today_in = isset($in[$date]) ? (int) $in[$date] : 0;
            $today_out = isset($out[$date]) ? (int) $out[$date] : 0;

            if ($today_in > 0 || $today_out > 0) {

                $left += $today_in;
                $left -= $today_out;

                $serie[] = array(
                    'date' => $date,
                    'in' => $today_in,
                    'out' => -$today_out,
                    'left' => $left,
                );
            }
        }

        return $serie;
    }

    /**
     * Filter callback to apply the rate according to the effective date
     *
     * @access public
     * @param  array   $records
     * @return array
     */
    public function applyUserRate(array $records)
    {
        $rates = $this->hourlyRate->getAllByProject($records[0]['project_id']);

        foreach ($records as &$record) {

            $hourly_price = 0;

            foreach ($rates as $rate) {
                if ($rate['user_id'] == $record['user_id'] && date('Y-m-d', $rate['date_effective']) <= date('Y-m-d', $record['start'])) {
                    $hourly_price = $this->currencyModel->getPrice($rate['currency'], $rate['rate']);
                    break;
                }
            }

            $record['cost'] = $hourly_price * $record['time_spent'];
        }

        return $records;
    }
    
    /**
     * Add a new wikipage into the database
     *
     * @access public
     * @param  integer   $project_id
     * @param  float     $amount
     * @param  string    $comment
     * @param  string    $date
     * @return boolean|integer
     */
    // , $date = ''
    public function createpage($project_id, $title, $comment, $content, $order = null)
    {
        $values = array(
            'project_id' => $project_id,
            'title' => $title,
            'content' => $content,
            'date_creation' => date('Y-m-d'),
            'date_modification' => date('Y-m-d'),
            'order' => $order ?: time()
        );

        return $this->db->table(self::TABLE)->persist($values);
    }

    /**
     * Add a new wiki line in the database
     *
     * @access public
     * @param  integer   $project_id
     * @param  float     $amount
     * @param  string    $comment
     * @param  string    $date
     * @return boolean|integer
     */
    public function create($project_id, $amount, $comment, $date = '')
    {
        $values = array(
            'project_id' => $project_id,
            'amount' => $amount,
            'comment' => $comment,
            'date' => $date ?: date('Y-m-d'),
        );

        return $this->db->table(self::TABLE)->persist($values);
    }

    /**
     * Remove a specific wiki page
     *
     * @access public
     * @param  integer    $wiki_id
     * @return boolean
     */
    public function removepage($wiki_id)
    {
        return $this->db->table(self::WIKITABLE)->eq('id', $wiki_id)->remove();
    }

    /**
     * Remove a specific wiki line
     *
     * @access public
     * @param  integer    $wiki_id
     * @return boolean
     */
    public function remove($wiki_id)
    {
        return $this->db->table(self::TABLE)->eq('id', $wiki_id)->remove();
    }

    /**
     * Validate creation
     *
     * @access public
     * @param  array   $values           Form values
     * @return array   $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateCreation(array $values)
    {
        $v = new Validator($values, array(
            new Validators\Required('project_id', t('Field required')),
            new Validators\Required('amount', t('Field required')),
        ));

        return array(
            $v->execute(),
            $v->getErrors()
        );
    }
}
