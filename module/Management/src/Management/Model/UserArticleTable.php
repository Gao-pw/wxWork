<?php


namespace Management\Model;


use Zend\Db\TableGateway\TableGateway;

class UserArticleTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tg){
        $this->tableGateway = $tg;
    }

    /**
     * 插入数据库
     * @param $article_id
     * @param $user_id
     */
    public function insertData($article_id,$user_id){
        $data = array(
            'user_id'=>$user_id,
            'article_id'=>$article_id,
        );
        $rowSet = $this->tableGateway->select($data);
        $row = $rowSet->current();
        if(!$row){
            $this->tableGateway->insert($data);
        }
    }

    /**
     * 删除某一数据
     * @param $article_id
     * @param $user_id
     */
    public function  deleteData($article_id,$user_id){
        $data = array(
            'user_id'=>$user_id,
            'article_id'=>$article_id,
        );
        $rowSet = $this->tableGateway->select($data);
        $row = $rowSet->current();
        if(!$row){
            $this->tableGateway->delete($data);
        }
    }
}