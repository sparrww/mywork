<?php
/*******************************************************************
 * @authors Air
 * @date    2014-09-06
 * @copy    Copyright © 2013-2018 Powered by Air Web Studio  
 *******************************************************************/
// 网站基本信息

namespace  Admin\Controller;

use Lib\Controller as Controller;

class indexController extends Controller{

    public $tablemember     = 'member';
    public $tableuser       = 'fans';
    public $tableprize      = 'prize';
    public $tablequestion   = 'question';
    public $tableaward      = 'award';

    public $info = [];


    public function __construct(){
        global $_GPC,$_W;
        //登陆判断
        //dump($_SESSION);die;
        if(empty($_SESSION[$_GPC['act'].'_login'])) {
            $this->login();
        }

        $this->info = json_decode($_SESSION[$_GPC['act'].'_login'],true);
    }

    public function Manage() {
        global $_GPC,$_W;
        include $this->display('header.html');
    }

    public function Index() {
    }

    public function Prize() {
        global $_GPC,$_W;


        $list = pdo_fetchall('select * from '.tablename($this->tableprize));
        $num = array_sum(array_column($list,'num'));
        $draw = $num-array_sum(array_column($list,'draw'));


        include $this->display('prize.html');
    }

    /**
     * 修改奖品数量,重新计算概率
     */
    public function doAwardEdit() {
        global $_GPC,$_W;
        $account = pdo_fetch('select * from '.tablename($this->tableaccount).' where account="'.self::ACCOUNT.'"');

        if($_W['isajax']){
            if(empty($_GPC['num']) || empty($_GPC['prob'])) message('缺少参数');
            foreach ($_GPC['prob'] as $k=>$v){
                if(intval($v)<0 || intval($v)>100) message($_GPC['prizename'][$k].'概率请在0-100之间');
            }
            foreach ($_GPC['num'] as $k=>$v){
                if(intval($v) < $num = pdo_fetchcolumn('select num from '.tablename($this->tableprize).' where id=:id',[':id'=>$k])) message($_GPC['prizename'][$k].'数量不能小于'.$num);

                $count = 0;
                foreach ($_GPC['num'] as $k=>$v){
                    if($k==1){
                        $count += 1.06*intval($v);
                    }
                    if($k==2){
                        $count += 1.16*intval($v);
                    }
                    if($k==3){
                        $count += 1.26*intval($v);
                    }
                    if($k==4){
                        $count += 1.66*intval($v);
                    }
                    if($k==5){
                        $count += 6*intval($v);
                    }
                    if($k==6){
                        $count += 6.6*intval($v);
                    }
                    if($k==7){
                        $count += 66*intval($v);
                    }
                    if($k==8){
                        $count += 1*intval($v);
                    }
                }
                if($count>$account['total_amount']) message('红包奖品总金额('.$count.')大于账户总金额,请修改奖品数量再次提交!');


            }
            foreach ($_GPC['num'] as $k=>$v){
                pdo_update($this->tableprize,[
                    'num'=>intval($v),
                    'prob'=>intval($_GPC['prob'][$k])
                ],['id'=>$k]);
            }

            message('修改成功','success');
        }
    }

    /**
     * @param $preData
     * @param string $sortType
     * @return array
     * 二维数组按照某字段排序
     */

    function sortArrayAsc($preData,$sortType='sort'){
        $sortData = array();
        foreach ($preData as $key_i => $value_i){
            $price_i = $value_i[$sortType];
            $min_key = '';
            $sort_total = count($sortData);
            foreach ($sortData as $key_j => $value_j){
                if($price_i<$value_j[$sortType]){
                    $min_key = $key_j+1;
                    break;
                }
            }
            if(empty($min_key)){
                array_push($sortData, $value_i);
            }else {
                $sortData1 = array_slice($sortData, 0,$min_key-1);
                array_push($sortData1, $value_i);
                if(($min_key-1)<$sort_total){
                    $sortData2 = array_slice($sortData, $min_key-1);
                    foreach ($sortData2 as $value){
                        array_push($sortData1, $value);
                    }
                }
                $sortData = $sortData1;
            }
        }
        return $sortData;
    }

