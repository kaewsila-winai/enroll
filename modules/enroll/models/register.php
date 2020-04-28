<?php
/**
 * @filesource modules/enroll/models/register.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Enroll\Register;

use Gcms\Login;
use Kotchasan\File;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=enroll.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านข้อมูลสมาชิกที่ $id
     * $id = 0 ลงทะเบียน
     * คืนค่าข้อมูล object ไม่พบคืนค่า false.
     *
     * @param int $id
     *
     * @return array|bool
     */
    public static function get($id)
    {
        if (empty($id)) {
            return (object) array(
                'id' => 0,
            );
        } else {
            return static::createQuery()
                ->from('enroll E')
                ->join('province P', 'LEFT', array('P.id', 'E.provinceID'))
                ->join('amphur A', 'LEFT', array(array('A.id', 'E.amphurID'), array('A.province_id', 'P.id')))
                ->join('district D', 'LEFT', array(array('D.id', 'E.districtID'), array('D.amphur_id', 'A.id')))
                ->where(array('E.id', $id))
                ->first('E.*', 'P.province', 'A.amphur', 'D.district');
        }
    }

    /**
     * บันทึกข้อมูล (enroll.php).
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token
        if ($request->initSession() && $request->isSafe()) {
            // รับค่าจากการ POST
            $save = array(
                'level' => $request->post('register_level')->toInt(),
                'plan' => $request->post('register_plan')->toInt(),
                'title' => $request->post('register_title')->toInt(),
                'name' => $request->post('register_name')->topic(),
                'id_card' => $request->post('register_id_card')->number(),
                'birthday' => $request->post('register_birthday')->date(),
                'phone' => $request->post('register_phone')->number(),
                'email' => $request->post('register_email')->url(),
                'nationality' => $request->post('register_nationality')->topic(),
                'religion' => $request->post('register_religion')->topic(),
                'address' => $request->post('register_address')->topic(),
                'districtID' => $request->post('register_districtID')->number(),
                'amphurID' => $request->post('register_amphurID')->number(),
                'provinceID' => $request->post('register_provinceID')->number(),
                'zipcode' => $request->post('register_zipcode')->number(),
                'father' => $request->post('register_father')->topic(),
                'father_phone' => $request->post('register_father_phone')->number(),
                'mother' => $request->post('register_mother')->topic(),
                'mother_phone' => $request->post('register_mother_phone')->number(),
                'parent' => $request->post('register_parent')->topic(),
                'parent_phone' => $request->post('register_parent_phone')->number(),
                'original_school' => $request->post('register_original_school')->topic(),
                'gpa' => min(4, $request->post('register_gpa')->toFloat()),
            );
            // ชื่อตาราง enroll
            $table_enroll = $this->getTableName('enroll');
            // database connection
            $db = $this->db();
            // ตรวจสอบค่าที่ส่งมา
            $user = self::get($request->post('register_id')->toInt());
            // สมาชิก
            $login = Login::isMember();
            // สามารถจัดการรายการลงทะเบียนได้
            $can_manage_enroll = Login::checkPermission($login, 'can_manage_enroll');
            // ใหม่ หรือแก้ไขโดยผู้ดูแล
            if ($user && ($user->id == 0 || $can_manage_enroll)) {
                foreach (array('name', 'birthday', 'phone', 'nationality', 'religion', 'address', 'zipcode', 'original_school', 'gpa') as $k) {
                    if (empty($save[$k])) {
                        // ไม่ได้กรอก $k
                        $ret['ret_register_'.$k] = 'Please fill in';
                    }
                }
                if (!preg_match('/[0-9]{13,13}/', $save['id_card'])) {
                    // ไม่ได้กรอก id_card หรือ ไม่ถูกต้อง
                    $ret['ret_register_id_card'] = Language::replace('Invalid :name', array(':name' => Language::get('Identification No.')));
                } else {
                    // ตรวจสอบ idcard ซ้ำ
                    $search = $db->first($table_enroll, array('id_card', $save['id_card']));
                    if ($search && ($user->id == 0 || $user->id != $search->id)) {
                        $ret['ret_register_id_card'] = Language::replace('This :name already exist', array(':name' => Language::get('Identification No.')));
                    }
                }
                foreach (array('districtID', 'amphurID', 'provinceID') as $k) {
                    if (empty($save[$k])) {
                        // ไม่ได้กรอก $k
                        $ret['ret_register_'.str_replace('ID', '', $k)] = 'Please fill in';
                    }
                }
                if (empty($ret)) {
                    // ID
                    if ($user->id == 0) {
                        $save['id'] = $db->getNextId($table_enroll);
                    } else {
                        $save['id'] = $user->id;
                    }
                    \Download\Upload\Model::execute($ret, $request, $save['id'], 'enroll', self::$cfg->enroll_attach_file_typies);
                }
                if (empty($ret)) {
                    // ไดเร็คทอรี่
                    $dir = ROOT_PATH.DATA_FOLDER.'enroll/';
                    // อัปโหลดไฟล์
                    foreach ($request->getUploadedFiles() as $item => $file) {
                        /* @var $file \Kotchasan\Http\UploadedFile */
                        if ($file->hasUploadFile()) {
                            if (!File::makeDirectory($dir)) {
                                // ไดเรคทอรี่ไม่สามารถสร้างได้
                                $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'enroll/');
                            } elseif ($item == 'thumbnail') {
                                // อัปโหลด
                                try {
                                    $file->resizeImage(array('jpg', 'jpeg', 'png'), $dir, $save['id'].'.jpg', self::$cfg->enroll_w);
                                } catch (\Exception $exc) {
                                    // ไม่สามารถอัปโหลดได้
                                    $ret['ret_'.$item] = Language::get($exc->getMessage());
                                }
                            }
                        } elseif ($file->hasError()) {
                            // ข้อผิดพลาดการอัปโหลด
                            $ret['ret_'.$item] = Language::get($file->getErrorMessage());
                        } elseif ($user->id == 0) {
                            // ใหม่ ต้องอัปโหลดไฟล์
                            $ret['ret_'.$item] = Language::get('Please upload pictures of students');
                        }
                    }
                }
                // บันทึก
                if (empty($ret)) {
                    if ($user->id == 0) {
                        // ใหม่
                        $save['create_date'] = date('Y-m-d H:i:s');
                        $db->insert($table_enroll, $save);
                    } else {
                        // แก้ไข
                        $db->update($table_enroll, $user->id, $save);
                    }
                    if ($can_manage_enroll) {
                        // กลับไปหน้ารายการการลงทะเบียน
                        $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'enroll-setup', 'id' => 0));
                    } else {
                        // กลับไปหน้าแรก
                        $ret['location'] = WEB_URL.'index.php';
                    }
                    // คืนค่า
                    $ret['alert'] = Language::get('Saved successfully');
                    // เคลียร์
                    $request->removeToken();
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
}
