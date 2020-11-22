<?php
/**
 * @author gpw
 * 学院管理，学院授权
 * @todo 权限优化
 */

namespace Management\Controller;


use Management\Form\ProfessionForm;
use Management\Form\TeacherForm;
use basicinfo\Model\Profession;
use basicinfo\Model\Professionstaff;
use Management\Form\SubjectForm;
use Basicinfo\Model\College;
use Basicinfo\Model\PermissionControll;
use Basicinfo\Model\Subject;
use User\Model\Userrole;
use User\Model\UserTeacher;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Management\Form\CollegeForm;
use Zend\View\Model\ViewModel;

class CollegeController extends AbstractActionController
{

    protected $collegeTable;
    protected $subjectTable;
    protected $professionTable;
    protected $staffTable;
    protected $teacherForm;
    protected $professionstaffTable;
    protected $userTable;
    protected $userroleTable;
    protected $permissionTable;
    protected $courseTable;
    protected $usrteacherTable;
    protected $uid;
    protected $rid;
    protected $cid;

    public function __construct(){
//     $permissionControl = new PermissionControll();
//     $permissionControl->judgePermission();

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

        if(in_array(8,$this->rid)||in_array(9,$this->rid)||in_array(10,$this->rid)||in_array(11,$this->rid)||in_array(12,$this->rid)){
            return true;
        }else{
            echo "<script> alert('无权访问！');window.location.href='/info';</script>";
        }
    }

    ////核心action////

    /////////////////
    /// 学院相关action
    /////////////////

    /**
     * 添加学院与修改学院，以及进行院长和学院秘书的授权
     * @todo 目前是查找staff表中的所有数据，消耗大量时间
     * manager 院长
     * dean 秘书
     */
    public function addCollegeAction(){

        if(!in_array(10,$this->rid)){
            echo "<script> alert('无权访问！');window.location.href='/info';</script>";
        }

        $college_id = $this->getParam1();//从路由获取

        switch ($college_id) {
            case 1: echo "学院有误，请重新选择"; break;
            default: break;
        }

        /**
         * 获取staff表中所有的老师
         * @todo 之后修改
         */
        $staffArr = $this->getStaffTable()->fetchAllArr();

        $collegeForm = new CollegeForm();

        $request = $this->getRequest();

        if($request->isPost()){

            $arrayData = $request->getPost()->toArray();

           if(!strcmp($arrayData['submit'],'修改学院信息')){
                $collegeForm->setData($request->getPost());
                //验证表单是否有效
                if($collegeForm->isValid()){
                    $filteredValue = $collegeForm->getInputFilter()->getValues();

                    if(isset($filteredValue["dean"])){
                        $deanFull = explode("_", $filteredValue["dean"]);
                        $dean_id = $deanFull[0];
                    }
                    else $dean_id = "";
                    if(isset($filteredValue["manager"])) {
                        $managerFull = explode("_", $filteredValue["manager"]);
                        $manager_id = $managerFull[0];
                    }
                    else $manager_id="";
                    if(isset($filteredValue["tstu"])) {
                        $total_stu = $filteredValue["tstu"];
                    }
                    else $total_stu=null;
                    if(isset($filteredValue["fstu"])) {
                        $free_stu = $filteredValue["fstu"];
                    }
                    else $free_stu=null;
                    $college_old = $this->getCollegeTable()->getCollege($filteredValue["cid"]);

                    $data = array(
                        "college_id" => $filteredValue["cid"],
                        "college_name" => $filteredValue["cname"],
                        "dean_id"=>$dean_id,
                        "manager_id"=>$manager_id,
                        "total_stu"=>$total_stu,
                        "free_stu"=>$free_stu,
                    );
                    //插入数据库
                    $college = new College();
                    $college->exchangeArray($data);
                    $result = $this->getCollegeTable()->saveCollege($college);
                    if($result)
                        echo "<script>alert('添加成功');</script>";
                    else
                        echo "<script>alert('添加失败');</script>";

                    //是否收回授权的判断
                    if($college_old){//echo "老学院<br/>";
                        //echo "老院长 ".$college_old->staff_id."<br/>";
                        if($dean_id!=$college_old->dean_id) {//以前有这个学院，以前和现在的负责人不同
//                            if($staff_id!="")
//                                $this->authenticateStaff($staff_id,9);
                            if($college_old->dean_id && $college_old->dean_id!="")
                                $this->revokeStaffAuth($college_old->dean_id,11);
                        }
                        //echo "老秘书： ".$college_old->staff_id_s."<br/>";
                        if($manager_id!= $college_old->manager_id){
                            //老学院有新的院研究生秘书
//                            if($manager_id!="")
//                                $this->authenticateStaff($staff_id_s,9);
                            if($college_old->manager_id && $college_old->manager_id!="")
                                $this->revokeStaffAuth($college_old->manager_id,9);
                        }
                        if($dean_id!="")//授权给现在的人，他是否已经有这个角色交给authenticate函数去处理
                            $this->authenticateStaff($dean_id,11);
                        if($manager_id!="")
                            $this->authenticateStaff($manager_id,9);
                    }
                    else {//echo "新学院，新负责人<br/>";
                        if($dean_id!="")
                            $this->authenticateStaff($dean_id,11);
                        if($manager_id!="")
                            $this->authenticateStaff($manager_id,9);
                    }
                }
            }
            //else $this->formInvalidMessage($collegeForm);
        }
        $existColleges = $this->getCollegeTable()->fetchAll();
        return array(
            'form'			=>	$collegeForm,
            'existColleges'	=>	$existColleges,
            'staffArr'		=>	$staffArr,
        );
    }

