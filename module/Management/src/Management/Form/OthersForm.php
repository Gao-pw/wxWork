<?php

namespace Management\Form;

use Zend\Form\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Identical\EmailAddress;


class OthersForm extends Form implements InputFilterProviderInterface
{
    protected $inputFilter;
    public function __construct($roles,$search_college_arr)
    {
        parent::__construct('new-account');
        $this->setAttribute('method','post');
        $this->setAttribute('class','form-horizontal');

//        $this->add(array(
//            'name'=>'Uid',
//            'attributes' => array(
//                'type'  => 'hidden',
//                'id' => 'Uid',
//            ),
//        ));

        $this->add(array(
            'name' => 'Staffid',
            'options' => array(
                'label' => '用户编号'
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'Staffid',
                'class'=>'form-control',
                'readonly'=>true,
            )
        ));

        $this->add(array(
            'name' => 'Uname',
            'options' => array(
                'label' => '用户名'
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'Uname',
                'class'=>'form-control',
                'readonly'=>true,
            )
        ));

        $this->add(array(
            'name' => 'Realname',
            'options' => array(
                'label' => '姓名'
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'Realname',
                'class' => 'form-control',
                'readonly'=>true,
            )
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
                'value_options'=>$roles,
            ),
        ));
    }
    public function getInputFilter(){
        if(!$this->inputFilter){
            $inputFilter = new InputFilter();
            $inputFilter->add(array(
                'name'		=> 'Staffid',
                'required'	=> true,		//必需的
                'validators' => array(
                    array('name' => 'NotEmpty'),		//不允许为空
                ),
            ));

            $inputFilter->add(array(
                'name'		=> 'Uname',
                'required'	=> true,		//必需的
                'validators' => array(
                    array('name' => 'NotEmpty'),		//不允许为空
                ),
            ));

            $inputFilter->add(array(
                'name'		=> 'Realname',
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