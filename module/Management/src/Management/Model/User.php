<?php


namespace Management\Model;


use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class User implements InputFilterAwareInterface
{

    public $username;
    public $user_id;
    public $user_type;

    public function exchangeArray($data){
        $this->username = !empty($data['username']) ? $data['username'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->user_type = !empty($data['user_type']) ? $data['user_type'] : null;
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