    /**
     * 用户输入教师名称，自动补全信息
     * auto-get-staff.js
     */
    public function autoGetStaffAction(){
        $response = $this->getResponse();
        if ($this->getRequest()->isPost()) {
            $postData = array_merge_recursive(
                $this->getRequest()->getPost()->toArray()
            );
            $result='';
            if ( !is_null($postData['part']) ){
                $partname = $postData["part"];
                //echo $partname.",";
            } else $partname = "";
            $staffArr = $this->getStaffTable()->getStaffLike($partname);
            foreach ($staffArr as $staff) {
                $result .= ($staff . ",");
            }

            echo $result;
            exit;
        }
    }

    /**
     * 编辑学院信息
     * @return ViewModel
     */
    public function getCollegeByCidAction(){
        if(!in_array(10,$this->rid)){
            exit("权限拒绝");
        }
        $cid = $this->getParam1();
        $result = $this->getCollegeTable()->getCollege($cid);
        $dean= $this->getStaffTable()->getStaff($result->dean_id);
        $manager = $this->getStaffTable()->getStaff($result->manager_id);
        $total_stu = $result->total_stu;
        $free_stu = $result->free_stu;
        if($dean)
            $dean_name = $result->dean_id."_".$dean->staff_name;
        else $dean_name = "";
        if($manager)
            $manager_name = $result->manager_id."_".$manager->staff_name;
        else $manager_name = "";
        $data = array(
            "cid" => $cid,
            "cname"=>$result->college_name,
            "dean"=>$dean_name,
            "manager"=>$manager_name,
            "tstu"=>$total_stu,
            "fstu"=>$free_stu
        );
        //$staffArr = $this->getStaffTable()->fetchAllArr();
        $form = new CollegeForm();
        $form->setData($data);
        $view = new ViewModel(array(
            'form' => $form,
        ));
        $view->setTerminal(true);
        return $view;
    }

    /**
     * 删除学院
     * @return \Zend\Http\Response
     */
    public function deleteCollegeAction(){//删除学院
        if(!in_array(10,$this->rid)){
            exit("权限拒绝");
        }
        $cid = $this->getParam1();
        $subjects = $this->getSubjectTable()->getSubjectsByCid($cid);
        foreach ($subjects as $key => $subject) {
            $sid = $subject->subject_id;
            $this->deleteSubject($subject->subject_id,$cid,$subject->full_time);
        }
        $college = $this->getCollegeTable()->getCollege($cid);
        if($college){
            $dean_id = $college->dean_id;
            $manager_id = $college->manager_id;
            $this->getCollegeTable()->deleteCollege($cid);
            $this->revokeStaffAuth($dean_id,9);
            $this->revokeStaffAuth($manager_id,11);
        }
        return $this->redirect()->toRoute("management/default",array("controller"=>"college","action"=>"addCollege"));
    }

    /**
     *删除学院下设所有的学科以及方向
     * @param $sid
     * @param $cid
     * @param $full_time
     */
    public function deleteSubject($sid,$cid,$full_time){
        $subject = $this->getSubjectTable()->getSubjectTripleKey($sid,$cid,$full_time);
        $tid = $subject->staff_id;
        //删除学科下的所有方向
        $profs = $this->getProfessionTable()->getProfessionByCond(array('college_id'=>$cid,'subject_id'=>$sid,'full_time'=>$full_time));//删除prof表中对应字段
        if($profs){
            foreach ($profs as $key => $prof) {
                $this->deleteProfession($prof->prof_id_unique);
            }
        }
        //删除学科
        $this->getSubjectTable()->deleteSubjectByCondArr(array('subject_id'=>$sid,'college_id'=>$cid,'full_time'=>$full_time));//删除subject表中的唯一字段
        //收回授权
        $this->revokeStaffAuth($tid,8);//收回学科负责人角色
    }

