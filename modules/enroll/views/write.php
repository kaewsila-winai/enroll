<?php
/**
 * @filesource modules/enroll/views/write.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Enroll\Write;

use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=enroll-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มแก้ไขหน้าเพจ
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ภาษา
        $language = $request->request('language', 'th')->filter('a-z');
        if (file_exists(ROOT_PATH.DATA_FOLDER.'enroll/page_'.$language.'.html')) {
            // ภาษาที่เลือก
            $content = file_get_contents(ROOT_PATH.DATA_FOLDER.'enroll/page_'.$language.'.html');
        } else {
            // ใช้เนื้อหาภาษาไทย (เริ่มต้น)
            $content = file_get_contents(ROOT_PATH.'modules/enroll/template/page_th.html');
        }
        // ฟอร์ม
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/enroll/model/write/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Page details}',
        ));
        // language
        $fieldset->add('select', array(
            'id' => 'write_language',
            'label' => '{LNG_Language}',
            'labelClass' => 'g-input icon-language',
            'itemClass' => 'item',
            'options' => Language::installedLanguage(),
            'value' => $language,
        ));
        // detail
        $fieldset->add('ckeditor', array(
            'id' => 'write_detail',
            'itemClass' => 'item',
            'height' => 300,
            'language' => Language::name(),
            'toolbar' => 'Document',
            'upload' => true,
            'label' => '{LNG_Detail}',
            'value' => $content,
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}',
        ));
        $form->script('initEnrollWrite();');
        // คืนค่า HTML

        return $form->render();
    }
}
