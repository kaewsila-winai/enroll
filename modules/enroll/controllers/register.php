<?php
/**
 * @filesource modules/enroll/controllers/register.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Enroll\Register;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=enroll-register
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * แก้ไขข้อมูลส่วนตัวสมาชิก
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::get('Student application form');
        // เลือกเมนู
        $this->menu = 'enroll';
        // ตรวจสอบรายการที่เลือก
        $user = \Enroll\Register\Model::get($request->request('id')->toInt());
        // สมาชิก
        $login = Login::isMember();
        // ใหม่ หรือแก้ไขโดยผู้ดูแล
        if ($user && ($user->id == 0 || Login::checkPermission($login, 'can_manage_enroll'))) {
            // แสดงผล
            $section = Html::create('section', array(
                'class' => 'content_bg',
            ));
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs',
            ));
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><a class="icon-register" href="index.php">{LNG_Home}</a></li>');
            $ul->appendChild('<li><span>{LNG_Enroll}</span></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-write">'.$this->title.'</h2>',
            ));
            // แสดงฟอร์ม
            $section->appendChild(createClass('Enroll\Register\View')->render($request, $user, $login));
            // คืนค่า HTML

            return $section->render();
        }
        // 404

        return \Index\Error\Controller::execute($this);
    }
}