    /**
     * 删除该学科下的所有方向
     * @param $prof_id_unique
     * @return \Zend\Http\Response
     */
    public function deleteProfession($prof_id_unique){
        $condition_arr = array(
            'prof_id_unique'=>$prof_id_unique,
        );
        $profession = $this->getProfessionTable()->getProfessionByCondArr($condition_arr);
        //删除所有相关的招生教师，即方向导师表中的pid，sid同这个的记录
        //1.删除这个方向，2.删除这个方向下的导师，3.收回这个方向下导师的角色，4.收回这个方向下方向负责人的角色
        if($profession) {
            //1.删除方向
            $this->getProfessionTable()->deleteProfessionByCond($condition_arr);
            //2.3.删除导师，收回导师角色
            $proStaffArr = $this->getProfessionstaffTable()->getProfessionstaff($condition_arr);
            if(!empty($proStaffArr)){//遍历该方向，收回所有导师的角色
                foreach ($proStaffArr as $key=> $row) {
                    $condition_arr['staff_id']=$row->staff_id;
                    //2.删除导师和方向的绑定关系
                    $this->getProfessionstaffTable()->deleteProfessionstaffByCondArr($condition_arr);
                    echo "职工 $row->staff_id 收回导师角色<br/>";
                    //3.收回导师的角色
                    $this->revokeStaffAuth($row->staff_id,7);//收回导师角色
                }
            }
            else{
                echo "$prof_id_unique ,$profession->profession_name 该方向下没有导师<br/>";
            }
            if(!empty($profession->staff_id)){
                //4.收回方向负责人的权限
                echo "收回$profession->staff_id 的方向负责人角色<br/>";
             //   $this->revokeStaffAuth($profession->staff_id,8);
                $this->revokeStaffAuth($profession->staff_id,12);
            }
            else {
                echo "该方向没有方向负责人<br/>";
            }
        }
        else {
            echo "没找到profession ,prof_id_unique = $prof_id_unique <br/>";
            return $this->redirect()->toRoute("management/default", array(
                "controller" => "college",
                "action" => "addCollege",
            ));
        }
    }


    /////////////////
    /// 学科相关action
    /////////////////

    /**
     * 添加学科
     * @return array
     */
    public function addSubjectAction() {

        /**
         * 添加权限
         * 8,9,10,11
         * 学科,院长，研究生院，学院秘书
         */
        if(in_array(9,$this->rid)||in_array(10,$this->rid)||in_array(11,$this->rid)||in_array(8,$this->rid)){
            $per = 1; //无实际意义
        }else{
            exit("权限拒绝");
        }

        /**
         * 一些传递变量
         * 1.frontData : 前端数组（用来给用户提示是否正确修改招生数据）
         */
        $frontData=array();//前端数组

        /**
         * 先决条件
         * 1.从路由取cid和cname（从路由只能获取到学院id(cid)，通过cid获取学院名称）
         * 2.设置permission
         */
        $this->getCidCnameFromRoute($cid,$college_name);
        $permission = $this->setUserPermission();

        /**
         * 如果不是研究生院做学院归属判断
         */
        if($permission==1&&$this->cid!=$cid){
            exit("权限错误（您不属于该学院）");
        }

        /**
         * 构建学科表单
         * 1.从base_staff表中获取该学科的所有老师
         * 2.将 permission(用户权限标志)，cid(学院ID)，staffArr(该学院下的所有老师) 三个参数传递到SubjectForm中
         */
        $staffArr = $this->getStaffTable()->getStaffArrByCid($cid); //1
        $form = new SubjectForm($permission,$cid,$staffArr);//2
        // $this->getSubjectTable(); //意义不明

        /**
         * 请求判断
         *
         * post:提交表单
         *      1.验证表单是否有效
         *      1.进行权限判定，若为 非研究生院 执行 2，若为 研究生院 执行 3；
         *      2.非研究生院
         *
         *      3.研究生院
         * get:
         */
        $request = $this->getRequest();
        if($request->isPost()){

            if(in_array(8,$this->rid)){
                exit("权限拒绝");
            }

            $form->setData($request->getPost());
            if($form->isValid()){//echo "form is valid<br/>";
                $filteredValue = $form->getInputFilter()->getValues();
                $sid = $filteredValue["subject_id"];
                $tid = $filteredValue["staff_id"];
                $full_time = $filteredValue["full_time"];

                /*
                 * 学科负责人的授权与撤回
                 */
                $old_sub = $this->getSubjectTable()->getSubjectTripleKey($sid,$cid,$full_time);
                if($old_sub){
                    if($tid!=$old_sub->staff_id){//以前有这个学科，以前和现在的负责人不同
                        $flag = 2;//老学科有新的负责人
                    }
                    else{
                        $flag = 0;//老学科负责人不变
                    }
                }
                else{
                    $flag = 1;//有新学科，新负责人
                }

                //从表单里取值
                $data = $form->getData();

                //插入数据库
                $subject = new Subject();
                $subject->exchangeArray($data);

                //收回权限 和 授权
                if($flag!=0){
                    if($flag==2 && $old_sub->staff_id!="") {
                        $this->revokeStaffAuth($old_sub->staff_id, 8);
//                        echo "收回 $old_sub->staff_id 授权 8<br/>";
                    }
                    if($tid!="") {
//                        echo "给新学科负责人授权";
                        $this->authenticateStaff($tid,8);
                    }
//                    else echo "新学科负责人空";
                }//else 学科负责人不变或没有学科负责人

                if ($permission != 0) {
                    //非研究生院
                    $result = $this->getSubjectTable()->updateStaffId($sid,$cid,$full_time,$tid);
                    if($result !=2){
                        if($result==0){
                            echo "<script>alert('您没有修改负责人');</script>";
                        }else{
                            echo "<script>alert('修改负责人失败，请联系相关负责人');</script>";
                        }
                        $existSubjects = $this->getSubjectTable()->getSubjectsByCid($cid);
                        return array('form'=>$form,'college_name'=>$college_name,'cid'=>$cid,'existSubjects'=>$existSubjects,'staffArr'=>$staffArr,'frontData'=>$frontData);
                    }else{
                        echo "<script>alert('修改负责人成功');</script>";
                    }
                }else{
                    //研究生院
                    $result = $this->getSubjectTable()->addSubject4($subject);
                    //在添加成功后实时更新数据
                    $cid = $data['college_id'];
                    $dynData = $this->dynAddStuNum($cid,$college_name);
                    if(!in_array("err",$dynData)){
                        $frontData =array(
                            "college_name"=>$college_name,
                            "all_total_stu"=>$dynData['total_stu'],
                            "all_free_stu"=>$dynData['free_stu'],
                        );
                    }else{
                        $frontData = $dynData;
                    }
                }
            }
        }
        $existSubjects = $this->getSubjectTable()->getSubjectsByCid($cid);
        return array('form'=>$form,'college_name'=>$college_name,'cid'=>$cid,'existSubjects'=>$existSubjects,'staffArr'=>$staffArr,'frontData'=>$frontData);
    }

