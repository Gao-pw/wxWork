<?php

namespace Management\Controller;

use Management\Form\OthersForm;
use Management\Form\PersonalForm;
use User\Model\Userrole;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use User\Model\UserTeacher;
use Basicinfo\Model\Staff;

class SystemManagementController extends AbstractActionController
{
    protected $TBaseCollegeTable;
    protected $UsrTeacherTable;
    protected $UsrRoleTable;
    protected $configKeyTable;
    protected $StaffTable;
    protected $college_table;
    protected $subject_table;

    public function getRolesArr($rid_arr){
        $roles = array();
        if(in_array('10',$rid_arr)){//角色select
            $roles = array(
                '9'=>'学院负责人',
                '10'=>'研究生院',
                '11'=>'院秘书',
            );
        }elseif(in_array('9',$rid_arr) || in_array('11',$rid_arr)){
            $roles = array(
                '2'=>'教师',
                '8'=>'学科负责人',
                '11'=>'院秘书',
                '12'=>'方向负责人',
            );
        }else{
            echo "<script>alert('无权访问!');window.location.href='/info';</script>";
        }
        return $roles;
    }

    public function othersAction(){
        $login_id_container = new Container('uid');
        $login_id = $login_id_container->item;
        if (is_null($login_id)) {
            echo "<script> alert('您未登录，尚无权访问！');window.location.href='/info';</script>";
        }
        $rid_container = new Container('rid');
        $rid_arr = $rid_container->item;//login 用户的权限
        if (is_null($rid_arr)) {
            echo "<script> alert('系统中未查到您的权限，尚无权访问！');window.location.href='/info';</script>";
        }
        $college_id_container = new Container('college_id');
        $college_id = $college_id_container->item;

        $roles_arr = $this->getRolesArr($rid_arr);
        if (!in_array(10,$rid_arr)) {//不是研究生院
            $college_info = $this->getTBaseCollegeTable()->getCollege($college_id);
            if (is_null($college_info)) {
                $college_info = $this->getTBaseCollegeTable()->getCollegebyStaffid($login_id);
            }
            $search_college_arr[$college_info->college_id] = $college_info->college_name;
        } else {
            $search_college_arr = $this->getTBaseCollegeTable()->getCollegesIDNameArr();
        }

        //获取可修改的角色数组(用于修改时的选择)
        $form = new PersonalForm($roles_arr,$search_college_arr);
        $form1 = new OthersForm($roles_arr,$search_college_arr);
        $current_page = $this->params()->fromRoute('param2');
        if (empty($current_page)) {
            $current_page = 1;
        }
        $per_page = 15;
        $offset = ($current_page - 1) * $per_page;


        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = array_merge_recursive(
                $this->getRequest()->getPost()->toArray()
            );
            $form1->setData($postData);
            if ($form1->isValid()) {
                $data = $form1->getData();
                $staff_id = $data['Staffid'];
                $rid = $data['Rid'];
                //获取该用户所有权限记录的数组usr_arr
                $usr_rid = $this->getUsrRole()->getRidArr($staff_id)->toArray();
                $usr_arr = array();
                foreach ($usr_rid as $k => $v){
                    $usr_arr[$k]['uid'] = $staff_id;
                    $usr_arr[$k]['rid'] = $v['rid'];
                }
                //删除所有权限
                if(!empty($usr_arr)){
                    $res1 = $this->getUsrRole()->deleteUsrRid($staff_id);
                }else
                    $res1 = true;
                //赋予新权限
                $insert_rid2 = new Userrole();
                $insert_rid2->uid = $staff_id;
                $insert_rid2->rid = '2';
                $res3 = $this->getUsrRole()->saveUserrole($insert_rid2);

                $insert_rid = new Userrole();
                $insert_rid->uid = $staff_id;
                $insert_rid->rid = $rid;
                $res2 = $this->getUsrRole()->saveUserrole($insert_rid);
                if($res1 && $res2 && $res3){
                    echo "<script>alert('修改成功')</script>";
                }elseif(!$res1 && !$res2){
                    echo "<script>alert('修改失败')</script>";
                }else{
                    echo "<script>alert('修改失败')</script>";
                    if(!$res1){//删除失败，添加成功
                        $this->getUsrRole()->deleteLastInsert();
                    }elseif(!$res2){//删除成功，添加失败
                        foreach ($usr_arr as $key => $value){
                            $this->getUsrRole()->saveUserrole($value);
                        }
                    }
                }
            }
            else
                echo "<script>alert('提交失败，请检查是否填写正确')</script>";
        }
        $ColArr = array();
        $count = 0;
        $allTeacher = $this->getUsrTeacherTable()->fetchAll()->toArray();
        if (in_array("10",$rid_arr)){
            foreach ($allTeacher as $k => $v){
                $flag = $this->getUsrRole() -> getUserrole(array(
                    'uid' => $v['staff_id'],
                    'rid' => 9
                ))->toArray();
                $flag2 = $this->getUsrRole() -> getUserrole(array(
                    'uid' => $v['staff_id'],
                    'rid' => 10
                ))->toArray();
                $flag3 = $this->getUsrRole() -> getUserrole(array(
                    'uid' => $v['staff_id'],
                    'rid' => 11
                ))->toArray();
                if (empty($flag)&&empty($flag2)&&empty($flag3))
                    continue;
                else{
                    $ColArr[$count] = $v;
                    $count++;
                }
            }
        }elseif(in_array("9",$rid_arr)){
            foreach ($allTeacher as $k => $v){
                $id = $v['staff_id'];
                $col = $this->getStaffTable()->getColBySid($id);
                if($col == $college_id){//查看该老师是否属于用户所在学院
                    $ColArr[$count] = $v;
                    $count++;
                }
            }
        }


