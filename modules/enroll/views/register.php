<?php
/**
 * @filesource modules/enroll/views/register.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Enroll\Register;

use Kotchasan\File;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Http\UploadedFile;
use Kotchasan\Language;

/**
 * module=enroll-register
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มลงทะเบียนเรียน
     *
     * @param Request $request
     * @param object $user
     * @param object $login
     *
     * @return string
     */
    public function render(Request $request, $user, $login)
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/enroll/model/register/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Enroll type}',
        ));
        // typies
        $category = \Enroll\Category\Model::init();
        $i = 0;
        foreach (Language::get('ENROLL_TYPIES') as $type => $label) {
            if ($i % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
            $i++;
            $groups->add('select', array(
                'id' => 'register_'.$type,
                'labelClass' => 'g-input icon-menus',
                'itemClass' => 'width50',
                'label' => $label,
                'options' => $category->toSelect($type),
                'value' => isset($user->{$type}) ? $user->{$type} : 1,
            ));
        }
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Student information}',
        ));
        $groups = $fieldset->add('groups');
        // title
        $groups->add('select', array(
            'id' => 'register_title',
            'labelClass' => 'g-input',
            'itemClass' => 'width20',
            'label' => '{LNG_Title}',
            'options' => Language::get('TITLES'),
            'value' => isset($user->title) ? $user->title : 1,
        ));
        // name
        $groups->add('text', array(
            'id' => 'register_name',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width80',
            'label' => '{LNG_Name}',
            'maxlength' => 100,
            'value' => isset($user->name) ? $user->name : '',
        ));
        // thumbnail
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'enroll/'.$user->id.'.jpg') ? WEB_URL.DATA_FOLDER.'enroll/'.$user->id.'.jpg' : WEB_URL.'skin/img/noicon.jpg';
        $fieldset->add('file', array(
            'id' => 'thumbnail',
            'labelClass' => 'g-input icon-thumbnail',
            'itemClass' => 'item',
            'label' => '{LNG_Picture of student}',
            'comment' => Language::replace('Straight face photos Wearing a uniform, not wearing a hat and glasses, taken within 6 months, :type type only', array(':type' => 'jpg, jpeg, png')),
            'dataPreview' => 'imgPicture',
            'previewSrc' => $thumb,
            'accept' => array('jpg', 'jpeg', 'png'),
        ));
        $groups = $fieldset->add('groups');
        // id_card
        $groups->add('number', array(
            'id' => 'register_id_card',
            'labelClass' => 'g-input icon-profile',
            'itemClass' => 'width50',
            'label' => '{LNG_Identification No.}',
            'comment' => '{LNG_13 digit identification number}',
            'maxlength' => 13,
            'value' => isset($user->id_card) ? $user->id_card : '',
            'validator' => array('keyup,change', 'checkIdcard', 'index.php/enroll/model/checker/idcard'),
        ));
        // birthday
        $groups->add('date', array(
            'id' => 'register_birthday',
            'labelClass' => 'g-input icon-calendar',
            'itemClass' => 'width50',
            'label' => '{LNG_Birthday}',
            'value' => isset($user->birthday) ? $user->birthday : null,
        ));
        $groups = $fieldset->add('groups');
        // phone
        $groups->add('number', array(
            'id' => 'register_phone',
            'labelClass' => 'g-input icon-phone',
            'itemClass' => 'width50',
            'label' => '{LNG_Phone}',
            'maxlength' => 10,
            'value' => isset($user->phone) ? $user->phone : '',
        ));
        // email
        $groups->add('email', array(
            'id' => 'register_email',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'width50',
            'label' => '{LNG_Email}',
            'maxlength' => 255,
            'value' => isset($user->email) ? $user->email : '',
        ));
        $groups = $fieldset->add('groups');
        // nationality
        $groups->add('text', array(
            'id' => 'register_nationality',
            'labelClass' => 'g-input icon-world',
            'itemClass' => 'width50',
            'label' => '{LNG_Nationality}',
            'maxlength' => 50,
            'value' => isset($user->nationality) ? $user->nationality : '',
        ));
        // religion
        $groups->add('text', array(
            'id' => 'register_religion',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Religion}',
            'maxlength' => 50,
            'value' => isset($user->religion) ? $user->religion : '',
        ));
        // address
        $fieldset->add('text', array(
            'id' => 'register_address',
            'labelClass' => 'g-input icon-address',
            'itemClass' => 'item',
            'label' => '{LNG_Address}',
            'maxlength' => 150,
            'value' => isset($user->address) ? $user->address : '',
        ));
        $groups = $fieldset->add('groups');
        // district
        $groups->add('text', array(
            'id' => 'register_district',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-location',
            'label' => '{LNG_District}',
            'value' => isset($user->district) ? $user->district : '',
        ));
        // amphur
        $groups->add('text', array(
            'id' => 'register_amphur',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-location',
            'label' => '{LNG_Amphur}',
            'value' => isset($user->amphur) ? $user->amphur : '',
        ));
        $groups = $fieldset->add('groups');
        // province
        $groups->add('text', array(
            'id' => 'register_province',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-location',
            'label' => '{LNG_Province}',
            'value' => isset($user->province) ? $user->province : '',
        ));
        // zipcode
        $groups->add('number', array(
            'id' => 'register_zipcode',
            'labelClass' => 'g-input icon-location',
            'itemClass' => 'width50',
            'label' => '{LNG_Zipcode}',
            'maxlength' => 5,
            'value' => isset($user->zipcode) ? $user->zipcode : '',
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Parent}',
        ));
        $groups = $fieldset->add('groups');
        // father
        $groups->add('text', array(
            'id' => 'register_father',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-customer',
            'label' => '{LNG_Name} {LNG_Father}',
            'value' => isset($user->father) ? $user->father : '',
        ));
        // father_phone
        $groups->add('number', array(
            'id' => 'register_father_phone',
            'labelClass' => 'g-input icon-phone',
            'itemClass' => 'width50',
            'label' => '{LNG_Phone}',
            'maxlength' => 10,
            'value' => isset($user->father_phone) ? $user->father_phone : '',
        ));
        $groups = $fieldset->add('groups');
        // mother
        $groups->add('text', array(
            'id' => 'register_mother',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-customer',
            'label' => '{LNG_Name} {LNG_Mother}',
            'value' => isset($user->mother) ? $user->mother : '',
        ));
        // mother_phone
        $groups->add('number', array(
            'id' => 'register_mother_phone',
            'labelClass' => 'g-input icon-phone',
            'itemClass' => 'width50',
            'label' => '{LNG_Phone}',
            'maxlength' => 10,
            'value' => isset($user->mother_phone) ? $user->mother_phone : '',
        ));
        $groups = $fieldset->add('groups', array(
            'comment' => '{LNG_If living with someone other than the parent while studying}',
        ));
        // parent
        $groups->add('text', array(
            'id' => 'register_parent',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-customer',
            'label' => '{LNG_Name} {LNG_Parent}',
            'value' => isset($user->parent) ? $user->parent : '',
        ));
        // parent_phone
        $groups->add('number', array(
            'id' => 'register_parent_phone',
            'labelClass' => 'g-input icon-phone',
            'itemClass' => 'width50',
            'label' => '{LNG_Phone}',
            'maxlength' => 10,
            'value' => isset($user->parent_phone) ? $user->parent_phone : '',
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Educational background}',
        ));
        $groups = $fieldset->add('groups');
        // original_school
        $groups->add('text', array(
            'id' => 'register_original_school',
            'itemClass' => 'width50',
            'labelClass' => 'g-input icon-office',
            'label' => '{LNG_Original school}',
            'value' => isset($user->original_school) ? $user->original_school : '',
        ));
        // gpa
        $groups->add('number', array(
            'id' => 'register_gpa',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'width50',
            'label' => '{LNG_Grade point average}',
            'data-keyboard' => '0123456789.',
            'value' => isset($user->gpa) ? $user->gpa : '',
        ));
        // enroll
        $fieldset->add('file', array(
            'name' => 'enroll[]',
            'id' => 'enroll',
            'labelClass' => 'g-input icon-upload',
            'itemClass' => 'item',
            'label' => '{LNG_Attach file}',
            'placeholder' => Language::replace('Upload :type files no larger than :size', array(':type' => implode(', ', self::$cfg->enroll_attach_file_typies), ':size' => UploadedFile::getUploadSize())).Language::trans(' ({LNG_Can select multiple files})'),
            'comment' => '{LNG_ENROLL_ATTACH_COMMENT}',
            'dataPreview' => 'previewAttach',
            'multiple' => true,
            'accept' => self::$cfg->enroll_attach_file_typies,
        ));
        if ($user->id > 0) {
            $fieldset->add('div', array(
                'innerHTML' => \Download\Index\Controller::init($user->id, 'enroll', self::$cfg->enroll_attach_file_typies, $login['id']),
            ));
        }
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Enroll}',
        ));
        $fieldset->add('hidden', array(
            'id' => 'register_id',
            'value' => $user->id,
        ));
        // districtID
        $fieldset->add('hidden', array(
            'id' => 'register_districtID',
            'value' => isset($user->districtID) ? $user->districtID : 0,
        ));
        // amphurID
        $fieldset->add('hidden', array(
            'id' => 'register_amphurID',
            'value' => isset($user->amphurID) ? $user->amphurID : 0,
        ));
        // provinceID
        $fieldset->add('hidden', array(
            'id' => 'register_provinceID',
            'value' => isset($user->provinceID) ? $user->provinceID : 0,
        ));
        // Javascript
        $form->script('initEnroll("%s ({LNG_age} %y {LNG_year}, %m {LNG_month} %d {LNG_days})");');
        // คืนค่า HTML

        return $form->render();
    }
}