    public function getCidCnameFromRoute(&$cid,&$cname){//从路由取cid和cname
        $cid =  $this->params()->fromRoute('param1', 0);//从路由取cid
        if($cid<=0){
            return $this->redirect()->toRoute("management/default",array('controller'=>'college',"action"=>"addCollege","param1"=>"1"));
        }
        $college = $this->getCollegeTable()->getCollege($cid);//用cid查学院名称
        $cname = $college->college_name;
    }

    /**
     * author : gpw
     * time : 2020年3月28日22:00:41
     * 设置权限值
     * 从session中获取用户权限
     * 进行权限判断 并修改 userPermission 值
     * 权限划分：
     *         10：研究生院 ( $userPermission = 0)
     *          9/11：学院 ($userPermission = 1)
     * 如果在 rid 数组中查到 10 则将 userPermission 设置为 0
     */
    public function setUserPermission(){
        $ridCon = new Container("rid");
        $rid = $ridCon -> item;
        if (!in_array(10,$rid)) {
            $userPermission = 1;
        }else{
            $userPermission = 0;
        }
        return $userPermission;
    }

    /**
     * 通过学科id查找学科
     * @return ViewModel
     */
    public function getSubjectBySidAction(){
        $containerCid = new Container("college_id");
        $cid = $containerCid->item;
        $permission = $this->setUserPermission();
        if(isset($_POST["college_id"]))
            $cid = $_POST["college_id"];
        if(isset($_POST["full_time"]))
            $full_time = $_POST["full_time"];
        $sid = $this->getParam1();

        if(!isset($cid) || !isset($full_time) || !isset($sid)){
            $form = new SubjectForm(1,$cid,array());
            $view = new ViewModel(array(
                'form' => $form,
                'cid'=>$cid,
                'college_name'=>"",
                'staffArr'=>array(),
                //'existSubjects'=>$existSubjects,
            ));
            $view->setTerminal(true);
            return $view;
        }
//        echo "cid = $cid , sid = $sid , full_time = $full_time<br/>";

        $college = $this->getCollegeTable()->getCollege($cid);//用cid查学院名称
        if($college)
            $college_name = $college->college_name;


        $staffFromCollege = $this->getStaffTable()->getStaffByCid($cid);
        $staffArr = array();
        foreach ($staffFromCollege as $key => $row) {
            if(!empty($row)){
                $id = $row->staff_id;
                $name = $row->staff_name;
                $staffArr[$id] = $name;
            }
            else echo "empty select result<br/>";
        }


//        $full_time = $this->params()->fromRoute()
        $resultSet = $this->getSubjectTable()->getSubjectTripleKey($sid,$cid,$full_time);

        $data = get_object_vars($resultSet);
        $form = new SubjectForm($permission,$cid,$staffArr);
        $form->setData($data);
        $view = new ViewModel(array(
            'form' => $form,
            'cid'=>$cid,
            'college_name'=>$college_name,
            'staffArr'=>$staffArr,
            //'existSubjects'=>$existSubjects,
        ));
        $view->setTerminal(true);
        return $view;
    }

    /**
     * 删除学科
     * @return \Zend\Http\Response
     */
    public function deleteSubjectAction(){//删除学科
        if(in_array(9,$this->rid)||in_array(10,$this->rid)||in_array(11,$this->rid)){
            $per = 1; //无实际意义
        }else{
            exit("权限拒绝");
        }
        $sid = $this->getParam1();
        $cid="";
        if(isset($_GET)) {
            foreach ($_GET as $key => $value) {
                $cid = $value;
                break;
            }
        }
        $full_time = $_GET["full_time"];
        $this->deleteSubject($sid,$cid,$full_time);
//		exit;
        return $this->redirect()->toRoute("management/default",array('controller'=>'college',"action"=>"addSubject","param1"=>$cid));
    }

