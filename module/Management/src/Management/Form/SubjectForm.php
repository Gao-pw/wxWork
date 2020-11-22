<?php
//module/Basicinfo/src/Basicinfo/Form/SubjectForm.php
namespace Management\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Captcha\AdapterInterface as CaptchaAdapter;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterProviderInterface;

use Basicinfo\Model\CollegeTable;


class SubjectForm extends Form implements InputFilterProviderInterface{
	protected $inputFilter;
	protected $collegeTable;

	//public function __construct(CollegeTable $collegeTable){
	public function __construct($userPermission,$cid,$staffArr){

		parent::__construct('subject');
		//给表添加元素
		$this->add(array(
			'name'=>'college_id',//学院编号
			'type'=>'Hidden',
			'attributes'=>array(
				'value'=>$cid,
                'id'=>'college_id',
			),
		));
		$this->add(array(
			'name'=>'subject_id',
			'type'=>'Text',
			'options'=>array(
				'label'=>'学科编号',
			),
			'attributes'=>array(
				//'placeholder'=>'学科编号',
				'id'=>'subject_id',
                'readonly'=> $userPermission,
			),
		));

        $this->add(array(
            'name'=>'full_time',
            'type'=>'Select',
            'options'=>array(
                'label'=>'学习方式',
                'value_options' => array(
                    '1'=>'全日制',
                    '2'=>'非全日制',
                ),
            ),
            'attributes'=>array(
                'id'=>'full_time',
            //    'disabled'=> $userPermission,
            //    'display'=>"none",
            ),
        ));
		$this->add(array(
			'name'=>'subject_name',
			'type'=>'Text',
			'options'=>array(
				'label'=>'学科名称',
			),
			'attributes'=>array(
				//'placeholder'=>'学科名称',
				'id'=>'subject_name',
                'readonly'=> $userPermission,
			),
		));
		$this->add(array(
			'name'=>'staff_id',
			'type'=>'Select',
			'options'=>array(
				'label'=>'学科负责人',
				'empty_option' => '请选择',
				'value_options' => $staffArr,
			),
			'attributes'=>array(
				'id'=>'staff_id',
			),
		));
		$this->add(array(
			'name'=>'total_stu',
			'type'=>'Text',
			'options'=>array(
				'label'=>'总招生数',
			),
			'attributes'=>array(
				'id'=>'total_stu',
                'readonly'=> $userPermission,
			),
		));
		$this->add(array(
			'name'=>'free_stu',
			'type'=>'Text',
			'options'=>array(
				'label'=>'推免人数',
                'readonly'=> $userPermission,
			),
			'attributes'=>array(
				'id'=>'free_stu',
                'readonly'=> $userPermission,
			),
		));
		
//		$this->add(array(
//			'name'=>'course1',
//			'type'=>'Text',
//			'options'=>array(
//				'label'=>'政治',
//				//'empty_option' => '101政治',
//				//'value_options' => $course1Arr,
//			),
//			'attributes'=>array(
//				'id'=>'course1',
//				//'placeholder'=>'政治',
//			),
//		));
//
//		$this->add(array(
//			'name'=>'course2',
//			'type'=>'Text',
//			'options'=>array(
//				'label'=>'外语',
//			),
//			'attributes'=>array(
//				'id'=>'course2',
//				//'placeholder'=>'外语',
//			),
//		));
//
//		$this->add(array(
//			'name'=>'course3',
//			'type'=>'Text',
//			'options'=>array(
//				'label'=>'专业课1',
//			),
//			'attributes'=>array(
//				'id'=>'course3',
//			),
//		));
//
//		$this->add(array(
//			'name'=>'course4',
//			'type'=>'Text',
//			'options'=>array(
//				'label'=>'专业课2',
//			),
//			'attributes'=>array(
//				'id'=>'course4',
//			),
//		));

		$this->add(array(
			'name'=>'remark',
			'type'=>'Textarea',
			'options'=>array(
				'label'=>'备注',
			),
			'attributes'=>array(
				'id'=>'remark',
                'readonly'=> $userPermission,
			),
		));

		// $this->add(array(
		// 	'name'=>'LHPYDWM',
		// 	'type'=>'Text',
		// 	'options'=>array(
		// 		'label'=>'联合培养单位码',
		// 	),
		// ));
		// $this->add(array(
		// 	'name'=>'LHPYDW',
		// 	'type'=>'Text',
		// 	'options'=>array(
		// 		'label'=>'联合培养单位',
		// 	),
		// ));
		$this->add(array(
			'name'=>'XZ',
			'type'=>'Text',
			'options'=>array(
				'label'=>'学制',
			),
			'attributes'=>array(
				'class'=>'mini_input',
				'id'=>'XZ',
                'readonly'=> $userPermission,
			),
		));

		$this->add(array(//submit列
			'name'=>'submit',
			'type'=>'Submit',
			'attributes'=>array(
				'value'=>'确认修改学科',
				'id'=>'submitbutton',
				'class'=>'btn btn-primary',
			),
		));
		//$this->add(new Element\Csrf('security'));//防止跨站请求伪造的攻击
	}

	public function getInputFilterSpecification(){
		$id16Fileter = array(
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
		);//不可空
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
		$string20Fileter = array(
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
						'max'		=> 20,
					),
				),
			),
		);//不可空
		$string128Fileter = array(
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
						'max'		=> 128,
					),
				),
			),
            'allow_empty' => true,
		);//可空
		$intFilter = array(
			'required'	=> true,	//必需的
			'filters'	=> array(
				array('name' => 'Int'),
			),
		);//不可空
		$emptyFilter = array(
			'required'	=> true,	//必需的
			'filters'	=> array(
				array('name' => 'Int'),
			),
			'allow_empty' => true,
		);//可空的int
		return array(
			'college_id'=>$id16Fileter,//不可空
			'subject_id'=>$id16Fileter,//不可空
			'subject_name'=>$string20Fileter,//不可空
			'staff_id'=>$id16EmptyFileter,//可空
			'total_stu'=>$intFilter,//不可空
			'free_stu'=>$intFilter,//不可空
//			'course1'=>$string128Fileter,//可空
//			'course2'=>$string128Fileter,//可空
//			'course3'=>$string128Fileter,//可空
//			'course4'=>$string128Fileter,//可空
			'remark'=>array(//可空
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
                            'max'		=> 128,
                        ),
                    ),
                ),
                'allow_empty' => true,
            ),//可空
			// 'LHPYDWM'=>$id16EmptyFileter,
			// 'LHPYDW'=>$id16EmptyFileter,
			'XZ'=>$intFilter,//可空
            'full_time'=>$intFilter,//不可空
		);
	}
}