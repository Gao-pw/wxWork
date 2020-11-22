<?php
//module/Basicinfo/src/Basicinfo/Form/TeacherForm.php
namespace Management\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class TeacherForm extends Form implements InputFilterProviderInterface{
	protected $inputFilter;
	//protected $collegeTable;

	//public function __construct(CollegeTable $collegeTable){
	public function __construct($staffArr,$subjectArr){
		parent::__construct('teacher');
		$this->add(array(
			'name' => 'tid',
			'type' => 'Select',
			'options' =>array(
				'label' => '导师',
				'empty_option' =>'请选择导师',
				'value_options' => $staffArr,
			),
			'attributes'=>array(
				'id'=>'tid',
			),
		));
		$this->add(array(
			'name' => 'sid1',
			'type' => 'Select',
			'options' =>array(
				'label' => '学术型硕士学科',
				'empty_option' =>'请选择学科',
				'value_options' => $subjectArr,
			),
			'attributes'=>array(
				'id'=>'sid1',
			),
		));
        $this->add(array(
            'name' => 'full_time1',
            'type' => 'Select',
            'options' =>array(
                'label' => '学术型硕士学习方式',
                'value_options' => array(
                    '1'=>'全日制',
                    '2'=>'非全日制'
                ),
            ),
            'attributes'=>array(
                'id'=>'full_time1',
            ),
        ));
		$this->add(array(
			'name' => 'sid2',
			'type' => 'Select',
			'options' =>array(
				'label' => '专业型硕士学科',
				'empty_option' =>'请选择学科',
				'value_options' => $subjectArr,
			),
			'attributes'=>array(
				'id'=>'sid2',
			),
		));
        $this->add(array(
            'name' => 'full_time2',
            'type' => 'Select',
            'options' =>array(
                'label' => '专业型硕士学习方式',
                'value_options' => array(
                    '1'=>'全日制',
                    '2'=>'非全日制'
                ),
            ),
            'attributes'=>array(
                'id'=>'full_time2',
            ),
        ));
		$this->add(array(
			'name'=>'pid11',
			'type'=>'Select',
			'options'=>array(
				'label'=>'学术型方向',
				'disable_inarray_validator' => true,//使之可以使用option以外的选项作为值，之后不会被过滤器拦截
				'empty_option' =>'请选择方向',
				//'value_options' => array(),
			),
			'attributes'=>array(
				'id'=>'pid11',
			),
		));
		$this->add(array(
			'name'=>'pid12',
			'type'=>'Select',
			'options'=>array(
				'label'=>'学术型方向',
				'disable_inarray_validator' => true,
				'empty_option' =>'请选择方向',
				//'value_options' => array(),
			),
			'attributes'=>array(
				'id'=>'pid12',
			),
		));
		$this->add(array(
			'name'=>'pid13',
			'type'=>'Select',
			'options'=>array(
				'label'=>'学术型方向',
				'disable_inarray_validator' => true,
				'empty_option' =>'请选择方向',
				//'value_options' => array(),
			),
			'attributes'=>array(
				'id'=>'pid13',
			),
		));
		$this->add(array(
			'name'=>'pid21',
			'type'=>'Select',
			'options'=>array(
				'label'=>'专业型方向',
				'disable_inarray_validator' => true,
				'empty_option' =>'请选择方向',
				//'value_options' => array(),
			),
			'attributes'=>array(
				'id'=>'pid21',
			),
		));
		$this->add(array(
			'name'=>'pid22',
			'type'=>'Select',
			'options'=>array(
				'label'=>'专业型方向',
				'disable_inarray_validator' => true,
				'empty_option' =>'请选择方向',
				//'value_options' => array(),
			),
			'attributes'=>array(
				'id'=>'pid22',
			),
		));
		$this->add(array(
			'name'=>'pid23',
			'type'=>'Select',
			'options'=>array(
				'label'=>'专业型方向',
				'disable_inarray_validator' => true,
				'empty_option' =>'请选择方向',
				//'value_options' => array(),
			),
			'attributes'=>array(
				'id'=>'pid23',
			),
		));
		$this->add(array(//submit列
			'name'=>'submit',
			'type'=>'Submit',
			'attributes'=>array(
				'value'=>'确认添加导师',
				'id'=>'submitbutton',
				'class'=>'btn btn-primary',
			),
		));
		//$this->add(new Element\Csrf('security'));//防止跨站请求伪造的攻击
	}

	public function getInputFilterSpecification(){
		$filterArrNoEmpty = array(
			'required'	=> true,	//必需的
			'filters'	=> array(
				array('name'=>'StripTags'),	//过滤html标签
				array('name'=>'StringTrim'),	//过滤空格
			),
			'validators'=>array(
				//array('name'=>'NotEmpty'),

				array(
					'name'=>'StringLength',	//验证长度
					'options'=>array(
						'encoding'	=> 'UTF-8',
						'min'		=> 1,
						'max'		=> 16,
					),
				),
			),
			'allow_empty' => false,
		);
		$filterArrAllowEmpty = array(
			'required'	=> false,	//必需的
			'filters'	=> array(
				array('name'=>'StripTags'),	//过滤html标签
				array('name'=>'StringTrim'),	//过滤空格
			),
			'validators'=>array(
				//array('name'=>'NotEmpty'),

				array(
					'name'=>'StringLength',	//验证长度
					'options'=>array(
						'encoding'	=> 'UTF-8',
						'min'		=> 1,
						'max'		=> 16,
					),
				),
			),
			'allow_empty' => true,
		);
		return array(
			'tid'=>$filterArrNoEmpty,
			'sid1'=>$filterArrAllowEmpty,
			'full_time1'=>$filterArrAllowEmpty,
			'sid2'=>$filterArrAllowEmpty,
            'full_time2'=>$filterArrAllowEmpty,
			'pid11'=>$filterArrAllowEmpty,
			'pid12'=>$filterArrAllowEmpty,
			'pid13'=>$filterArrAllowEmpty,
			'pid21'=>$filterArrAllowEmpty,
			'pid22'=>$filterArrAllowEmpty,
			'pid23'=>$filterArrAllowEmpty,
		);
	}
}