    /**
     * 找到所属学科
     * @param $subject_id 学科id
     * @param $full_time 学习方式
     * @param $cid 学院
     * @return bool
     */
    public function findSubjectTeacher($subject_id,$full_time,$cid){
        $adapter = $this->dbAdapter();
        $s1 = "select staff_id from base_subject where subject_id ='".$subject_id."' and full_time = ".$full_time." and college_id = '".$cid."'";
        $staff_idTemp = $adapter->query($s1)->execute();
        $staff_idArr = iterator_to_array($staff_idTemp);
        $staff_id = $staff_idArr[0]['staff_id'];
        if($this->uid!=$staff_id){
            return false;
        }else{
            return true;
        }
    }


    /////////////////
    /// 方向相关action
    /////////////////

    /**
     * 添加方向
     * @return array|\Zend\Http\Response
     */
    public function addProfessionAction() {//录入方向


        //1.取参数：学院id 学科id 全日制否
        $sid =  $this->getParam1();//从路由取学科id

        foreach ($_GET as $key=>$value){
            $cid = $value;
            break;
        }//从添加学科的页面url传过来的学院id
        if(isset($_GET["full_time"])){
            $full_time = $_GET["full_time"];
        }//从添加学科的页面url传过来的全日制否
        if(isset($_POST["college_id"])) {
            $cid = $_POST["college_id"];
        }//从提交后的表单传过来的学院id
        if(isset($_POST["full_time"])) {
            $full_time = $_POST["full_time"];
        }//从提交后的表单传过来的全日制否（学习方式）

        /**
         * 权限
         * 8:学科负责人
         */
        if(in_array(8,$this->rid)){
            if($this->findSubjectTeacher($sid,$full_time,$cid)){
                $per = 1;
            }else{
                exit("权限拒绝1");
            }
        }

        if(in_array(8,$this->rid)||in_array(9,$this->rid)||in_array(10,$this->rid)||in_array(11,$this->rid)){
            $per = 1;
        }else{
            exit("权限拒绝2");
        }


        //没取成功就返回到添加学科的页面
        if(!isset($sid) || !isset($cid) || !isset($full_time)){
            return $this->redirect()->toRoute("management/default",array("controller"=>"college","action"=>"addSubject","param1"=>"1"));
        }

        //2.取各个显示需要的内容
        //取学科名称
        $subject = $this->getSubjectTable()->getSubjectTripleKey($sid,$cid,$full_time);
        $subject_name = $subject?$subject->subject_name:"";
        //取学院名称
        $college = $this->getCollegeTable()->getCollege($cid);
        $cname = $college?$college->college_name:"";
        //取该学院的教师列表
        $staffArr = $this->getStaffTable()->getStaffArrByCid($cid);


        $condition = array(//4元素：学院 学科 全日制 方向编号
            "full_time"=>$full_time,
            "subject_id" => $sid,
            "college_id"=>$cid,
        );
        //3.新建表单
        $form = new ProfessionForm($staffArr);

        //5.表单提交后 处理数据
        $request = $this->getRequest();
        if($request->isPost()){//echo "recv from form<br/>";
            $form->setData($request->getPost());
            if($form->isValid()){//echo "form is valid<br/>";
                $data = $form->getInputFilter()->getValues();//4元素：编号 名称 负责人 提交
                unset($data["submit"]);//3元素：编号 名称 负责人
                $data = array_merge($data,$condition);//除了prof_id_unique之外，完整的一条方向记录

                //若已有这个方向，该方向已有方向负责人，且方向负责人和现在不同
                $condition["profession_id"] = $data["profession_id"];
                //var_dump($condition);
                $profession = $this->getProfessionTable()->getProfessionByCondArr($condition);

                if($profession &&   $profession->staff_id!=$data["staff_id"]){
                    $old_staff = $profession->staff_id;
                    $data["prof_id_unique"] = $profession->prof_id_unique;//方向的唯一主键
                }
                //更新数据库
                $profession = new Profession();
                $profession->exchangeArray($data);
                $result = $this->getProfessionTable()->saveProfession($profession);
                //收回旧老师的权限
                if($result && isset($old_staff) && $old_staff!=""){//成功了就授权给老师
                    //方向负责人不空，收回授权
                //    $this->revokeStaffAuth($old_staff,8);
                    $this->revokeStaffAuth($old_staff,12);
                }
                //给新老师授权
                if($result && isset($data["staff_id"]) && $data["staff_id"]!=""){//新的方向负责人不空，授权给新的人
                //    $this->authenticateStaff($data["staff_id"],8);
                    $this->authenticateStaff($data["staff_id"],12);
                }
            }
            else $this->formInvalidMessage($form);
        }

        //3.查询现有的方向列表，因为提交后要刷新， 所以写在这里而不是表单验证前面
        unset($condition["profession_id"]);
        $existProfessions = $this->getProfessionTable()->getProfessionByCond($condition);

        return array(
            'form'=>$form,
            'college_name'=>$cname,
            'subject_name'=>$subject_name,
            'full_time_cn'=>$full_time==1?"全日制":"非全日制",
            'sid'=>$sid,
            'cid'=>$cid,
            'full_time'=>$full_time,
            'existProfessions'=>$existProfessions,
            'staffArr'=>$staffArr
        );
    }

