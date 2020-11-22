<?php
/**
 * 功能类
 * @author gpw
 */

namespace Management\Controller;


use Basicinfo\Model\College;
use Basicinfo\Model\Staff;
use Management\Form\resultForm;
use User\Model\Userrole;
use User\Model\UserTeacher;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class ManagementController extends AbstractActionController
{

    //类变量
    private $uid;
    private $rid;
    private $cid;

    private $TBaseCollegeTable;//base_college
    protected $staff_table;//base_staff
    protected $usrteacherTable;//user_teacher
    protected $userroleTable;
    protected $collegeTable;

    /**
     * 构造器
     * @todo 权限优化
     * @todo 用户结构
     * SystemManagementController constructor.
     */
    public function __construct(){
//        $permissionControl = new PermissionControll();
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
            exit("请先登录");
        }
        if(!in_array(10,$this->rid)){
            exit("您没有用户权限");
        }
    }

    /**
     * 生成照片zip
     * @author gpw
     */
    public function PhotoAction(){
        if(!in_array(10,$this->rid)){
            echo "<script>alert('您无此权限，请返回上一页')</script>";
            exit;
        }

        //获取系统根目录
        $server_root=$_SERVER['DOCUMENT_ROOT'];

        //系统存放照片根路径
        $path_photo_root = $server_root."/img/allPhoto";

        //系统存放所有照片路径
        $path_photo = $path_photo_root."/photo";

        //1.删除旧的照片压缩文件
        $old_zip_path = $path_photo_root."/allPhoto.zip";
        if(file_exists($old_zip_path)){
            unlink($old_zip_path);
        }

        //2.删除旧的文件
        if(file_exists($path_photo)){
            $this->deldir($path_photo);
        }

        //获取所有处于录取状态的uid
        $adapter = $this->dbAdapter();
        $s1 ="SELECT uid FROM `stu_check` WHERE `status`=10";
        $resultUIDAll = $adapter->query($s1)->execute();
        $uidArr = iterator_to_array($resultUIDAll);

        /**
         * 一些参数
         * @todo 目前没有在页面返回，之后可以把这些参数进行体现
         */
        $emptyPhoto = 0;//以录取但没有上传照片
        $emptyName = array();//以录取但没有上传照片的学生姓名
        $errPhoto =0;//以录取但找不该学生信息
        $allStu = count($uidArr);//所有录取学生人数
        $sucStu = 0;

        //存放所有图片的路径地址
        $all_photo_path = array();

        //压缩文件
        $zip = new \ZipArchive();

        //压缩目录(照片根目录的下一级)
        $zip_path = $path_photo_root."/";

        //压缩文件名称
        $zip_name = "allPhoto.zip";

        foreach ($uidArr as $key=>$value){
            //查学院名称，学科名称，学生姓名，学生身份证号
            $s3 = "SELECT bc.college_name,bs.subject_name,r1.user_name,r1.idcard FROM base_college as bc, base_subject as bs ,(SELECT user_name,idcard,target_college,target_subject FROM stu_base WHERE uid = ".$value['uid'].") as r1 where bc.college_id = r1.target_college AND bs.subject_id = r1.target_subject";
            $stuInfo = $adapter->query($s3)->execute();
            $stuInfoArr = iterator_to_array($stuInfo);
            $collegeName = $stuInfoArr[0]['college_name'];//学院
            $subjectName = $stuInfoArr[0]['subject_name'];//学科
            $stuName = $stuInfoArr[0]['user_name'];//姓名
            $stuId = $stuInfoArr[0]['idcard'];//身份证


            if(is_null($collegeName)||is_null($subjectName)||is_null($stuName)||is_null($stuId)){
                //如果为空，错误信息++
                $errPhoto++;
            }else{
                //如果不为空，判断文件是否存在
                $photo_add=$server_root."/img/stu/".$value['uid']."/einfo/1/1_1.";
                $photo_add_path="";
                $fileName = $collegeName."_".$subjectName."_".$stuName."_".$stuId;
                if(file_exists($photo_add."jpg")==true){
                    $photo_add_path=$photo_add."jpg";
                    $fileName.=".jpg";
                }
                else if(file_exists($photo_add."jpeg")==true){
                    $photo_add_path=$photo_add."jpeg";
                    $fileName.=".jpeg";
                }
                else if(file_exists($photo_add."png")==true){
                    $photo_add_path=$photo_add."png";
                    $fileName.=".png";
                }else{
                     $emptyPhoto++;
                     array_push($emptyName,$stuName);
                }
                if(file_exists($photo_add_path)){

                    //复制图片路径到/img/allPhoto/photo/路径下
                    $this->file2dir($photo_add_path,$path_photo."/",$fileName);

                    $sucStu++;

                    //将路径写入路径数组
                    array_push($all_photo_path,array('path'=>$path_photo."/".$fileName,'filename'=>$fileName));
                }

            }
        }

        if(empty($all_photo_path)){
            if($allStu==0){
                echo "<script>alert('当前拟录取人数为0');</script>";

            }else{
                echo "<script>alert('拟录取学生未上传照片');</script>";
            }
            exit("请返回上一页");
        }

        $context = "拟录取人数:".$allStu."\r\n 下载照片总数:".$sucStu."\r\n 未上传照片考生数:".$emptyPhoto."\r\n 系统未找到人数:".$errPhoto;

        //添加压缩包
        if ($zip->open($zip_path . $zip_name,  \ZipArchive::OVERWRITE |\ZipArchive::CREATE) == TRUE) {

            //添加注释文件
            $zip->addFromString("1.导出帮助.txt",$context);

            //遍历地址路径，把图片添加到压缩包里
            if(!empty($all_photo_path)){
                foreach ($all_photo_path as $key=>$value){
                    $zip->addFile($value['path'],$value['filename']);//添加文件
                }
            }

            $zip->close();
        }

        $download=$zip_path.$zip_name;
        header("Cache-Control: max-age=0");
        header("Content-Description: File Transfer");
        header('Content-Type:application/zip');
        header('Content-disposition:attachment;filename=' . basename($download));
        header("Content-Transfer-Encoding: binary");//告诉浏览器二进制传输
        $filesize = filesize($download);
        header('Content-length:' . $filesize);
        ob_clean();
        flush();
        readfile($download);

        unlink($download);//删除压缩包

        //删除旧的文件
        if(file_exists($path_photo)){
            $this->deldir($path_photo);
        }

    }


    //删除文件目录
    public function deldir($dir) {
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }

        closedir($dh);
        return 1;
    }


    //复制文件到指定目录
    function file2dir($sourcefile, $dir,$filename)
    {
        if (!file_exists($sourcefile)) {
            return false;
        }
        return copy($sourcefile, $dir . '' . $filename);
    }


    public function dbAdapter(){
        return $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
    }

    /**
     * 下载学院用户excel
     * 研究生院
     * @author gpw
     */
    public function downloadExcelAction(){
        $adapter = $this->dbAdapter();

        //1.查找所有学院名称和学院id放到excel表中
        $sql_findCollege = "select college_id,college_name,total_stu,free_stu from base_college";
        $collegeTemp = $adapter->query($sql_findCollege)->execute();
        $collegeArr = iterator_to_array($collegeTemp);

        //2.excel表的title
        $title = array(
            'college_id'=>'学院编号',
            'college_name'=>'学院名称',
            'manage_name'=>'院长姓名',
            'manage_email'=>'院长邮箱',
            'manage_password'=>'院长密码',
            'manage_phone'=>'院长电话',
            'dean_name'=>'院秘书姓名',
            'dean_email'=>'院秘书邮箱',
            'dean_password'=>'院秘书密码',
            'dean_phone'=>'院秘书电话',
            'total_stu'=>'招生总数',
            'free_stu'=>'推免人数'
        );
        $view = new ViewModel(array(
            'title'=>$title,
            'info'=>$collegeArr,
        ));
        $view->setTerminal(true);
        return $view;
    }

    /**
     * 上传用户excel
     * 研究生
     * @author gpw
     */
 public function loadExcelAction(){
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
        if(!in_array(10,$rid_arr)){
            echo "<script> alert('无权访问！');window.location.href='/info';</script>";
        }
        $resForm = new resultForm();
        $request = $this->getRequest();
        $adapter = $this->dbAdapter();
        if($request->isPost()){
            $arrayData = $request->getPost()->toArray();
            if (!strcmp($arrayData['submit'], '上传并更新')) {
                $postData = array_merge_recursive(//文件信息合并到postdata数组
                    $request->getFiles()->toArray()
                );
                $resForm->setData($postData);
                if ($resForm->isValid()) {//表单检验成功
                    $data = $resForm->getData();
                    $now = strtotime('now');

                    //1.获取根目录
                    $server_root=$_SERVER['DOCUMENT_ROOT'];
                    $path = $server_root.'/uploadUser';

                    //2.判断文件夹是否存在
                    $this->creatFile($path);

                    //3.移动文件到/uploadUser/userTime.csv
                    $filePath =$server_root.'/uploadUser/user' . $now . '.csv';
                    move_uploaded_file($data['upload_file']['tmp_name'], $filePath);

                    //4.加载csv数据
                    $data = $this->loadCsv($filePath);

                    //var_dump($data);
                    $managerCount = 0;
                    $deanCount = 0;

                    //5.遍历数据进行插入
                    foreach ($data as $key=>$collegeArr){
                        //var_dump($data);

                        $college_id = $collegeArr[0];//学院编号
                        $college_name = $collegeArr[1];//学院名称

                        $manage_name = !empty($collegeArr[2])? $collegeArr[2] : null;//院长姓名

                        $dean_name = !empty($collegeArr[6])? $collegeArr[6] : null;//秘书姓名

                        $total_stu = !empty($collegeArr[10])? $collegeArr[10] : 0; //招生总数

                        $temp_free_stu = trim($collegeArr[11]);
                        $free_stu = !empty($temp_free_stu)? $temp_free_stu : 0; //推免人数

                        $sql_insert_stu_num = "update base_college set total_stu =".$total_stu.", free_stu = ".$free_stu." where college_id= '".$college_id."'";
                        $adapter->query($sql_insert_stu_num)->execute();

                        //如果院长的名字不为空则插入院长
                        if(!is_null($manage_name)){
                            $manage_mail = !empty($collegeArr[3])? $collegeArr[3] : null;//院长邮箱
                            $manage_password = !empty($collegeArr[4])? $collegeArr[4]: $manage_mail;//院长密码
                            $manage_cellphone = !empty($collegeArr[5])? $collegeArr[5]: null;//院长电话

                            $staffId = $this->judgeUser($manage_name,$college_id,$manage_mail,$manage_cellphone);

                            //判断是否是当前院长
                            $sql_judgeManagement = "select manager_id from base_college where college_id = '".$college_id."'";
                            $tempManage = iterator_to_array($adapter->query($sql_judgeManagement)->execute());
                            if($staffId != $tempManage[0]['manage_id']){
                                $sql_insertManage = "update base_college set manager_id = '".$staffId."' where college_id='".$college_id."'";
                                $adapter->query($sql_insertManage)->execute();
                                $this->shouQuan($staffId,9,$manage_password);

                                $managerCount++;//

                                if(!empty($tempManage[0])){
                                    $this->quXiaoQuan($tempManage[0],9);
                                }
                            }
                        }

                        if(!is_null($dean_name)){
                            $dean_mail = !empty($collegeArr[7])? $collegeArr[7] : null;//秘书邮箱
                            $temp_dean_pwd = trim($collegeArr[8]);
                            $dean_password = !empty($temp_dean_pwd)? trim($collegeArr[8]): $dean_mail;//秘书密码
                            $temp_dean_cellphone = trim($collegeArr[9]);
                            $dean_cellphone = !empty($temp_dean_cellphone)? $temp_dean_cellphone : null;
                 //           var_dump($dean_password);
                            $staffId = $this->judgeUser($dean_name,$college_id,$dean_mail,$dean_cellphone);

                            //判断是否是当前秘书
                            $sql_judgeDean = "select dean_id from base_college where college_id = '".$college_id."'";
                            $tempManage = iterator_to_array($adapter->query($sql_judgeDean)->execute());
                            if($staffId != $tempManage[0]['dean_id']){
                                echo $staffId."秘书不相同<br>";
                                $sql_insertDean = "update base_college set dean_id = '".$staffId."' where college_id='".$college_id."'";
                                $adapter->query($sql_insertDean)->execute();
                                $this->shouQuan($staffId,11,$dean_password);

                                $deanCount++;

                                if(!empty($tempManage[0])){
                                    $this->quXiaoQuan($tempManage[0],11);
                                }
                            }
                        }
                    }
                    unlink($filePath);//删除文件
                    echo "<script>alert('成功添加".$managerCount."位院长;成功添加".$deanCount."位学院秘书');window.location.href='/management/management/LoadExcel';</script>";
                    exit();
                }
            }
        }
        return array(
            'form'=>$resForm,
            'rid' => $rid_arr,
        );
    }
    public function loadExceloriAction(){
        $resForm = new resultForm();
        $request = $this->getRequest();
        $adapter = $this->dbAdapter();
        if($request->isPost()){
            $arrayData = $request->getPost()->toArray();
            if (!strcmp($arrayData['submit'], '上传并更新')) {
                $postData = array_merge_recursive(//文件信息合并到postdata数组
                    $request->getFiles()->toArray()
                );
                $resForm->setData($postData);
                if ($resForm->isValid()) {//表单检验成功
                    $data = $resForm->getData();
                    $now = strtotime('now');

                    //1.获取根目录
                    $server_root=$_SERVER['DOCUMENT_ROOT'];
                    $path = $server_root.'/uploadUser';

                    //2.判断文件夹是否存在
                    $this->creatFile($path);

                    //3.移动文件到/uploadUser/userTime.csv
                    $filePath =$server_root.'/uploadUser/user' . $now . '.csv';
                    move_uploaded_file($data['upload_file']['tmp_name'], $filePath);

                    //4.加载csv数据
                    $data = $this->loadCsv($filePath);

                    //var_dump($data);
                    $managerCount = 0;
                    $deanCount = 0;

                    //5.遍历数据进行插入
                    foreach ($data as $key=>$collegeArr){
                        //var_dump($data);

                        $college_id = $collegeArr[0];//学院编号
                        $college_name = $collegeArr[1];//学院名称

                        $manage_name = !empty($collegeArr[2])? $collegeArr[2] : null;//院长姓名

                        $dean_name = !empty($collegeArr[6])? $collegeArr[6] : null;//秘书姓名

                        $total_stu = !empty($collegeArr[10])? $collegeArr[10] : 0; //招生总数

                        $temp_free_stu = trim($collegeArr[11]);
                        $free_stu = !empty($temp_free_stu)? $temp_free_stu : 0; //推免人数

                        $sql_insert_stu_num = "update base_college set total_stu =".$total_stu.", free_stu = ".$free_stu." where college_id= '".$college_id."'";
                        $adapter->query($sql_insert_stu_num)->execute();

                        //如果院长的名字不为空则插入院长
                        if(!is_null($manage_name)){
                            $manage_mail = !empty($collegeArr[3])? $collegeArr[3] : null;//院长邮箱
                            $manage_password = !empty($collegeArr[4])? $collegeArr[4]: $manage_mail;//院长密码
                            $manage_cellphone = !empty($collegeArr[5])? $collegeArr[5]: null;//院长电话

                            $staffId = $this->judgeUser($manage_name,$college_id,$manage_mail,$manage_cellphone);

                            //判断是否是当前院长
                            $sql_judgeManagement = "select manager_id from base_college where college_id = '".$college_id."'";
                            $tempManage = iterator_to_array($adapter->query($sql_judgeManagement)->execute());
                            if($staffId != $tempManage[0]['manage_id']){
                                $sql_insertManage = "update base_college set manager_id = '".$staffId."' where college_id='".$college_id."'";
                                $adapter->query($sql_insertManage)->execute();
                                $this->shouQuan($staffId,9,$manage_password);

                                $managerCount++;//

                                if(!empty($tempManage[0])){
                                    $this->quXiaoQuan($tempManage[0],9);
                                }
                            }
                        }

                        if(!is_null($dean_name)){
                            $dean_mail = !empty($collegeArr[7])? $collegeArr[7] : null;//秘书邮箱
                            $temp_dean_pwd = trim($collegeArr[8]);
                            $dean_password = !empty($temp_dean_pwd)? trim($collegeArr[8]): $dean_mail;//秘书密码
                            $temp_dean_cellphone = trim($collegeArr[9]);
                            $dean_cellphone = !empty($temp_dean_cellphone)? $temp_dean_cellphone : null;
                 //           var_dump($dean_password);
                            $staffId = $this->judgeUser($dean_name,$college_id,$dean_mail,$dean_cellphone);

                            //判断是否是当前秘书
                            $sql_judgeDean = "select dean_id from base_college where college_id = '".$college_id."'";
                            $tempManage = iterator_to_array($adapter->query($sql_judgeDean)->execute());
                            if($staffId != $tempManage[0]['dean_id']){
                                echo $staffId."秘书不相同<br>";
                                $sql_insertDean = "update base_college set dean_id = '".$staffId."' where college_id='".$college_id."'";
                                $adapter->query($sql_insertDean)->execute();
                                $this->shouQuan($staffId,11,$dean_password);

                                $deanCount++;

                                if(!empty($tempManage[0])){
                                    $this->quXiaoQuan($tempManage[0],11);
                                }
                            }
                        }
                    }
                    unlink($filePath);//删除文件
                    echo "<script>alert('成功添加".$managerCount."位院长;成功添加".$deanCount."位学院秘书');window.location.href='/management/management/LoadExcel';</script>";
                    exit();
                }
            }
        }
        return array(
            'form'=>$resForm
        );
    }

    /**
     * 授予用户权限
     * @param $uid
     * @param $role
     * @param $pwd
     */
    public function shouQuan($uid,$role,$pwd){
        $userrole = new Userrole();
        $user = $this->getUsrteacherTable()->getUser($uid);
        $staff = $this->getStaffTable()->getStaff($uid);//查staff表取uid
        $flag = 0;
        if(!$user){
            $user = new UserTeacher();
            $pd= !is_null($pwd)? $pwd : $staff->email;
            $userData = array(
                'staff_id'=>$uid,
                'user_name' => $staff->staff_name,
                'email' => $staff->email,
                'password' => md5($pd),
            );
            $user->exchangeArray($userData);
            $this->getUsrteacherTable()->saveUsrteacher($user);
            $flag = 1;//要插入userrole表
            $ur_data = array('uid'=>$uid,'rid'=>2);//插入教师角色
            $userrole->exchangeArray($ur_data);
            $this->getUserroleTable()->saveUserrole($userrole);
            $ur_data = array('uid'=>$uid,'rid'=>$role);//身份角色插入，等会再做，和旧老师一起
            //   echo "插入老师成功<br>";
        } else {//echo "新负导师有uid<br/>";echo "uid = ".$staff->uid."<br/>";
            if(!is_null($pwd)){
                $password = md5($pwd);
                $adapter = $this->dbAdapter();
                $updatePWD = "update usr_teacher set password = '".$password."' where staff_id = '".$uid."'";
                $adapter->query($updatePWD)->execute();
            //    echo "更新密码成功<br>";
            }
            $ur_data = array('uid'=>$uid,'rid'=>$role);
            //在userrole里查询他是否有负责人权限
            $ifExist = $this->getUserroleTable()->getUserrole($ur_data);
            $row = $ifExist->current();
            if(!$row){//echo "新导师没有导师角色<br/>";
                $flag = 1;//没有则在userrole里插入
            }
         //   echo "更新老师成功<br>";
        }
        if($flag==1){
            $userrole->exchangeArray($ur_data);
            $this->getUserroleTable()->saveUserrole($userrole);
            //echo "成功授权 用户：$uid ， $rid 角色 <br/>";
        }
    }

    /**
     * 撤销用户权限
     * @param $tid
     * @param $rid
     */
    public function quXiaoQuan($tid,$rid){
        $staff = $this->getStaffTable()->getStaff($tid);//staff表里查这个人的uid
        $row=null;
        $flag2 = 0;
        switch ($rid) {
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
        }
    }

    /**
     * 判断用户是否存在于base_staff表中,不存在插入，存在更新邮箱
     * @param $name
     * @param $cid
     * @param $mail
     * @param $phone
     * @return string 返回用户id
     */
    public function judgeUser($name,$cid,$mail,$phone){
        $res = $this->getStaffTable()->getStaffbyCollegeandName($name, $cid);
        var_dump($res);
        if ( $res == false){
            //如果用户名不存在于base_staff表中，插入数据
            $xh = $this->getStaffXh($cid);
            $staff_id = '8' . $cid . $xh;
            $staff = new Staff();
            $value = array(
                'staff_id' => $staff_id,
                'staff_name' => $name,
                'college_id' => $cid,
                'email'=>$mail,
                'phone'=>$phone,
                'cellphone'=>$phone,
            );
            $staff->exchangeArray($value);
            $result = $this->getStaffTable()->saveStaff($staff);
        }else{
            $value = array(
                'staff_id'=>$res->staff_id,
                'email'=>$mail,
                'cellphone'=>$phone,
            );
            $staff_id = $res->staff_id;
            $result = $this->getStaffTable()->updateStaff($value);
        }
        return $staff_id;
    }

    /**
     * 创建文件夹
     * @author gpw
     * @param $path
     */
    public function creatFile($path){
        if(!file_exists($path)) {
            if(mkdir($path,0777,true)) {
                echo "创建文件夹成功";
            }else{
                echo "创建文件夹失败";
            }
        } else {
            echo "该文件夹已存在";
        }
    }

    /**
     * load csv
     * @param $filepath
     * @return array
     */
    public function loadCsv($filepath){
        $info = "";// 标识信息

        //1.Determine if the CSV file exists
        if (is_file($filepath))
            $file = fopen($filepath, 'r');
        else {
            echo $filepath . " is not a file.<br/>";
            exit;
        }
        //标题行读取（读取第2行）
        $row = fgets($file);
        $encoding = mb_detect_encoding($row, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5", "UNICODE"));
        $row = iconv($encoding, "utf-8//IGNORE", $row);
        $line = explode(",",$row);

        //内容读取
        $data = array();
        $count = 0;
        while (!feof($file)) {
            $row = fgets($file);
            if ($row == "" || $row == " " || $row == "\n")
                continue; //最后一行
            $encoding = mb_detect_encoding($row, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5", "UNICODE"));
            $row = iconv($encoding, "utf-8//IGNORE", $row);
            $line = explode(",",$row);
            $data[] = $line;
            $count ++;
        }
        fclose($file);
        return $data;
    }

    /**
     * 获取教师序号
     * @param $college_id
     * @return string
     */
    public function getStaffXh($college_id){
        $now_college_staff_num = sizeof($this->getStaffTable()->getStaffArrByCid($college_id));
        if(empty($now_college_staff_num)){
            return sprintf('%03s', 1);
        }
        else{
            $now_college_staff_num++;
            return sprintf('%03s', $now_college_staff_num);
        }

    }

    ////数据库////
    public function getStaffTable()
    {
        if (!$this->staff_table) {
            $sm = $this->getServiceLocator();
            $this->staff_table = $sm->get('Basicinfo\Model\StaffTable');
        }
        return $this->staff_table;
    }
    public function getUsrteacherTable(){
        if (! $this->usrteacherTable) {
            $sm = $this->getServiceLocator ();
            $this->usrteacherTable = $sm->get ( 'User\Model\UserTeacherTable' );
        }
        return $this->usrteacherTable;
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
}
