<?php
namespace Management\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\File\Extension;  
use Zend\Validator\File\Size;

class resultForm extends Form implements InputFilterProviderInterface{
    public function __construct($name = null)
    {

        parent::__construct('upload_file');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'upload_file',
            'type' => 'File',
            'attributes' => array(
                'id' => 'file',
                'class' => 'fileinput',
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => '上传并更新',
                'class' => 'btn btn-default',
            ),
        ));

    }

    public function getInputFilterSpecification(){
        $extention = new Extension('csv');
        $size = new Size(array('max'=>'8MB'));

        return array(
            'upload_file'=>array(
                'required' => true,
                'validators' => array(
                    $extention,
                    $size,
                ),
            ),
        );
    }
}