    /**
     * 删除方向
     * @return \Zend\Http\Response
     */
    public function deleteProfessionAction(){//删除方向
        $prof_id_unique =$this->getParam1();
        $profession = $this->getProfessionTable()->getProfessionByCondArr(array('prof_id_unique'=>$prof_id_unique));
        $this->deleteProfession($prof_id_unique);
        return $this->redirect()->toRoute(
            "management/default",
            array(
                "controller"=>"college",
                "action"=>"addProfession",
                "param1"=>$profession->subject_id,
            ),
            array(//get传参
                "query"=>array(
                    "cid"=>$profession->college_id,
                    "full_time"=>$profession->full_time,
                ),
            )
        );
    }

    /**
     * 编辑方向
     * @return ViewModel
     */
    public function editProfessionAction() {//录入方向
//	    $this->getProfessionParamsFromRout($cid,$sid,$full_time,$pid);
        $prof_id_unique =$this->getParam1();
        $condition_arr = array(
            'prof_id_unique'=>$prof_id_unique,
//            'college_id'=>$cid,
//            'subject_id'=>$sid,
//            'profession_id'=>$pid,
//            'full_time'=>$full_time,
        );
        $profession = $this->getProfessionTable()->getProfessionByCondArr($condition_arr);

        $cid = "";
        $staffArr = array();
        $full_time = 1;
        //新建表单
        if($profession){
            $cid = $profession->college_id;
            $pid = $profession->profession_id;
            $full_time = $profession->full_time;
            $staffArr = $this->getStaffTable()->getStaffArrByCid($cid);
            $form = new ProfessionForm($staffArr);
            $data = array(
                "profession_id" => $pid,
                "profession_name" => $profession->profession_name,
                //"subject_id" => $sid,
                //"college_id"=>$cid,
                "staff_id"=> isset($profession->staff_id)?$profession->staff_id:"",
            );
            $form->setData($data);
        }
        else {
            echo "<script>alert('" . $prof_id_unique . " 对应的方向不存在');</script>";
        }
        $view = new ViewModel(array(
            'form'=>$form,
            'cid'=>$cid,
            'staffArr'=>$staffArr,
            'full_time' => $full_time,
        ));
        $view->setTerminal(true);
        return $view;

        //return array();
    }

    /////////////////
    /// 导师相关action
    /////////////////

    public function addTeacherAction(){//录入导师方向//echo "this is add teacher<br/>";
        $this->getCidCnameFromRoute($cid,$college_name);//取cid cname
        $staffArr = $this->getStaffTable()->getStaffArrByCid($cid);//取员工列表
        $subjectArr = $this->getSubjectTable()->getSubjectArrByCid($cid);//取科目列表
        $form = new TeacherForm($staffArr,$subjectArr);//创建表单

        $request = $this->getRequest();
        if($request->isPost()){//echo "recv from form<br/>";
            $form->setData($request->getPost());
            if($form->isValid()){//echo "form is valid<br/>";
                $filteredValue = $form->getInputFilter()->getValues();
                $this->authenticateStaff($filteredValue["tid"],7);
                $this->getProfessionstaffTable();
                if(!empty($filteredValue["sid1"])){
//				    $full_time =  $filteredValue["full_time1"];
                    if(!empty($filteredValue["pid11"])){
                        $this->addProfStaff($filteredValue['tid'],$filteredValue["pid11"]);
                    }
                    if(!empty($filteredValue["pid12"])){
                        $this->addProfStaff($filteredValue['tid'],$filteredValue["pid12"]);
                    }
                    if(!empty($filteredValue["pid13"])){
                        $this->addProfStaff($filteredValue['tid'],$filteredValue["pid13"]);
                    }
                }
                if(!empty($filteredValue["sid2"])){
//                    $full_time =  $filteredValue["full_time2"];
                    if(!empty($filteredValue["pid21"])){
                        $this->addProfStaff($filteredValue['tid'],$filteredValue["pid21"]);
                    }
                    if(!empty($filteredValue["pid22"])){
                        $this->addProfStaff($filteredValue['tid'],$filteredValue["pid22"]);
                    }
                    if(!empty($filteredValue["pid23"])){
                        $this->addProfStaff($filteredValue['tid'],$filteredValue["pid23"]);
                    }
                }
            }
            else $this->formInvalidMessage($form);
//			exit;
        }
        //获取已添加的导师信息
        $profStaffArr = $this->getProfessionstaffTable()->getProfStaffArrByCid($cid);
        return array('form'=>$form,'cid'=>$cid,'profStaffArr'=>$profStaffArr,'college_name'=>$college_name);
    }

