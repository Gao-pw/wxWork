<?php
//module/Basicinfo/src/Basicinfo/Form/SubjectForm.php
namespace management\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterProviderInterface;

use Basicinfo\Model\CollegeTable;


class ProfessionForm extends Form implements InputFilterProviderInterface{
	protected $inputFilter;
	//protected $collegeTable;

	//public function __construct(CollegeTable $collegeTable){
	public function __construct($staffArr=array()){
		parent::__construct('profession');
		$this->add(array(
			'name'=>'profession_id',
			'type'=>'Text',
			'options'=>array(
				'label'=>'方向编号',
			),
		));
		$this->add(array(
			'name'=>'profession_name',
			'type'=>'Text',
			'options'=>array(
				'label'=>'方向名称',
			),
		));
        $this->add(array(
            'name'=>'staff_id',
            'type'=>'Select',
            'options'=>array(
                'label'=>'方向负责人',
                'empty_option' => '请选择',
                'value_options' => $staffArr,
            ),
            'attributes'=>array(
                'id'=>'staff_id',
            ),
        ));
		
		$this->add(array(//submit列
			'name'=>'submit',
			'type'=>'Submit',
			'attributes'=>array(
				'value'=>'确认修改方向',
				'id'=>'submitbutton',
				'class'=>'btn btn-primary',
			),
		));
		//$this->add(new Element\Csrf('security'));//防止跨站请求伪造的攻击
	}

	public function getInputFilterSpecification(){
        $id16EmptyFileter = array(
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
            'allow_empty' => true,
        );//可空
		$filterArr = array(
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
		);
		return array(
			// 'cid'=>$filterArr,
			// 'sid'=>$filterArr,
			'profession_id'=>$filterArr,
			'profession_name'=>array(
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
            'staff_id'=>$id16EmptyFileter,
		);
	}
}