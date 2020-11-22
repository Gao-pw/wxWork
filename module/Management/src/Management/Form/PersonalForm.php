<?php
namespace Management\Form;


use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Identical\EmailAddress;

class PersonalForm extends Form implements InputFilterProviderInterface
{
    protected $inputFilter;
    public function __construct($roles,$search_college_arr)
    {
        parent::__construct('new-account');
        $this->setAttribute('method','post');
        $this->setAttribute('class','form-horizontal');

        $this->add(array(
            'name'=>'Uid',
            'attributes' => array(
                'type'  => 'hidden',
                'id' => 'Uid',
            ),
        ));

        $this->add(array(
            'name'=>'Uname',
            'options' => array(
                'label' => '账号',
            ),
            'attributes' => array(
                'type'  => 'text',
                'id'=>'Uname',
                'class'=>'form-control',
                'placeholder' => '请使用老师邮箱作为账号'
            ),
        ));

        $this->add(array(
            'name' => 'Realname',
            'options' => array(
                'label' => '真实姓名',
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'Realname',
                'class' => 'form-control',
            ),
        ));

        $this->add(array(
            'name'=>'Password',
            'options' => array(
                'label' => '密码',
            ),
            'attributes' => array(
                'type'  => 'password',
                'class'=>'form-control',
                'placeholder' => '密码由6-24个数字或字母组成'
            ),
        ));

        $this->add(array(
            'name'=>'Password2',
            'options' => array(
                'label' => '确认密码',
            ),
            'attributes' => array(
                'type'  => 'password',
                'class'=>'form-control',
            ),
        ));

        $this->add(array(
            'name'=>'Mobile',
            'options' => array(
                'label' => '移动电话',
            ),
            'attributes' => array(
                'type'  => 'text',
                'id'=>'Mobile',
                'class'=>'form-control',
                'placeholder' => '请填入11位数字号码'
            ),
        ));

        $this->add(array(
            'name'=>'Email',
            'options' => array(
                'label' => '电子邮箱',
            ),
            'attributes' => array(
                'type'  => 'text',
                'id'=>'Email',
                'class'=>'form-control',
                'placeholder' => '例：xxx@xxx.xxx'
            ),
        ));

        $this->add(array(
            'name' => 'YXSM',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'YXSM',
                'class'=>'form-control',
            ),
            'options'=>array(
                'label' => '院系所',
                'empty_option' => '请选择',
                'value_options' => $search_college_arr,
            ),
        ));

        $this->add(array(
            'name'=>'Rid',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'Rid',
                'class'=>'form-control',
            ),
            'options'=>array(
                'label' => '用户角色',
                'empty_option' => '请选择',
                'value_options'=> $roles,
            ),
        ));


        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => '提交',
                'id' => 'submitbutton',
                'class'=>'btn btn-primary',
            ),
        ));

    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add(array(
                'name'     => 'Uname',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),//去除前后空格
                ),
                'validators' => array(
                    array('name' => 'NotEmpty'),
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 50,
                        ),
                    ),
                ),
            ));
            $inputFilter->add(array(
                'name'     => 'Realname',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 20,
                        ),
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name'		=> 'Password',
                'required'	=> true,		//必需的
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 6,
                            'max'      => 24,
                        ),
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name'		=> 'Password2',
                'required'	=> true,		//必需的
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 6,
                            'max'      => 24,
                        ),
                    ),
                    array(
                        'name'    => 'Identical',//检验是否和password1相同，Zend\Validator\Identical可以用于检验另一个元素和自己是否相同
                        'options' => array(
                            'token' => 'Password',
                        ),
                    )
                ),
            ));

            $inputFilter->add(array(
                'name'		=> 'Mobile',
                'required'	=> true,		//必需的
                'validators' => array(
                    array('name' => 'NotEmpty'),		//不允许为空
                    array(
                        'name' => 'StringLength',//限制长度
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 11,
                            'max'      => 11,
                        ),
                    ),
                    array('name' => 'Digits')//必须为数字字符
                ),
            ));

            $inputFilter->add(array(
                'name'		=> 'Email',
                'required'	=> true,		//必需的
                'validators' => array(
                    array('name' => 'NotEmpty'),		//不允许为空
                    array(
                        'name' => 'StringLength',//限制长度
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 256,
                        ),
                    ),
//                    array('name' => 'EmailAddress'),//检验邮箱格式，Zend\Validator\Identical\EmailAddress专门用于检验邮箱格式
                ),
            ));

            $inputFilter->add(array(
                'name'		=> 'YXSM',
                'required'	=> true,		//必需的
                'validators' => array(
                    array('name' => 'NotEmpty'),		//不允许为空
                ),
            ));

            $inputFilter->add(array(
                'name'		=> 'Rid',
                'required'	=> true,		//必需的
                'validators' => array(
                    array('name' => 'NotEmpty'),		//不允许为空
                ),
            ));
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

    public function getInputFilterSpecification(){
    }
}