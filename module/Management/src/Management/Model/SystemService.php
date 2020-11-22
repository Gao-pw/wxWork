<?php
/**
 * @author gpw
 */

namespace Management\Model;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

/**
 * Class SystemService
 * @package Management\Model
 */
class SystemService
{

    /**
     * 获取session信息
     * @return array
     */
    public function getSession(){
        $nameSession = new Container('username');
        $username = $nameSession->item;

        $idSession = new Container('user_id');
        $user_id =  $idSession->item;

        $typeSession = new Container('user_type');
        $user_type = $typeSession->item;

        if(empty($username)){
            exit("请先登录！");
        }

        return array(
            'username'=>$username,
            'user_id'=>$user_id,
            'user_type'=>$user_type,
        );
    }

}