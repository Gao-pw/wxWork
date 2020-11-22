<?php
namespace Management\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class StaffForm extends Form implements InputFilterProviderInterface{
	protected $inputFilter;

	public function __construct($college_id){

		parent::__construct('dean');

		//给表添加元素
		$this->add(array(//uid Text
			'name'=>'email',
			'type'=>'Text',
			'attributes'=>array(
				'id'=>'email',
				'placeholder'=>'邮箱',
			),
		));

		$this->add(array(
			'name'=>'staff_id',
			'type'=>'Text',
			'attributes'=>array(
				'id'=>'staff_id',
				'placeholder'=>'工号',
			),
		));

		$this->add(array(
			'name'=>'staff_name',
			'type'=>'Text',
			'attributes'=>array(
				'id'=>'staff_name',
				'placeholder'=>'姓名',
			),
		));

		$this->add(array(
			'name'=>'phone',
			'type'=>'Text',
			'attributes'=>array(
				'id'=>'phone',
				'placeholder'=>'办公电话',
			),
		));
		$this->add(array(
			'name'=>'cellphone',
			'type'=>'Text',
			'attributes'=>array(
				'id'=>'cellphone',
				'placeholder'=>'移动电话',
			),
		));

		$this->add(array(
			'name'=>'title',
			'type'=>'Text',
			'attributes'=>array(
				'id'=>'title',
				'placeholder'=>'职称',

			),
		));

		$this->add(array(
			'name'=>'position',
			'type'=>'Text',
			'attributes'=>array(
				'id'=>'position',
				'placeholder'=>'职位',
			),
		));

		$this->add(array(
			'name'=>'college_id',
			'type'=>'Text',
			'attributes'=>array(
				'id' =>'college_id',
				'placeholder'=>'学院ID',
				'readonly'=>'readonly',
				'value'=>$college_id,
			),
		));

		$this->add(array(//submit列
			'name'=>'submit',
			'type'=>'Submit',
			'attributes'=>array(
				'value'=>'提交',
				'id'=>'submitbutton',
				'class'=>'btn btn-large btn-primary',
			),
		));
	}

	public function getInputFilterSpecification(){
		return array(
			'college_id'=>array(
				'required'	=> true,	//必需的
				'filters'	=> array(
					array('name'=>'StripTags'),	//过滤html标签
					array('name'=>'StringTrim'),	//过滤空格
				),
				'validators'=>array(
					array('name'=>'NotEmpty'),
					array(
						'name'=>'StringLength',	//验证长度
						'options'=>array(
							'encoding'	=> 'UTF-8',
							'min'		=> 0,
							'max'		=> 16,
						),
					),
					//array('name'    => 'EmailAddress'),
				),
			),
			'staff_name'=>array(
				'required'	=> true,	//必需的
				'filters'	=> array(
					array('name'=>'StripTags'),	//过滤html标签
					array('name'=>'StringTrim'),	//过滤空格
				),
				'validators'=>array(	//验证器
					array(
						'name'		=>'StringLength',	//验证长度
						'options'	=>array(
							'encoding'	=> 'UTF-8',
							'min'		=> 1,
							'max'		=> 18,
						),
					),//其它验证
				),
			),
			'staff_id'=>array(
				'required'	=> true,	//必需的
				'filters'	=> array(
					array('name'=>'StripTags'),	//过滤html标签
					array('name'=>'StringTrim'),	//过滤空格
				),
				'validators'=>array(	//验证器
					array(
						'name'		=>'StringLength',	//验证长度
						'options'	=>array(
							'encoding'	=> 'UTF-8',
							'min'		=> 1,
							'max'		=> 18,
						),
					),//其它验证
				),
			),
		);
	}
}