    public function addProfStaff($staff_id,$prof_id_unique){
        $data = array(
            "staff_id" => $staff_id,
            "prof_id_unique" => $prof_id_unique,
        );
        $profStaff = new Professionstaff();
        $profStaff->exchangeArray($data);
        $result = $this->getProfessionstaffTable()->addProfessionstaff($profStaff);
        if(!$result){
            echo "fail to add into db<br/>";
        }
    }

    public function getProfBySidAction(){
        $sid = $this->getParam1();
        $cid="";
        $full_time = "";
        if(isset($_POST["college_id"]))
            $cid=$_POST["college_id"];
        if(isset($_POST["full_time"]))
            $full_time=$_POST["full_time"];
        //用sid去查profession表
        $resultSet = $this->getProfessionTable()->getProfessionByCond(array(
                'college_id'=>$cid,
                'subject_id'=>$sid,
                'full_time'=>$full_time,
            )
        );
        $profArr = array();
        foreach ($resultSet as $key => $row) {
            if(!empty($row)){
                $id = $row->prof_id_unique;
                $name = $row->profession_name;
                $profArr[$id] = $name;
            }
//			else echo "<option value='-".$key."'>empty select result</option>";
        }
        return array('profArr'=>$profArr,'sid'=>$sid);
    }

    public function deleteProfStaffAction(){//删除方向导师
        //echo "this is delete profession staff action<br/>";
        $prof_id_unique = $this->params()->fromRoute("param1",0);
        $staff_id = $this->params()->fromRoute("param2",0);
        $college_id = $this->params()->fromRoute("param3",0);
        $id = array('staff_id'=>$staff_id,'prof_id_unique'=>$prof_id_unique);
        $this->getProfessionstaffTable()->deleteProfessionstaffByCondArr($id);
        $this->revokeStaffAuth($staff_id,7);//收回导师角色
        return $this->redirect()->toRoute("management/default",array("controller"=>"college","action"=>"addTeacher","param1"=>$college_id));
    }

    ////其他方法////

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

    /**
     * 从学科设置中动态获取招生总人数并插入到表中
     * 时间 ：2020年3月3日15:35:12
     * author ：gpw
     */
    public function dynAddStuNum($cid,$college_name){
        $dataS = $this->getSubjectTable()->sumStu($cid);
        $data = array(
            "college_id" => $cid,
            "college_name" => $college_name,
            "total_stu" => $dataS['total_stu'],
            "free_stu" => $dataS['free_stu'],
        );
        $college = new College();
        $college->exchangeArray($data);
        $result = $this->getCollegeTable()->saveStuNum($college);
        if ($result){
            return $dataS;
        }
        else{
            $dataErr = array(
                "error"=>"获取总数据失败请重试",
            );
            return $dataErr;
        }
    }

    /**
     * 验证表单是否有效
     * @param $form
     */
    public function formInvalidMessage($form){//表单验证无效的原因报错
        $messages = $form->getMessages();
        echo "<br/>表单验证无效<br/>";
        foreach ($messages as $key => $value) {
            echo $key.":</br>";
            foreach ($value as $key1 => $value1) {
                echo "&nbsp;".$key1.":".$value1."</br>";
            }
        }
    }

    ////角色用户的授权与收回////

    /*
 * function 授权某员工的某角色
 * param $tid 员工编号staff_id
 *              $rid 角色编号
 * */
    public function authenticateStaff($tid,$rid){//授予某员工的 某角色
        $staff = $this->getStaffTable()->getStaff($tid);//查staff表取uid
        $this->getUserroleTable();
        $userrole = new Userrole();
        $flag = 0;//标识是否需要插userrole表

        //查找教师user表是否有这个老师，没有则把老师插入user表
        $user = $this->getUsrteacherTable()->getUser($tid);
        if(!$user){//如果staffid不在usr_teacher表里，则插入
            if(empty($staff->email)){
                echo "<script>alert('当前用户邮箱信息不完善，请补充信息后添加');window.location.replace('/management/teacher/adddean/'".$staff->college_id.")</script>";
                exit();
            }
            $user = new UserTeacher();
            $userData = array(
                'staff_id'=>$tid,
                'user_name' => $staff->staff_name,
                'email' => $staff->email,
                'password' => md5($staff->email),
            );
            $user->exchangeArray($userData);
            $this->getUsrteacherTable()->saveUsrteacher($user);

            $flag = 1;//要插入userrole表
            $ur_data = array('uid'=>$tid,'rid'=>2);//插入教师角色
            $userrole->exchangeArray($ur_data);
            if($this->getUserroleTable()->saveUserrole($userrole))
                echo "<script>alert('成功添加新用户，用户名密码都为邮箱');</script>";
            $ur_data = array('uid'=>$tid,'rid'=>$rid);//身份角色插入，等会再做，和旧老师一起
        }
        else{//echo "新负导师有uid<br/>";echo "uid = ".$staff->uid."<br/>";
            $ur_data = array('uid'=>$tid,'rid'=>$rid);
            //在userrole里查询他是否有负责人权限
            $ifExist = $this->getUserroleTable()->getUserrole($ur_data);
            $row = $ifExist->current();
            if(!$row){//echo "新导师没有导师角色<br/>";
                $flag = 1;//没有则在userrole里插入
            }
        }
        if($flag==1){
            $userrole->exchangeArray($ur_data);
            if($this->getUserroleTable()->saveUserrole($userrole))
                echo "<script>alert('成功授权');</script>";
            //echo "成功授权 用户：$uid ， $rid 角色 <br/>";
        }
    }

