<?php


namespace Management\Model;


use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Container;

class UserTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tg){
        $this->tableGateway = $tg;
    }

    /**
     * 写入session
     * @param $row
     */
    private function writeSession($row){
        $containerCol = new Container('user_id');
        $containerCol->item = $row->user_id;
        $containerUname = new Container('username');
        $containerUname->item = $row->username;

        $containerStaffid = new Container('user_type');
        $containerStaffid->item = (int)$row->user_type;
    }

    /**
     * 登录
     * @param $username
     * @return int
     */
    public function login($username){
        $rowSet  = $this->tableGateway->select(array('username'=>$username));
        $row = $rowSet->current();
        if(!$row){
            return 2;
            //throw new \Exception("could not find row");
        }else{
            $this->writeSession($row);
            return 1;
        }
    }
}