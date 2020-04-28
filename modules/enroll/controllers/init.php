<?php
/**
 * @filesource modules/enroll/controllers/init.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Enroll\Init;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * Init Module.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นเริ่มต้นการทำงานของโมดูลที่ติดตั้ง
     * และจัดการเมนูของโมดูล.
     *
     * @param Request                $request
     * @param \Index\Menu\Controller $menu
     * @param array                  $login
     */
    public static function execute(Request $request, $menu, $login)
    {
        $menu->addTopLvlMenu('enroll', '{LNG_Enroll}', 'index.php?module=enroll-register', null, 'module');
        // เมนูตั้งค่า
        $submenus = array();
        // สามารถตั้งค่าระบบได้
        if (Login::checkPermission($login, 'can_config')) {
            $submenus[] = array(
                'text' => '{LNG_Settings}',
                'url' => 'index.php?module=enroll-settings',
            );
            $submenus[] = array(
                'text' => '{LNG_Page}',
                'url' => 'index.php?module=enroll-write',
            );
        }
        // สามารถจัดการการลงทะเบียนได้
        if (Login::checkPermission($login, 'can_manage_enroll')) {
            foreach (Language::get('ENROLL_TYPIES') as $typ => $label) {
                $submenus[] = array(
                    'text' => $label,
                    'url' => 'index.php?module=enroll-categories&amp;type='.$typ,
                );
            }
            $menu->add('settings', '{LNG_Enroll}', null, $submenus);
            $menu->addTopLvlMenu('enrollsetup', '{LNG_List of} {LNG_Enroll}', 'index.php?module=enroll-setup', null, 'module');
        }
    }

    /**
     * รายการ permission ของโมดูล.
     *
     * @param array $permissions
     *
     * @return array
     */
    public static function updatePermissions($permissions)
    {
        $permissions['can_manage_enroll'] = '{LNG_Can manage enroll}';

        return $permissions;
    }
}