    /*
     * function 收回某员工的某角色
     * param $tid 员工编号staff_id
     *              $rid 角色编号
     * */
    public function revokeStaffAuth($tid,$rid){//收回某员工的 某角色
        $staff = $this->getStaffTable()->getStaff($tid);//staff表里查这个人的uid
        $row=null;
        $flag2 = 0;
        //echo "收回 tid = $tid , rid = $rid 中<br/><br/>";
        switch ($rid) {
            case 12://方向负责人
            case 8://学科负责人
                $stillInCharge = $this->getSubjectTable()->getSubjectsByStaffid($tid);
                $stillInCharge2 = $this->getProfessionTable()->getProfessionByCond(array(
                    'staff_id'=>$tid,
                ));
                if($stillInCharge2){
                    foreach ($stillInCharge2 as $profession){
//                        var_dump($profession);
//                        echo "<br/><br/><br/>";
                        if($profession && $profession->staff_id &&$profession->staff_id!=""){
                            $flag2 = 1;
                            break;
                        }
                    }
                }
                //echo "flag = $flag2<br/>";
                break;
            case 7://导师
                $idArr0 = array('staff_id'=>$tid);
                $stillInCharge = $this->getProfessionstaffTable()->getProfessionstaff($idArr0);
                break;
            case 9://学院负责人
                $stillInCharge = $this->getCollegeTable()->getCollegeByCondArr(array("dean_id"=>$tid));
                //$stillInCharge = 1;
                break;
            case 11://院秘书
                $stillInCharge = $this->getCollegeTable()->getCollegeByCondArr(array("manager_id"=>$tid));
                break;
            default://其它
                $stillInCharge = NULL;
                break;
        }
        if($stillInCharge && is_object($stillInCharge))
            $row = $stillInCharge->current();

        if(!$row && $flag2==0){//空
            $idArr = array('uid'=>$tid,'rid'=>$rid);
            $this->getUserroleTable()->deleteUserrole($idArr);
//			echo "负责人 $staff->staff_id 不再是其它的 $rid 负责人了，收回其角色<br/>";
        }
//		else{
//            echo "负责人 $staff->staff_id 还是其它的 $rid 负责人了，不收回其角色<br/>";
//		    var_dump($row);
//        }
    }

    ////数据库连接////

    public function getUserTable() {
        if (! $this->userTable) {
            $sm = $this->getServiceLocator ();
            $this->userTable = $sm->get ( 'User\Model\UserTable' );
        }
        return $this->userTable;
    }
    public function getUserroleTable() {
        if (! $this->userroleTable) {
            $sm = $this->getServiceLocator ();
            $this->userroleTable = $sm->get ( 'User\Model\UserroleTable' );
        }
        return $this->userroleTable;
    }
    public function getCollegeTable() {
        if (! $this->collegeTable) {
            $sm = $this->getServiceLocator ();
            $this->collegeTable = $sm->get ( 'Basicinfo\Model\CollegeTable' );
        }
        return $this->collegeTable;
    }
    public function getSubjectTable() {
        if (! $this->subjectTable) {
            $sm = $this->getServiceLocator ();
            $this->subjectTable = $sm->get ( 'Basicinfo\Model\SubjectTable' );
        }
        return $this->subjectTable;
    }
    public function getProfessionTable() {
        if (! $this->professionTable) {
            $sm = $this->getServiceLocator ();
            $this->professionTable = $sm->get ( 'Basicinfo\Model\ProfessionTable' );
        }
        return $this->professionTable;
    }
    public function getStaffTable() {
        if (! $this->staffTable) {
            $sm = $this->getServiceLocator ();
            $this->staffTable = $sm->get ( 'Basicinfo\Model\StaffTable' );
        }
        return $this->staffTable;
    }
    public function getProfessionstaffTable() {
        if (! $this->professionstaffTable) {
            $sm = $this->getServiceLocator ();
            $this->professionstaffTable = $sm->get ( 'Basicinfo\Model\ProfessionstaffTable' );
        }
        return $this->professionstaffTable;
    }
    public function getPermissionTable(){
        if (! $this->permissionTable) {
            $sm = $this->getServiceLocator ();
            $this->permissionTable = $sm->get ( 'User\Model\PermissionTable' );
        }
        return $this->permissionTable;
    }
    public function getCourseTable(){
        if (! $this->courseTable) {
            $sm = $this->getServiceLocator ();
            $this->courseTable = $sm->get ( 'Basicinfo\Model\CourseTable' );
        }
        return $this->courseTable;
    }
    public function getUsrteacherTable(){
        if (! $this->usrteacherTable) {
            $sm = $this->getServiceLocator ();
            $this->usrteacherTable = $sm->get ( 'User\Model\UserTeacherTable' );
        }
        return $this->usrteacherTable;
    }
}