        $usr_teacher = $ColArr;

        $total_num = count($ColArr);
        $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($usr_teacher));
        $paginator->setCurrentPageNumber($current_page);
        $total_page = ceil($total_num / $per_page);
        $pagesInRange = array();
        for ($i = 1; $i <= $total_page; $i++) {
            $pagesInRange[] = $i;
        }


        $column = array(
            'staff_id' =>'编号',
            'real_name'=>'姓名',
            'user_name'=>'用户名',
            'college'=>'学院',
            'mobile'=>'移动电话',
            'create_time'=>'创建时间',
            'rid'=>'角色',
            'oprat'=>' ',
        );

        $data_push = array();
        if($total_num-$offset < $per_page){
            $teacher = array_slice($ColArr,$offset);
        }else{
            $teacher = array_slice($ColArr,$offset,$per_page);
        }

        foreach ($teacher as $key => $value){
            $staff_id = $value['staff_id'];
            //获取教师角色（$rid）
            $usr_rid = $this->getUsrRole()->getRidArr($staff_id)->toArray();

            $rid = "";
            foreach ($usr_rid as $k => $v){
                //将rid转换为name
                $Name = $this->getConfigKeyTable()
                    ->getConfigValueByKey('usr_role',array('value_name' => $v['rid']),true,false);
                $rid = $rid." ".$Name[$v['rid']]['value_cn']." ";
            }


            //获取院系名称（$college_name）
            $base_staff = $this->getStaffTable()->getStaff($staff_id);
            if(!$base_staff){
                $data_push[$key] = $this->assemble($value,null,null,$rid);
                continue;
            }

            //获取移动电话（$phone）
            $phone = $base_staff->phone;
            $college_id = $base_staff->college_id;
            $college = $this->getTBaseCollegeTable()->getCollege($college_id);
            if(!$college){
                $data_push[$key] = $this->assemble($value,null,$phone,$rid);
                continue;
            }
            $college_name = $college->college_name;
            $data_push[$key] = $this->assemble($value,$college_name,$phone,$rid);
        }
        $view = new ViewModel(array(
            'column' => $column,
            'teacher' => $data_push,
            'paginator' => $paginator,
            'pageCount' => $total_page,
            'pagesInRange' => $pagesInRange,
            'previous' => $current_page > 1 ? $current_page - 1 : null,
            'next' => $current_page < $total_page ? $current_page + 1 : null,
            'total_num' => $total_num,
            'current' => $current_page,
            'form' => $form,
            'form1'=>$form1,
            'rid_arr'=>$rid_arr
        ));
        return $view;

    }

    public function manageRegisterAction(){
        $login_id_container = new Container('uid');
        $login_id = $login_id_container->item;
        if (is_null($login_id)) {
            echo "<script> alert('您未登录，尚无权访问！');window.location.href='/info';</script>";
        }
        $rid_container = new Container('rid');
        $rid_arr = $rid_container->item;//login 用户的权限
        if (is_null($rid_arr)) {
            echo "<script> alert('系统中未查到您的权限，尚无权访问！');window.location.href='/info';</script>";
        }
        if (!in_array("9",$rid_arr) && !in_array("11",$rid_arr)){
            echo "<script> alert('您无权访问这个页面！');window.location.href='/info';</script>";
        }
        $college_id_container = new Container('college_id');
        $college_id = $college_id_container->item;
        $college_status = $this->getCollegeTable()->getCollege($college_id)->open;
        $college_name = $this->getCollegeTable()->getCollege($college_id)->college_name;

        if ($college_status == "1"){
            $college_check = "checked";
        }else{
            $college_check = "unchecked";
        }

        $subjetcArr = $this->getSubjectTable()->getSubjectsByCid($college_id)->toArray();
        $subjectRes = array();
        foreach ($subjetcArr as $subject ){
            $temp['subject_id'] = $subject['subject_id'];
            $temp['subject_name'] = $subject['subject_name'];
            if ($subject['open'] == "1"){
                $temp['open'] = "checked";
            }else{
                $temp['open'] = "unchecked";
            }
            $subjectRes[] = $temp;
        }

        return array(
            "college_name" => $college_name,
            "college_id" => $college_id,
            "college_check" => $college_check,
            "subjectRes" => $subjectRes
        );
    }
    public function manageRegisterSwitchAction(){
        $login_id_container = new Container('uid');
        $login_id = $login_id_container->item;
        if (is_null($login_id)) {
            echo "<script> alert('您未登录，尚无权访问！');window.location.href='/info';</script>";
        }
        $rid_container = new Container('rid');
        $rid_arr = $rid_container->item;//login 用户的权限
        if (is_null($rid_arr)) {
            echo "<script> alert('系统中未查到您的权限，尚无权访问！');window.location.href='/info';</script>";
        }
        if (!in_array("9",$rid_arr) && !in_array("11",$rid_arr)){
            echo "<script> alert('您无权访问这个页面！');window.location.href='/info';</script>";
        }
        $college_id_container = new Container('college_id');
        $college_id = $college_id_container->item;
        $college_name = $this->getCollegeTable()->getCollege($college_id)->college_name;
        $request = $this -> getRequest();
        if ($request->isPost()){
            //开关
            $switch = $_POST['switch'];
            //学院、学科id
            $id = $_POST['id'];
            $idArr = explode("_",$id);
            $name = $idArr[0];
            if ($name == "college"){
                $college_id = $idArr[1];
                $data = array(
                    'open' => $switch
                );
                $resOfCol = $this->getCollegeTable()->updateCollege($data,$college_id);
                $resOfSub = $this->getSubjectTable()->updateSubjectOfCollege($data,$college_id);
                if ($resOfCol && $resOfSub){
                    if ($switch == 1){
                        echo $college_name."注册已开启";
                    }elseif($switch == 2){
                        echo $college_name."注册已关闭";
                    }
                }
                exit;
            }elseif ($name == "subject"){
                if ($switch == 1){
                    $col = $this->getCollegeTable()->getCollege($college_id);
                    if ($col -> open == 2){
                        $data = array(
                            'open' => $switch
                        );
                        $resOfCol = $this->getCollegeTable()->updateCollege($data,$college_id);
                    }
                }
                $subject_id = $idArr[1];
                $data = array(
                    'open' => $switch
                );
                $resOfSub = $this->getSubjectTable()->updateSubject($data,$college_id,$subject_id);
                $checkOfCol = $this->getSubjectTable()->checkCollege($college_id);
                $subject_name = $this->getSubjectTable()->getSubjectByCond(array(
                    'college_id' => $college_id,
                    'subject_id' => $subject_id
                ))->subject_name;
                if($checkOfCol){//如果学科全部关闭，则应关闭学院
                    $this->getCollegeTable()->updateCollege($data,$college_id);
                }
                if ($resOfSub){
                    if ($switch == 1){
                        echo $subject_name."注册已开启";
                    }elseif($switch == 2){
                        echo $subject_name."注册已关闭";
                    }
                }
                exit;
            }
            exit;
        }
    }
    /**
     * Author:lrn(Adela_Lee)
     * Function:
     * Notes:
     * @return ViewModel
     * Date: 2020-09-14
     * Time: 11:01
     */
