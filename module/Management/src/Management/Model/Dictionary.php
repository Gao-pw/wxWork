<?php


namespace Management\Model;


use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Dictionary implements InputFilterAwareInterface
{

    public $id;
    public $key;
    public $value;
    public $update_time;

    public function exchangeArray($data){
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->key = !empty($data['key']) ? $data['key'] : null;
        $this->value = !empty($data['value']) ? $data['value'] : null;
        $this->update_time = !empty($data['update_time']) ? $data['update_time'] : date('Y-m-d H:i:s');
    }

    public function getArrayCopy(){
        return get_object_vars($this);
    }

    /**
     * @inheritDoc
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        // TODO: Implement setInputFilter() method.
    }

    /**
     * @inheritDoc
     */
    public function getInputFilter()
    {
        // TODO: Implement getInputFilter() method.
    }
}