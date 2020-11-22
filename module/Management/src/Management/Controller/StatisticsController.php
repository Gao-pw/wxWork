<?php
/**
 * e-charts 数据展示
 * @author gpw
 * @version 1.0
 */

namespace Management\Controller;


use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class StatisticsController extends AbstractActionController
{
    /**
     * @var 参数
     */
    private $collegeTable;
    private $rid;

    /**
     * 调用数据库
     * @return array|object
     */
    private function dbAdapter(){
        return $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    }

    /**
     * controller 构造方法 写入权限
     * StatisticsController constructor.
     */
    public function __construct(){
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
            exit("请先登录");
        }
        if(!in_array(10,$this->rid)){
            exit("您没有用户权限");
        }
    }

    /**
     * 初始页面
     * 向前端传入 学院 数组
     * @return array|void
     */
    public function indexAction()
    {
        $chartCollege = $this->getCollegeTable()->fetchAll();
        return array('chartCollege'=>$chartCollege);
    }

    /**
     * 性别比例 e-charts
     */
    public function sexRationAction(){
        $adapter = $this->dbAdapter();
        $sql = 'SELECT count(*) as num,gender from stu_base as sb left join stu_check as sc on sb.uid = sc.uid where sc.`status` = \'10\' or sc.`status` = \'12\' GROUP BY gender';
        $info = iterator_to_array($adapter->query($sql)->execute());
        $response = $this->getResponse();
        $response->setContent(json_encode(array('info' =>$info)));
        header('Content-Type: application/json');
        return $response;
    }

    /**
     * 生源学校统计
     */
    public function studentSyAction(){
        $adapter = $this->dbAdapter();
        $sql = "select sb.graduate_university,dbuni.university_name as xxmc,count(*) as num from stu_base as sb left join stu_check as sc on sb.uid = sc.uid left join db_university_free as dbuni on sb.graduate_university = dbuni.university_id where sc.`status` = '10' or sc.`status` = '12' GROUP BY graduate_university ORDER BY num DESC LIMIT 10";
        $info = iterator_to_array($adapter->query($sql)->execute());
        $response = $this->getResponse();
        $response->setContent(json_encode($info));
        header('Content-Type: application/json');
        return $response;
    }

    /**
     * 生源地china
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function studentSfAction(){
        $adapter = $this->dbAdapter();
        $postData = array();
        $sql = "select db.SSMC,sb.ssdm,count(distinct sb.uid) num from stu_base sb left join stu_check as sc on sb.uid = sc.uid join db_administrative_division as db on sb.ssdm = db.SSDM where sc.`status` = '10' or sc.`status` = '12' group by sb.ssdm,db.SSMC";
        $info = iterator_to_array($adapter->query($sql)->execute());
        foreach ($info as $key=>$value){
            $postData[$value['SSMC']]=$value['num'];
        }
        $response = $this->getResponse();
        $response->setContent(json_encode($postData));
        header('Content-Type: application/json');
        return $response;
    }

    /**
     * 生源地柱状图
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function studentSfZhuAction(){
        $adapter = $this->dbAdapter();
        $sql = "select db.SSMC,sb.ssdm,count(distinct sb.uid) num from stu_base sb left join stu_check as sc on sb.uid = sc.uid join db_administrative_division as db on sb.ssdm = db.SSDM where sc.`status` = '10' or sc.`status` = '12' group by sb.ssdm,db.SSMC";
        $info = iterator_to_array($adapter->query($sql)->execute());
        $response = $this->getResponse();
        $response->setContent(json_encode($info));
        header('Content-Type: application/json');
        return $response;
    }

    /**
     * 其余各项数据展示
     * 211人数
     * 985人数
     * 六级600分以上
     */
    public function elseInfoAction(){
        $adapter = $this->dbAdapter();

        /**
         * 985
         */
        $sql_is985 = "SELECT count(*) as num from stu_base as sb left join stu_check as sc on sb.uid = sc.uid left join db_university_free as db on sb.graduate_university = db.university_id where db.is985 = '1' and (sc.`status` = '10' or sc.`status` = '12')";
        $is985 = iterator_to_array($adapter->query($sql_is985)->execute());
        $info['985人数'] = $is985[0]['num'];//985人数

        /**
         * 211
         */
        $sql_is211 = "SELECT count(*) as num from stu_base as sb left join stu_check as sc on sb.uid = sc.uid left join db_university_free as db on sb.graduate_university = db.university_id where db.is211 = '1' and (sc.`status` = '10' or sc.`status` = '12')";
        $is211 = iterator_to_array($adapter->query($sql_is211)->execute());
        $info['211人数'] = $is211[0]['num'];//211人数

        /**
         * cet6 600以上
         */
        $sql_cet6is600 = "SELECT count(*) as num from stu_base as sb left join stu_check as sc on sb.uid = sc.uid where sb.value_cet6 >= 600 and (sc.`status` = '10' or sc.`status` = '12'); ";
        $is600 = iterator_to_array($adapter->query($sql_cet6is600)->execute());
        $info['六级600以上'] = $is600[0]['num'];//6级600以上人数

        /**
         * cet6 620以上
         */
        $sql_cet6is620 = "SELECT count(*) as num from stu_base as sb left join stu_check as sc on sb.uid = sc.uid where sb.value_cet6 >= 620 and (sc.`status` = '10' or sc.`status` = '12'); ";
        $is620 = iterator_to_array($adapter->query($sql_cet6is620)->execute());
        $info['六级620以上'] = $is620[0]['num'];//6级620以上人数

        /**
         * cet6 640以上
         */
        $sql_cet6is640 = "SELECT count(*) as num from stu_base as sb left join stu_check as sc on sb.uid = sc.uid where sb.value_cet6 >= 640 and (sc.`status` = '10' or sc.`status` = '12'); ";
        $is640 = iterator_to_array($adapter->query($sql_cet6is640)->execute());
        $info['6级640以上'] = $is640[0]['num'];//6级600以上人数

        $response = $this->getResponse();
        $response->setContent(json_encode($info));
        header('Content-Type: application/json');
        return $response;
    }

    /*
     * 数据库层
     */
    function getCollegeTable() {
        if (!$this->collegeTable) {
            $sm = $this->getServiceLocator();
            $this->collegeTable = $sm->get('Basicinfo\Model\CollegeTable');
        }
        return $this->collegeTable;
    }
}