    /**
     * 用户管理
     */
    public function doAward() {
        global $_GPC,$_W;


        //list 列表
        $where = ' WHERE 1=1';
        if(!empty($_GPC['keyword'])){
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $where .= " and (mobile like '{$_GPC['keyword']}%' or tradno like '{$_GPC['keyword']}%' or prizename like '{$_GPC['keyword']}%')";
        }
        if(!empty($_GPC['create_time_start'])){
            $where .= ' and ctime >='.strtotime($_GPC['create_time_start']);
        }
        if(!empty($_GPC['create_time_end'])){
            $where .= ' and ctime <='.strtotime($_GPC['create_time_end']);
        }

        if(isset($_GPC['iswin'])){
            if($_GPC['iswin'] != 99){
                $where .= " and iswin='{$_GPC['iswin']}'";
            }
        }else{
            $where .= " and iswin=1";
            $_GPC['iswin'] = 1;
        }
        if(isset($_GPC['status'])){
            if($_GPC['status'] != 99){
                $where .= " and status='{$_GPC['status']}'";
            }
        }

        if($_GPC['export']=='1'){

            $list = pdo_fetchall('SELECT * FROM '.tablename($this->tableaward).$where.' order by id desc');

            $str = "\xEF\xBB\xBF手机号\t,交易流水号\t,渠道\t,抽奖时间\t,中奖状态\t,发放状态\t,奖品名称\t,发放金额(元)\t,IP地址\t,手机型号\t\n";
            $i=2;
            foreach($list as $row){

                if($row['ch']=='app'){
                    $row['ch'] = '手机银行';
                }elseif($row['ch']=='web'){
                    $row['ch'] = '网上银行';
                }
                if($row['iswin']=='0'){
                    $row['iswin'] = '未中奖';
                }elseif($row['iswin']=='1'){
                    $row['iswin'] = '已中奖';
                }

                if($row['status']==0){
                    $row['status'] = '未领取';
                }elseif ($row['status']==1){
                    $row['status'] = '发放成功';
                }elseif ($row['status']==2){
                    $row['status'] = '发放失败';
                }
                $row['ctime'] = date('Y-m-d H:i:s',$row['ctime']);

                $str .= $row['mobile']."\t,".$row['tradno']."\t,".$row['ch']."\t,".$row['ctime']."\t,".$row['iswin']."\t,".$row['status']."\t,".$row['prizename']."\t,".$row['pay_account'].",".$row['ip']."\t,".$row['os']."\t,\n";
                $i++;
            }
            header("Content-type:text/csv");
            header("Content-Disposition:attachment; filename=账单数据".date('Y-m-d-H',time()).".csv");

            echo $str;
            exit();
        }

        $total= pdo_fetchcolumn('SELECT count(1) FROM '.tablename($this->tableaward).$where);
        $pindex = max(1, intval($_GPC['page']));
        $psize = 12;
        $pager = pagination($total, $pindex, $psize);
        $start = ($pindex - 1) * $psize;
        $limit = " LIMIT {$start},{$psize}";

        $list = pdo_fetchall('SELECT * FROM '.tablename($this->tableaward).$where.' order by id desc '.$limit);


        include $this->display('award.html');

    }

    /**
     * 问题管理
     */
    public function doQuestion() {
        global $_GPC,$_W;
        $where = ' WHERE 1=1';
        if(!empty($_GPC['keyword'])){
            $_GPC['keyword'] = trim($_GPC['keyword']);
            $where .= " and (title like '%{$_GPC['keyword']}%')";
        }

        $total= pdo_fetchcolumn('SELECT count(*) FROM '.tablename($this->tablequestion).$where);
        $pindex = max(1, intval($_GPC['page']));
        $psize = 12;
        $pager = pagination($total, $pindex, $psize);
        $start = ($pindex - 1) * $psize;
        $limit = " LIMIT {$start},{$psize}";

        $list = pdo_fetchall('SELECT * FROM '.tablename($this->tablequestion).$where.' order by id desc '.$limit);


        include $this->display('question');

    }