public function addUserAction(){
        $view = new ViewModel(array());
        $login_id_container = new Container('uid');
        $login_id = $login_id_container->item;
        if (is_null($login_id)) {
            echo "<script> alert('您未登录，尚无权访问！');window.location.href='/info';</script>";
//            $login_id = 8004044;   //测试用
        }
        $rid_container = new Container('rid');
        $rid_arr = $rid_container->item;//login 用户的权限
        if (is_null($rid_arr)) {
            echo "<script> alert('系统中未查到您的权限，尚无权访问！');window.location.href='/info';</script>";
//            $rid_arr =array(9,10,11,14);  //测试
        }
        $college_id_container = new Container('college_id');
        $college_id = $college_id_container->item;
        if (is_null($college_id)) {
//            $college_id = array('004');  //信息学院，测试用，记得删
        }
        $roles_arr = $this->getRolesArr($rid_arr);
        if (!in_array(10,$rid_arr)) {//不是研究生院
            $college_info = $this->getTBaseCollegeTable()->getCollege($college_id);
            if (is_null($college_info)) {
                $college_info = $this->getTBaseCollegeTable()->getCollegebyStaffid($login_id);
            }
            $search_college_arr[$college_info->college_id] = $college_info->college_name;
        } else {
            $search_college_arr = $this->getTBaseCollegeTable()->getCollegesIDNameArr();
        }
        $form = new PersonalForm($roles_arr,$search_college_arr);
        $request = $this->getRequest();
        if($request->isPost()){
            $account = new UserTeacher();
            //$form->setInputFilter($account->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $data = $form->getData();
                if($data['Password'] == $data['Password2']){
                    //生成salt
                    $psw = md5($data['Password']);
                    $salt = substr(md5(uniqid()), 1, 5);
//                    $password = md5($salt . strtoupper(trim($data['Password'])) . $salt);
                    $password = md5(trim($data['Password']));
                    $LastUid = $this->getUsrTeacherTable()->getLastUID();
                    $insert_data = array(
                        'staff_id' => $LastUid+1,
                        'user_name'=> strip_tags(trim($data['Realname'])),
                        'email'=> strip_tags(trim($data['Email'])),
                        'salt'=> $salt,
                        'password'=> $password,
                        'create_time'=> date('Y-m-d H:i:s'),
                        'update_at'=> null,
                        'rid'=>$data['Rid'],
                    );
                    $account->exchangeArray($insert_data);
                    //用户已存在
                    if($this->getUsrTeacherTable()->registercheck($insert_data['email'])){
                        $view->setVariables(array('form'=>$form, 'msg'=>'用户已存在,插入失败'));
                        return $view;
                    }
                    //save
                    $res1 = $this->getUsrTeacherTable()->saveUser2($insert_data);
                    $base_staff_arr = new Staff();
                    $insert_staff_arr=array(
                        'staff_id' =>$LastUid+1,
                        'staff_name' => strip_tags(trim($data['Realname'])),
                        'college_id' => $data['YXSM'],
                        'title' => null,
                        'phone' => $data['Mobile'],
                        'cellphone' => $data['Mobile'],
                        'email' => $data['Email'],
                        'position' => null,
                    );
                    $base_staff_arr->exchangeArray($insert_staff_arr);
                    $res3 = $this->getStaffTable()->saveStaff($base_staff_arr);

                    //添加教师用户的教师权限
                    $insert_rid2 = new Userrole();
                    $insert_rid2_arr=array(
                        'uid' => $insert_data['staff_id'],
                        'rid' => '2'
                    );
                    $insert_rid2->exchangeArray($insert_rid2_arr);
                    $res5 = $this->getUsrRole()->saveUserrole($insert_rid2);

                    //添加到base_staff表里！！！！！
                    $insert_rid = new Userrole();
                    $insert_rid_arr=array(
                        'uid' => $insert_data['staff_id'],
                        'rid' => $insert_data['rid']
                    );
                    $insert_rid->exchangeArray($insert_rid_arr);
                    $res2 = $this->getUsrRole()->saveUserrole($insert_rid);


                    //添加base_college的manager id
                    if($insert_data['rid'] == '9'){
                        $update_basecol = $this->getTBaseCollegeTable()->getCollege($data['YXSM']);
                        $update_basecol->manager_id = $insert_data['staff_id'];
                        $res4 = $this->getTBaseCollegeTable()->saveCollege($update_basecol);
                    }
                    //添加base_college的secretary id
                    if($insert_data['rid'] == '11'){
                        $update_basecol = $this->getTBaseCollegeTable()->getCollege($data['YXSM']);
                        $update_basecol->dean_id = $insert_data['staff_id'];
                        $res6 = $this->getTBaseCollegeTable()->saveCollege($update_basecol);
                    }
                    //判断是否插入成功否则回滚
                    if($res1&&$res2&&$res3&&$res5){
                        echo "<script type=\"text/javascript\" >alert('新增用户成功!');</script>";
                    }else{
                        echo "<script type=\"text/javascript\" >alert('新增用户失败!');</script>";
                        if(!$res1) {//usr_teacher insert failed,删掉一行usr_user_role
                            $this->getUsrRole()->deleteLastInsert();
                        } elseif(!$res2){//usr_user_role插入失败则删掉一行usr_teacher
                            $last_insert = $this->getUsrTeacherTable()->getLastUID();
                            $this->getUsrTeacherTable()->deleteUser($last_insert);
                        }
                        elseif(!$res3){
                            $last_insert = $this->getStaffTable()->getLastUID();
                            $this->getStaffTable()->deletestaff($last_insert);
                        }
                    }
                }
            }else
                echo "<script>alert('提交失败，请检查表单是否填写正确')</script>";
        }
        $view = new ViewModel(array(
            'form' => $form,
            'rid'=>$rid_arr,
        ));
        return $view;
    }
    public function addUserioriAction(){
        $view = new ViewModel(array());
        $login_id_container = new Container('uid');
        $login_id = $login_id_container->item;
        if (is_null($login_id)) {
            echo "<script> alert('您未登录，尚无权访问！');window.location.href='/info';</script>";
//            $login_id = 8004044;   //测试用
        }
        $rid_container = new Container('rid');
        $rid_arr = $rid_container->item;//login 用户的权限
        if (is_null($rid_arr)) {
            echo "<script> alert('系统中未查到您的权限，尚无权访问！');window.location.href='/info';</script>";
//            $rid_arr =array(9,10,11,14);  //测试
        }
        $college_id_container = new Container('college_id');
        $college_id = $college_id_container->item;
        if (is_null($college_id)) {
//            $college_id = array('004');  //信息学院，测试用，记得删
        }
        $roles_arr = $this->getRolesArr($rid_arr);
        if (!in_array(10,$rid_arr)) {//不是研究生院
            $college_info = $this->getTBaseCollegeTable()->getCollege($college_id);
            if (is_null($college_info)) {
                $college_info = $this->getTBaseCollegeTable()->getCollegebyStaffid($login_id);
            }
            $search_college_arr[$college_info->college_id] = $college_info->college_name;
        } else {
            $search_college_arr = $this->getTBaseCollegeTable()->getCollegesIDNameArr();
        }
        $form = new PersonalForm($roles_arr,$search_college_arr);
        $request = $this->getRequest();
        if($request->isPost()){
            $account = new UserTeacher();
            //$form->setInputFilter($account->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid()){
                $data = $form->getData();
                if($data['Password'] == $data['Password2']){
                    //生成salt
                    $psw = md5($data['Password']);
                    $salt = substr(md5(uniqid()), 1, 5);
//                    $password = md5($salt . strtoupper(trim($data['Password'])) . $salt);
                    $password = md5(trim($data['Password']));
                    $LastUid = $this->getUsrTeacherTable()->getLastUID();
                    $insert_data = array(
                        'staff_id' => $LastUid+1,
                        'user_name'=> strip_tags(trim($data['Realname'])),
                        'email'=> strip_tags(trim($data['Email'])),
                        'salt'=> $salt,
                        'password'=> $password,
                        'create_time'=> date('Y-m-d H:i:s'),
                        'update_at'=> null,
                        'rid'=>$data['Rid'],
                    );
                    $account->exchangeArray($insert_data);
                    //用户已存在
                    if($this->getUsrTeacherTable()->registercheck($insert_data['email'])){
                        $view->setVariables(array('form'=>$form, 'msg'=>'用户已存在,插入失败'));
                        return $view;
                    }
                    //save
                    $res1 = $this->getUsrTeacherTable()->saveUser2($insert_data);
                    $base_staff_arr = new Staff();
                    $insert_staff_arr=array(
                        'staff_id' =>$LastUid+1,
                        'staff_name' => strip_tags(trim($data['Realname'])),
                        'college_id' => $data['YXSM'],
                        'title' => null,
                        'phone' => $data['Mobile'],
                        'cellphone' => $data['Mobile'],
                        'email' => $data['Email'],
                        'position' => null,
                    );
                    $base_staff_arr->exchangeArray($insert_staff_arr);
                    $res3 = $this->getStaffTable()->saveStaff($base_staff_arr);

                    //添加教师用户的教师权限
                    $insert_rid2 = new Userrole();
                    $insert_rid2_arr=array(
                        'uid' => $insert_data['staff_id'],
                        'rid' => '2'
                    );
                    $insert_rid2->exchangeArray($insert_rid2_arr);
                    $res5 = $this->getUsrRole()->saveUserrole($insert_rid2);

                    //添加到base_staff表里！！！！！
                    $insert_rid = new Userrole();
                    $insert_rid_arr=array(
                        'uid' => $insert_data['staff_id'],
                        'rid' => $insert_data['rid']
                    );
                    $insert_rid->exchangeArray($insert_rid_arr);
                    $res2 = $this->getUsrRole()->saveUserrole($insert_rid);


                    //添加base_college的manager id
                    if($insert_data['rid'] == '9'){
                        $update_basecol = $this->getTBaseCollegeTable()->find($data['YXSM']);
                        $update_basecol->manager_id = $insert_data['staff_id'];
                        $res4 = $this->getTBaseCollegeTable()->saveCollege($update_basecol);
                    }
                    //添加base_college的secretary id
                    if($insert_data['rid'] == '11'){
                        $update_basecol = $this->getTBaseCollegeTable()->find($data['YXSM']);
                        $update_basecol->secretary_id = $insert_data['staff_id'];
                        $res6 = $this->getTBaseCollegeTable()->saveCollege($update_basecol);
                    }
                    //判断是否插入成功否则回滚
                    if($res1&&$res2&&$res3&&$res5){
                        echo "<script type=\"text/javascript\" >alert('新增用户成功!');</script>";
                    }else{
                        echo "<script type=\"text/javascript\" >alert('新增用户失败!');</script>";
                        if(!$res1) {//usr_teacher insert failed,删掉一行usr_user_role
                            $this->getUsrRole()->deleteLastInsert();
                        } elseif(!$res2){//usr_user_role插入失败则删掉一行usr_teacher
                            $last_insert = $this->getUsrTeacherTable()->getLastUID();
                            $this->getUsrTeacherTable()->deleteUser($last_insert);
                        }
                        elseif(!$res3){
                            $last_insert = $this->getStaffTable()->getLastUID();
                            $this->getStaffTable()->deletestaff($last_insert);
                        }
                    }
                }
            }else
                echo "<script>alert('提交失败，请检查表单是否填写正确')</script>";
        }
        $view = new ViewModel(array(
            'form' => $form,
        ));
        return $view;
    }
    public function assemble($data_arr,$college,$mobile,$rid){

        $res_arr = array(
            'staff_id' => $data_arr['staff_id'],
            'real_name' => $data_arr['user_name'],
            'user_name' => $data_arr['email'],
            'college' => $college,
            'mobile' => $mobile,
            'create_time' => $data_arr['create_at'],
            'rid' => $rid,
        );
        return $res_arr;
    }

    public function deleteUserAction(){
        //从url中读出staff id
        $staff_id = $this->params()->fromRoute('param1');
        //删权限
        $uid_rid = $this->getUsrRole()->getRidArr($staff_id)->toArray();
        $usr_arr = array();
        foreach ($uid_rid as $k => $v){
            $usr_arr[$k]['uid'] = $staff_id;
            $usr_arr[$k]['rid'] = $v['rid'];
        }
        //删除所有权限
        if(!empty($usr_arr)){
            $res1 = $this->getUsrRole()->deleteUsrRid($staff_id);
        }else
            $res1 = false;

        //删除teacher表记录
        $res2 = $this->getUsrTeacherTable()->deleteUser($staff_id);
        if($res1 && $res2){
            echo "<script>alert('删除成功')</script>";
        }else{
            if(!$res1){//权限删除失败
                echo "<script>alert('权限删除失败')</script>";
            }elseif(!$res2){//teacher注册表删除失败
                echo "<script>alert('teacher表删除失败')</script>";
            }
        }

        //路由名 参数
        return $this->redirect()->toRoute('management/default', array('controller' => 'SystemManagement', 'action' => 'others'));
    }
    public function initialPasswordAction(){
        //从url中读出staff id
        $staff_id = $this->params()->fromRoute('param1');
        //初始化密码
        $res = $this->getUsrTeacherTable()->getUser($staff_id);
        print_r($res);
        $tea = $res;
        $tea->password = md5($tea->email);
        print_r($tea);
        $res2 = $this->getUsrTeacherTable()->saveUsrteacher($tea);
        if($res2)
            echo "<script>alert('初始化密码成功')</script>";
        else{
            echo "<script>alert('初始化密码失败，请重试或上报管理人员')</script>";
        }
        //路由名 参数
        return $this->redirect()->toRoute('management/default', array('controller' => 'SystemManagement', 'action' => 'others'));
    }

    public function getTBaseCollegeTable()
    {
        if (!$this->TBaseCollegeTable) {
            $sm = $this->getServiceLocator();
            $this->TBaseCollegeTable = $sm->get('Basicinfo\Model\CollegeTable');
        }
        return $this->TBaseCollegeTable;
    }
    public function getUsrTeacherTable()
    {
        if (!$this->UsrTeacherTable) {
            $sm = $this->getServiceLocator();
            $this->UsrTeacherTable = $sm->get('User\Model\UserTeacherTable');
        }
        return $this->UsrTeacherTable;
    }
    public function getUsrRole()
    {
        if (!$this->UsrRoleTable) {
            $sm = $this->getServiceLocator();
            $this->UsrRoleTable = $sm->get('User\Model\UserroleTable');
        }
        return $this->UsrRoleTable;
    }
    public function getRoleNameTable()
    {
        if (!$this->RoleNameTable) {
            $sm = $this->getServiceLocator();
            $this->RoleNameTable = $sm->get('Manage\Model\RoleNameTable');
        }
        return $this->RoleNameTable;
    }
    public function getStaffTable()
    {
        if (!$this->StaffTable) {
            $sm = $this->getServiceLocator();
            $this->StaffTable = $sm->get('Basicinfo\Model\StaffTable');
        }
        return $this->StaffTable;
    }
    public function getConfigKeyTable(){
        if (!$this->configKeyTable) {
            $sm = $this->getServiceLocator();
            $this->configKeyTable = $sm->get('Setting\Model\ConfigTable');
        }
        return $this->configKeyTable;
    }
    protected function getCollegeTable()
    {
        if (!$this->college_table) {
            $sm = $this->getServiceLocator();
            $this->college_table = $sm->get('Basicinfo\Model\CollegeTable');
        }
        return $this->college_table;
    }
    public function getSubjectTable()
    {
        if (!$this->subject_table) {
            $sm = $this->getServiceLocator();
            $this->subject_table = $sm->get('Basicinfo\Model\SubjectTable');
        }
        return $this->subject_table;
    }
}

