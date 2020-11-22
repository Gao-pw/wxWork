<?php


namespace Management\Model;


use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Article implements InputFilterAwareInterface
{

    public $article_id;
    public $article_title;
    public $article_link;
    public $article_date;
    public $article_position;
    public $article_read;

    /**
     * @param $data
     */
    public function exchangeArray($data){
        $this->article_id = !empty($data['article_id'])? $data['article_id'] : null;
        $this->article_title = !empty($data['article_title'])? $data['article_title'] : null;
        $this->article_link = !empty($data['article_link'])? $data['article_link'] : null;
        $this->article_date = !empty($data['article_date'])? $data['article_date'] : null;
        $this->article_position = !empty($data['article_position'])? $data['article_position'] : null;
        $this->article_read = !empty($data['article_read'])? $data['article_read'] : null;
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