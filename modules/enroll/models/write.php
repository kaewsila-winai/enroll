<?php
/**
 * @filesource modules/enroll/models/write.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Enroll\Write;

use Gcms\Login;
use Kotchasan\File;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=enroll-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * บันทึกข้อมูล (write.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token, can_config, ไม่ใช่สมาชิกตัวอย่าง
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_config')) {
                // รับค่าจากการ POST
                $language = $request->post('write_language')->filter('a-z');
                $detail = $request->post('write_detail')->detail();
                if (in_array($language, Language::installedLanguage())) {
                    // บันทึก
                    $f = @fopen(ROOT_PATH.DATA_FOLDER.'enroll/page_'.$language.'.html', 'w');
                    if ($f) {
                        fwrite($f, $detail);
                        fclose($f);
                        // คืนค่า
                        $ret['alert'] = Language::get('Saved successfully');
                        // reload
                        $ret['location'] = 'reload';
                        // เคลียร์
                        $request->removeToken();
                    } else {
                        // ไม่สามารถเขียนไฟล์ได้
                        $ret['alert'] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'enroll/');
                    }
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
