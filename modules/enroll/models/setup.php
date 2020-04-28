<?php
/**
 * @filesource modules/enroll/models/setup.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Enroll\Setup;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * โมเดลสำหรับ (setup.php).
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * Query ข้อมูลสำหรับส่งให้กับ DataTable
     *
     * @param array $params
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($params)
    {
        $where = array();
        if ($params['level'] > 0) {
            $where[] = array('level', $params['level']);
        }
        if ($params['plan'] > 0) {
            $where[] = array('plan', $params['plan']);
        }

        return static::createQuery()
            ->select('name', 'id', 'id_card', 'phone', 'level', 'plan', 'create_date', 'gpa')
            ->from('enroll')
            ->where($where);
    }

    /**
     * รับค่าจาก action (setup.php)
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = array();
        // session, referer, can_manage_enroll
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_manage_enroll')) {
                // รับค่าจากการ POST
                $action = $request->post('action')->toString();
                // id ที่ส่งมา
                if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->toString(), $match)) {
                    // ตาราง
                    $table = $this->getTableName('enroll');
                    if ($action === 'delete') {
                        // ลบ
                        $this->db()->delete($table, array('id', $match[1]), 0);
                        // ลบไฟล์
                        foreach ($match[1] as $id) {
                            if (is_file(ROOT_PATH.DATA_FOLDER.'enroll/'.$id.'.jpg')) {
                                unlink(ROOT_PATH.DATA_FOLDER.'enroll/'.$id.'.jpg');
                            }
                        }
                        // reload
                        $ret['location'] = 'reload';
                    }
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }

    /**
     * ส่งออกข้อมูล
     *
     * @param array $select
     * @param array $params
     *
     * @return array
     */
    public static function export($select, $params)
    {
        $where = array();
        if ($params['level'] > 0) {
            $where[] = array('E.level', $params['level']);
        }
        if ($params['plan'] > 0) {
            $where[] = array('E.plan', $params['plan']);
        }

        return \Kotchasan\Model::createQuery()
            ->select($select)
            ->from('enroll E')
            ->join('province P', 'LEFT', array('P.id', 'E.provinceID'))
            ->join('amphur A', 'LEFT', array(array('A.id', 'E.amphurID'), array('A.province_id', 'P.id')))
            ->join('district D', 'LEFT', array(array('D.id', 'E.districtID'), array('D.amphur_id', 'A.id')))
            ->where($where)
            ->order('E.create_date')
            ->cacheOn()
            ->execute();
    }
}
