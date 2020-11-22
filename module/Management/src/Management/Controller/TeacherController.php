<?php
/**
 * @author gpw
 * 教师管理
 *
 * 2020年8月6日17:21:27
 * 暂用
 * @todo 改成xly模式
 */

namespace Management\Controller;


use Basicinfo\Model\PermissionControll;
use Basicinfo\Model\Staff;
use Management\Form\StaffForm;
use Management\Model\SystemService;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

class TeacherController extends AbstractActionController
{

    private $uid;//user id
    private $cid;//college id
    private $rid;//role id

    //数据库静态变量
    private $staffTable;//base_staff table
    private $usrteacherTable;//user_teacher

    /**
     * TeacherController constructor.
     * @todo 权限控制：学院或者研究生院增加老师
     * @global $uid：用户id
     * @global $cid：用户学院id
     * @global $rid：用户权限
     */
    public function __construct(){
        $permissionControl = new PermissionControll();
//        $permissionControl->judgePermission();

        /* 用户id */
        $container = new Container('uid');
        $this->uid = $container->item;

        /* 用户权限 */
        $containerrid = new Container('rid');
        $this->rid = $containerrid->item;

        /* 用户所属学院 */
        $collegeId = new Container('college_id');
        $this->cid = $collegeId->item;

        if(empty($this->uid)){
            echo "<script> alert('尚未登录，无权访问！');window.location.href='/info';</script>";
        }

        
        if(in_array(10,$this->rid)||in_array(9,$this->rid)||in_array(11,$this->rid)){
            return true;
        }else{
            echo "<script> alert('无权访问！');window.location.href='/info';</script>";
        }
    }


    //////////////所用action////////

    /**
     * 显示所有的学院以及学科负责人
     * 用户仅为研究生院(权限：10)
     * @todo 权限优化
     * @todo staff作用未知，后续优化
     */
    public function ShowAllTeacherAction(){

        $adapter = $this->dbAdapter();

        /*
         * 不具备权限跳转到文章列表
         */
        if(!in_array(10,$this->rid)){
            echo "<script>alert('您没有当前权限')</script>";
            echo "<script type=\"text/javascript\">window.location.replace('/info/article/articleList');</script>";
            return false;
        }

        $sql = new Sql($adapter);
        $sl = new Select();

        //inner join ,作用不明（sql的意思是找到staff和teacher表中id一样的老师）
        $sl->from(array('staff' => 'base_staff'))
            ->join(array('u' => 'usr_teacher'), 'u.staff_id = staff.staff_id');
        $statement = $sql->prepareStatementForSqlObject($sl);
        $resultset = $statement->execute();
        $staff = iterator_to_array($resultset);//staff传到前端并没有实际使用。

        //从base_subject表中找到老师的信息并进行展示
        $sr = new Select();
        $l = "SELECT `c`.*, `s`.`staff_id` AS `dean_id`, `s`.`staff_name` AS `dean_name`, `s`.`email` AS `dean_email`, `bs`.`staff_id` AS `manager_id`, `bs`.`staff_name` AS `manager_name`, `bs`.`email` AS `manager_email` FROM `base_college` AS `c` LEFT JOIN `base_staff` AS `s` ON `c`.`dean_id` = `s`.`staff_id` LEFT JOIN `base_staff` AS `bs` ON `c`.`manager_id` = `bs`.`staff_id`";
        $resultset = $adapter->query($l)->execute();
        $college = iterator_to_array($resultset);
        return array('staff' => $staff, 'college' => $college);
    }

