<?php
namespace Management\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterProviderInterface;

class CollegeForm extends Form implements InputFilterProviderInterface{
	protected $inputFilter;

	public function __construct(){
		parent::__construct('college');

		//给表添加元素
		$this->add(array(
			'name'=>'cid',
			'type'=>'Text',
			'attributes'=>array(
				'id'=>'new_cid',
			),
			'options'=>array(
				'label'=>'学院编号',
			),
		));
		$this->add(array(
			'name'=>'cname',
			'type'=>'Text',
			'attributes'=>array(
				'id'=>'new_cname',
			),
			'options'=>array(
				'label'=>'学院名称',
			),
		));
        $this->add(array(
            'name'=>'dean',
            'type'=>'Text',
            'attributes'=>array(
                'id'=>'dean',
            ),
            'options'=>array(
                'label'=>'院秘书',
                // 'empty_option' => '请选择',
                // 'value_options' => $staffArr,
            ),
        ));

        $this->add(array(
            'name'=>'manager',
            'type'=>'Text',
            'attributes'=>array(
                'id'=>'manager',
            ),
            'options'=>array(
                'label'=>'院长',
                // 'empty_option' => '请选择',
                // 'value_options' => $staffArr,
            ),
        ));

        $this->add(array(
            'name'=>'tstu',
            'type'=>'number',
            'attributes'=>array(
                'id'=>'tstu',
            ),
            'options'=>array(
                'label'=>'总招生数',
                // 'empty_option' => '请选择',
                // 'value_options' => $staffArr,
            ),
        ));

        $this->add(array(
            'name'=>'fstu',
            'type'=>'number',
            'attributes'=>array(
                'id'=>'fstu',
            ),
            'options'=>array(
                'label'=>'推免数',
                // 'empty_option' => '请选择',
                // 'value_options' => $staffArr,
            ),
        ));

		$this->add(array(//submit列
			'name'=>'submit',
			'type'=>'Submit',
			'attributes'=>array(
				'value'=>'修改学院信息',
				'id'=>'submitbutton',
				'class'=>'btn btn-primary',
			),
		));
		//$this->add(new Element\Csrf('security'));//防止跨站请求伪造的攻击
	}

	public function getInputFilterSpecification(){
		return array(
			'cid'=>array(
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
							'min'		=> 1,
							'max'		=> 16,
						),
					),
				),
			),
			'cname'=>array(
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
							'max'		=> 20,
						),
					),//其它验证
				),
			),
			'dean'=>array(
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
							'max'		=> 20,
						),
					),//其它验证
				),
                'allow_empty' => true,
			),

			'manager'=>array(
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
							'max'		=> 20,
						),
					),//其它验证
				),
                'allow_empty' => true,
			),

            'tstu'=>array(
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
                            'max'		=> 20,
                        ),
                    ),//其它验证
                ),
                'allow_empty' => true,
            ),

            'fstu'=>array(
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
                            'max'		=> 20,
                        ),
                    ),//其它验证
                ),
                'allow_empty' => true,
            ),
		);
	}
}