<?php


namespace Management\Model;


use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class UserArticle implements InputFilterAwareInterface
{

    public $user_id;
    public $article_id;

    public function getArrayCopy(){
        return get_object_vars($this);
    }

    public function exchangeArray($data){
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->article_id = !empty($data['article_id']) ? $data['article_id'] : null;
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