    /**
     * 添加老师action
     * get:显示当前学科所有用户
     * post:数据库操作
     */
    public function adddeanAction(){
        $cidParam = $this->getParam1();
        $adapter = $this->dbAdapter();
        $staff = $this->getStaffTable()->getStaffByCid($cidParam)->toArray();//根据学科id查找所有老师
        $form = new StaffForm($cidParam);

        if($this->getRequest()->isPost()){
            $postData = array_merge_recursive(
                $this->getRequest()->getPost()->toArray()
            );
            //把接收到的值赋给表单，用表单的过滤器来验证数据有效性
            $form->setData($postData);
            //var_dump($postData);

            if ($form->isValid()) {//数据有效

                //保存表单
                $filteredValues = $form->getInputFilter()->getValues();

                $staff = new Staff();
                $data = array(
                    'staff_id' => $filteredValues["staff_id"],
                    'staff_name' => $filteredValues["staff_name"],
                    'college_id' => $filteredValues["college_id"],
                    'title' => $filteredValues["title"],
                    'phone' => $filteredValues["phone"],
                    'cellphone' => $filteredValues["cellphone"],
                    'email' => $filteredValues["email"],
                    'position' => $filteredValues["position"],
                );
                $staff->exchangeArray($data);//初始化
                $re = $this->getStaffTable()->saveStaff($staff);

                if ($re) {
                    echo "<script>alert('添加成功');</script>";
                } else {
                    echo "<script>alert('修改无变化');</script>";
                }
                $url = "/adddean/".$filteredValues['college_id'];
                echo "<script type='text/javascript'>location.href=' .$url '</script>";
                return false;
                //return $this->redirect()->toRoute('management/default', array('controller'=>'teacher','action'=>'adddean','param1' => $filteredValues["college_id"]));
            }
        }else{
            if(!in_array(10,$this->rid)&&$cidParam!=$this->cid){
                echo "<script>alert('您只能修改您所属学院的用户')</script>";
                echo "<script type=\"text/javascript\">window.location.replace('/info/article/articleList');</script>";
                return false;
            }
        }

        return array('form' => $form, 'staff' => $staff);
    }

    /**
     * 查找数据库填充数据
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function fillingMessageAction(){
        $staffId = $this->getParam1();
        $staff = $this->getStaffTable()->getStaff($staffId);
        $response = $this->getResponse();
        $response->setContent(json_encode(array('staff' => $staff)));
        return $response;
    }

    /**
     * 删除用户
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function deleteAction(){
        $uid = $this->getParam1();
        $college_id = $this->getParam2();
        $staff = $this->getStaffTable()->getStaff($uid);
        $staffRe = $this->getStaffTable()->deletestaff($uid);
        $response = $this->getResponse();

        if($staff){
            $userRe = $this->getUsrteacherTable()->deleteUser($staff->staff_id);
            if($userRe){
                $response->setContent(json_encode(array('msg'=>'删除成功！')));
            }else{
                $response->setContent(json_encode(array('msg'=>'部分删除失败，请检查teacher表中是否有该老师数据！')));
            }
        }else{
            $response->setContent(json_encode(array('msg' => '删除失败')));
        }
        return $response;
    }


    /////////////方法////////////////

    /**
     * 获取adapter
     * @return array|object
     */
    public function dbAdapter(){
        return $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    }

    /**
     * 获取路由中param1的值
     * @return mixed
     */
    public function getParam1(){
        return $this->params()->fromRoute('param1');
    }

    /**
     * 获取路由中的param2的值
     * @return mixed
     */
    public function getParam2(){
        return $this->params()->fromRoute('param2');
    }


    //////////数据库配置//////////////

    /**
     * 获取base_staff表
     * @return array|object
     */
    public function getStaffTable()
    {
        if (!$this->staffTable) {
            $sm = $this->getServiceLocator();
            $this->staffTable = $sm->get('Basicinfo\Model\StaffTable');
        }
        return $this->staffTable;
    }

    /**
     * 获取user_teacher表
     * @return array|object
     */
    public function getUsrteacherTable(){
        if(!$this->usrteacherTable){
            $um = $this->getServiceLocator();
            $this->usrteacherTable = $um->get('User\Model\UserTeacherTable');
        }
        return $this->usrteacherTable;
    }


}