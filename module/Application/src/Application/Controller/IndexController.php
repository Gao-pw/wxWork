<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    //数据库操作
    public function getAdapter(){
        return $this->serviceLocator->get('Zend\Db\Adapter\Adapter');
    }

    //json操作
    public function ReturnResult($array){
        $response = $this->getResponse();
        $response->setContent(json_encode($array));
        return $response;
    }

    /**
     * 登录操作
     * 100：登录成功
     * 101：密码错误
     * 102：用户名错误
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function LoginAction(){
        $code = "";
        $mes = "";
        $request = $this->getRequest();
        if($request->isPost()){
            $name = $_POST['name'];
            $password = $_POST['password'];
            $adapter = $this->getAdapter();
            $sq1 = "select * from an_user where username ='".$name."';";
            $re = $adapter->query($sq1)->execute();
            $result = iterator_to_array($re);
            if(count($result)>0){
                $password_db = $result[0]['password'];
                if (!strcmp($password,$password_db)){
                    $code = 100;
                    $mes = "登录成功";
                }else{
                    $code = 101;
                    $mes = "密码错误";
                }
            }else{
                $code = 102;
                $mes = "用户名错误";
            }
            $response = $this->getResponse();
            $response->setContent(json_encode(array('code' => $code,'message'=>$mes)));
            return $response;
        }
    }

    /**
     * 刷新消息数据库
     */
    public function FindMessageAction(){
        $adapter = $this->getAdapter();
        $sql ="select * from an_message";
        $re = $adapter->query($sql)->execute();
        $result = iterator_to_array($re);
        $num = count($result);
        $code = 201;
        $mes = "查找成功";
        $response = $this->getResponse();
        $response->setContent(json_encode(array('code' => $code,'message'=>$mes,'num'=>$num,'result'=>$result)));
        return $response;
    }

    /**
     * 插入消息数据库
     */
    public function InsertMessageAction(){
        $adapter = $this->getAdapter();
        $request = $this->getRequest();
        if($request->isPost()){
            $address = $_POST["address"];
            $message = $_POST["message"];
            $sql1 = "insert into an_message (address,message) values ('".$address."','".$message."')";
            $resultSet = $adapter->query($sql1)->execute();
            $rows = $resultSet->getAffectedRows();
            if($rows==1){
                $code = 301;
                $mes = "插入成功";
            }else{
                $code = 302;
                $mes = "插入失败";
            }
            return $this->ReturnResult(array('code'=>$code,'message'=>$mes));
        }
    }

    /**
     * 删除某条消息
     */
    public function DeleteMessageAction(){
        $adapter = $this->getAdapter();
        $request = $this->getRequest();
        if($request->isPost()){
            $m_id = $_POST['m_id'];
            $sql = "delete from an_message where message_id ='".$m_id."'";
            $resultSet = $adapter->query($sql)->execute();
            $rows = $resultSet->getAffectedRows();
            if($rows==1){
                $code = 401;
                $mes = "删除成功";
            }else{
                $code = 402;
                $mes = "删除失败";
            }
            return $this->ReturnResult(array('code'=>$code,'message'=>$mes));
        }
    }
}
