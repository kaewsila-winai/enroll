<?php
/**
 * @filesource modules/enroll/views/setup.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Enroll\Setup;

use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=enroll-setup
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var object
     */
    private $category;
    /**
     * ตารางรายชื่อผู้ลงทะเบียน
     *
     * @param Request $request
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        // หมวดหมู่
        $this->category = \Enroll\Category\Model::init();
        $params = array();
        $filters = array();
        $headers = array(
            'name' => array(
                'text' => '{LNG_Name}',
                'sort' => 'name',
            ),
            'id_card' => array(
                'text' => '{LNG_Identification No.}',
            ),
            'id' => array(
                'text' => '',
            ),
            'phone' => array(
                'text' => '{LNG_Phone}',
                'class' => 'center',
            ),
        );
        $cols = array(
            'phone' => array(
                'class' => 'center',
            ),
            'create_date' => array(
                'class' => 'center',
            ),
            'gpa' => array(
                'class' => 'center',
            ),
        );
        foreach (Language::get('ENROLL_TYPIES') as $typ => $label) {
            $params[$typ] = $request->request($typ)->toInt();
            $filters[] = array(
                'name' => $typ,
                'text' => $label,
                'options' => array(0 => '{LNG_all items}') + $this->category->toSelect($typ),
                'value' => $params[$typ],
            );
            $headers[$typ] = array(
                'text' => $label,
                'class' => 'center',
            );
            $cols[$typ] = array(
                'class' => 'center',
            );
        }
        $headers['create_date'] = array(
            'text' => '{LNG_Date}',
            'class' => 'center',
            'sort' => 'create_date',
        );
        $cols['create_date'] = array(
            'class' => 'center',
        );
        $headers['gpa'] = array(
            'text' => 'GPA',
            'class' => 'center',
            'sort' => 'gpa',
        );
        $cols['gpa'] = array(
            'class' => 'center',
        );
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable(array(
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Enroll\Setup\Model::toDataTable($params),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('enrollSetup_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('enrollSetup_sort', 'id desc')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => array('name', 'phone', 'id_card'),
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/enroll/model/setup/action',
            'actionCallback' => 'dataTableActionCallback',
            'actions' => array(
                array(
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => array(
                        'delete' => '{LNG_Delete}',
                    ),
                ),
                array(
                    'class' => 'button icon-excel orange',
                    'text' => '{LNG_Download} CSV',
                    'href' => 'export.php?module=enroll-setup&amp;'.http_build_query($params),
                    'target' => 'export',
                ),
            ),
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => $filters,
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => $headers,
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => $cols,
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => array(
                array(
                    'class' => 'icon-edit button green',
                    'href' => $uri->createBackUri(array('module' => 'enroll-register', 'id' => ':id')),
                    'text' => '{LNG_Edit}',
                ),
            ),
        ));
        // save cookie
        setcookie('enrollSetup_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('enrollSetup_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
        // คืนค่า HTML

        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว.
     *
     * @param array  $item ข้อมูลแถว
     * @param int    $o    ID ของข้อมูล
     * @param object $prop กำหนด properties ของ TR
     *
     * @return array
     */
    public function onRow($item, $o, $prop)
    {
        $item['phone'] = '<a href="tel:'.$item['phone'].'">'.$item['phone'].'</a>';
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'enroll/'.$item['id'].'.jpg') ? WEB_URL.DATA_FOLDER.'enroll/'.$item['id'].'.jpg' : WEB_URL.'skin/img/noicon.jpg';
        $item['id'] = '<img src="'.$thumb.'" style="max-height:32px;max-width:50px" alt=thumbnail>';
        $item['create_date'] = Date::format($item['create_date'], 'd M Y');
        foreach (Language::get('ENROLL_TYPIES') as $typ => $label) {
            $item[$typ] = $this->category->get($typ, $item[$typ]);
        }

        return $item;
    }
}