    /**
     * 问题导入
     */
    public function doimportQuestion(){
        global $_W,$_GPC;
        $file = $_FILES["cvsfile"];
        $info = explode('.',$file["name"]);
        $ext = end($info);
        if(strtolower($ext) != "csv" && strtolower($ext) != "xls" && strtolower($ext) != "xlsx") {
            message("文件类型不正确，请上传以xls或csv或xlsx为后缀的文件");
        }
        $filedir = '/resource/attachment/activity/' . $_GPC['name'];
        if (!file_exists(IA_ROOT . $filedir)) {
            mkdirs(IA_ROOT . $filedir);
        }
        $filename = $filedir . '/' . time() . random(4).$ext;
        $filepath = IA_ROOT . $filename;
        if(!$file["tmp_name"] || !is_uploaded_file($file["tmp_name"])){
            message("移动上传临时文件错误");
        }
        if(!move_uploaded_file($file["tmp_name"],$filepath)){
            message("上传文件失败");
        }
        $arr = formatExcelArray($filepath);
        if($arr[1]['A'] != '题目' || $arr[1]['B'] != 'A' || $arr[1]['F'] != '答案') message('文件内容格式不正确');

        foreach ($arr as $k=>$v){
            if($k==1) continue;
            pdo_insert($this->tablequestion,[
                'title'=> trim($v['A']),
                'A'=> trim($v['B']),
                'B'=> trim($v['C']),
                'C'=> trim($v['D']),
                'D'=> trim($v['E']),
                'result'=> strtoupper($v['F']),
                'ctime'=>TIMESTAMP
            ]);
        }

        message("导入数据成功",create_url('question'),"success");
    }

    /**
     * 问题删除
     */
    public function doDelQuestion() {
        global $_GPC,$_W;
        if ($_W['isajax']) {
            if($_GPC['id']){
                if(pdo_delete($this->tablequestion,['id'=>$_GPC['id']])){
                    message('success','success');
                }else{
                    message('error');
                }
            }
        }
    }

    /**
     * 问题添加/修改
     */
    public function doAddQuestion() {
        global $_GPC,$_W;

        if($_W['ispost']){
            if(empty($_GPC['title']) || empty($_GPC['A']) || empty($_GPC['B']) || empty($_GPC['C']) || empty($_GPC['D']) || empty($_GPC['result'])) message('信息不完整');

            if(!empty($_GPC['id'])){

                pdo_update($this->tablequestion,[
                    'title'=> trim($_GPC['title']),
                    'A'=> trim($_GPC['A']),
                    'B'=> trim($_GPC['B']),
                    'C'=> trim($_GPC['C']),
                    'D'=> trim($_GPC['D']),
                    'result'=> strtoupper($_GPC['result']),
                    'ctime'=>TIMESTAMP
                ],['id'=>$_GPC['id']]);
            }else{
                pdo_insert($this->tablequestion,[
                    'title'=> trim($_GPC['title']),
                    'A'=> trim($_GPC['A']),
                    'B'=> trim($_GPC['B']),
                    'C'=> trim($_GPC['C']),
                    'D'=> trim($_GPC['D']),
                    'result'=> strtoupper($_GPC['result']),
                    'ctime'=>TIMESTAMP
                ]);
            }
            message('操作成功',create_url('question'));
        }
        if(!empty($_GPC['id'])){
            $list = pdo_fetch('select * from '.tablename($this->tablequestion).' where id=:id',[':id'=>$_GPC['id']]);
        }

        include $this->display('addquestion');

    }

    /**
     * 修改密码
     */
    public function Pwd() {
        global $_GPC,$_W;
        if ($_W['isajax']) {
            if($_GPC['password']){
                pdo_update($this->tablemember,['password'=>md5($_GPC['password']),'utime'=>time()],['id'=>1]);
            }
            message('');
        }
        include $this->display('pwd.html');

    }

    /**
     * 退出
     */
    public function loginOut(){
        global $_GPC, $_W;
        $_SESSION[$_GPC['act'].'_login'] = '';
        echo '<meta http-equiv="refresh" content="0;url=' . create_url('login') . '">';
        die;
    }

    /**
     * 登陆
     */
    public function login()
    {
        global $_GPC, $_W;
        if (empty($_SESSION[$_GPC['act'].'_login'])) {

            if ($_W['isajax']) {
                $name = $_GPC['username'];
                $pwd = $_GPC['password'];

                $result = pdo_fetch('select * from ' . tablename($this->tablemember) . ' where username=:username and password=:password', [
                    ':username' => $name,
                    ':password' => md5($pwd)
                ]);


                if ($result) {
                    setcookie(session_name(), session_id(), time() + 3600 * 24, "/");
                    $_SESSION[$_GPC['act'].'_login'] = json_encode($result);
                    message(['url' => create_url('manage')],'success');
                } else {
                    message('用户名与密码不匹配！');
                }
            }
            include $this->display('login.html');
            die;
        }else{
            echo '<meta http-equiv="refresh" content="0;url=' . create_url('Prize') . '">';
            die;
        }
    }

}
