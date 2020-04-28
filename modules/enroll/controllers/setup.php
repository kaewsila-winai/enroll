<?php
/**
 * @filesource modules/enroll/controllers/setup.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Enroll\Setup;

use Gcms\Login;
use Kotchasan\Date;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=enroll-setup
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * ตารางรายการ สินค้า.
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::trans('{LNG_List of} {LNG_Enroll}');
        // เลือกเมนู
        $this->menu = 'enrollsetup';
        // สมาชิก
        $login = Login::isMember();
        // สามารถจัดการการลงทะเบียนได้
        if (Login::checkPermission($login, 'can_manage_enroll')) {
            // แสดงผล
            $section = Html::create('section', array(
                'class' => 'content_bg',
            ));
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs',
            ));
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><span class="icon-register">{LNG_Home}</span></li>');
            $ul->appendChild('<li><span>{LNG_Enroll}</span></li>');
            $ul->appendChild('<li><span>{LNG_List of}</span></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-list">'.$this->title.'</h2>',
            ));
            // แสดงตาราง
            $section->appendChild(createClass('Enroll\Setup\View')->render($request, $login));
            // คืนค่า HTML

            return $section->render();
        }
        // 404

        return \Index\Error\Controller::execute($this);
    }

    /**
     * export
     *
     * @param Request $request
     */
    public function export(Request $request)
    {
        // สามารถจัดการรายการลงทะเบียนได้
        if (Login::checkPermission(Login::isMember(), 'can_manage_enroll')) {
            $category = \Enroll\Category\Model::init();
            $titles = Language::get('TITLES');
            $header = array();
            $select = array();
            $params = array();
            foreach (Language::get('ENROLL_TYPIES') as $typ => $label) {
                $header[] = $label;
                $params[$typ] = $request->get($typ)->toInt();
                $select[] = 'E.'.$typ;
            }
            $select = array_merge($select, array(
                'E.title',
                'E.name',
                'E.id_card',
                'E.birthday',
                'E.phone',
                'E.email',
                'E.nationality',
                'E.religion',
                'E.address',
                'D.district',
                'A.amphur',
                'P.province',
                'E.zipcode',
                'E.father',
                'E.father_phone',
                'E.mother',
                'E.mother_phone',
                'E.parent',
                'E.parent_phone',
                'E.original_school',
                'E.gpa',
            ));
            $header[] = Language::get('Title');
            $header[] = Language::get('Name');
            $header[] = Language::get('Identification No.');
            $header[] = Language::get('Birthday');
            $header[] = Language::get('Phone');
            $header[] = Language::get('Email');
            $header[] = Language::get('Nationality');
            $header[] = Language::get('Religion');
            $header[] = Language::get('Address');
            $header[] = Language::get('District');
            $header[] = Language::get('Amphur');
            $header[] = Language::get('Province');
            $header[] = Language::get('Zipcode');
            $header[] = Language::trans('{LNG_Name} {LNG_Father}');
            $header[] = Language::get('Phone');
            $header[] = Language::trans('{LNG_Name} {LNG_Mother}');
            $header[] = Language::get('Phone');
            $header[] = Language::trans('{LNG_Name} {LNG_Parent}');
            $header[] = Language::get('Phone');
            $header[] = Language::get('Original school');
            $header[] = Language::get('Grade point average');
            $datas = array();
            foreach (\Enroll\Setup\Model::export($select, $params) as $item) {
                $datas[] = array(
                    $category->get('level', $item->level),
                    $category->get('plan', $item->plan),
                    $titles[$item->title],
                    $item->name,
                    $item->id_card,
                    Date::format($item->birthday, 'd M Y'),
                    $item->phone,
                    $item->email,
                    $item->nationality,
                    $item->religion,
                    $item->address,
                    $item->district,
                    $item->amphur,
                    $item->province,
                    $item->zipcode,
                    $item->father,
                    $item->father_phone,
                    $item->mother,
                    $item->mother_phone,
                    $item->parent,
                    $item->parent_phone,
                    $item->original_school,
                    $item->gpa,
                );
            }
            // export to CSV

            return \Kotchasan\Csv::send('enroll', $header, $datas, 'UTF-8');
        }

        return false;
    }
}
