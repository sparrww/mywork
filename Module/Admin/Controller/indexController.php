<?php
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
            echo '<meta http-equiv="refresh" content="0;url=' . create_url('Yjj') . '">';
            die;
        }
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
     * 用户管理
     */
    public function Award() {
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
     * 药监局
     */
    public function Yjj() {
        global $_GPC,$_W;
        if ($_W['isajax']) {
            if($_GPC['password']){
                pdo_update($this->tablemember,['password'=>md5($_GPC['password']),'utime'=>time()],['id'=>1]);
            }
            message('');
        }

        $data = '[
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字150516205",
            "spm": "无",
            "itemid": "1FB17A34AB94473CBFE69CBF3BF522F6",
            "pzrq": "2016/11/16",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g︰阿苯达唑6g+伊维菌素0.25g",
            "tym": "阿苯达唑伊维菌素预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160623-277"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字150516242",
            "spm": "无",
            "itemid": "5590B08872F54F9598E0B04E3A823160",
            "pzrq": "2016/11/16",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:磺胺间甲氧嘧啶10g+甲氧苄啶2g",
            "tym": "复方磺胺间甲氧嘧啶预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160721-252"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字150512539",
            "spm": "无",
            "itemid": "91E9BE6B8F754756BA7F804B59A4787A",
            "pzrq": "2016/11/16",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "20%",
            "tym": "氟苯尼考粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160721-256"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字150516011",
            "spm": "无",
            "itemid": "D8F3D059453A408490C1F8F92C64227D",
            "pzrq": "2016/11/16",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "盐酸多西环素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160719-099"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字150512026",
            "spm": "鲁西诺蝇清",
            "itemid": "EDD976D6B7754BDD8F3D1227F16077F9",
            "pzrq": "2016/10/17",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:甲基吡啶磷可湿性粉-50；20g与9-二十三碳烯0.05g",
            "tym": "甲基吡啶磷可湿性粉-10",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160718-485"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药原字150512079",
            "spm": "诺蝇净",
            "itemid": "24439AD2ADA5410CA86EA18353EA53AC",
            "pzrq": "2016/08/25",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "环丙氨嗪预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160629-022"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药原字150511139",
            "spm": "诺球",
            "itemid": "40E0C5CAA4784A588D9FE1C956BDD5E5",
            "pzrq": "2016/08/25",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "",
            "tym": "地克珠利",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160629-124"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药原字150512077",
            "spm": "诺蝇净",
            "itemid": "884EF1FC002042B6A8B900D79D43CB10",
            "pzrq": "2016/08/25",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "按无水物计算，含C6H10N6不得少于98.0%",
            "tym": "环丙氨嗪",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160629-131"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字150516002",
            "spm": "无",
            "itemid": "0620DFC168A64E89863AD56D93B03083",
            "pzrq": "2016/08/16",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g﹕土霉素50g（5000万单位）",
            "tym": "土霉素预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160623-224"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字150512757",
            "spm": "无",
            "itemid": "1C2181DAC60347E4AB51BF2D465057E5",
            "pzrq": "2016/08/16",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:5g（1.5亿单位）",
            "tym": "硫酸黏菌素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160623-261"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字150511189",
            "spm": "无",
            "itemid": "70982101D74E433786B258B8D00380EC",
            "pzrq": "2016/08/16",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "5%",
            "tym": "芬苯达唑粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160623-241"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字150511046",
            "spm": "鲁西螨蝇清",
            "itemid": "FC8D1F02E54F4C6AA14BFE055D2E1CC3",
            "pzrq": "2016/08/16",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "12.5%",
            "tym": "双甲脒溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160623-300"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150512003",
            "spm": "鲁西螨蝇净",
            "itemid": "127F7A4844F949D78E45237B940317ED",
            "pzrq": "2016/06/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "60%",
            "tym": "二嗪农溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160419-47"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药添字（2016）150511141",
            "spm": "诺球",
            "itemid": "61659A8DDDF746B6BFEC95DA14A1EF51",
            "pzrq": "2016/04/18",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "0.5%",
            "tym": "地克珠利预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160304-71"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150512731",
            "spm": "无",
            "itemid": "1112A53A52854BE19753A8D6D94D7464",
            "pzrq": "2016/04/07",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:10g（1000万单位）",
            "tym": "酒石酸泰乐菌素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160219-17"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150516018",
            "spm": "珍君宝",
            "itemid": "BD5AE8C40C6548F69E830041D88016E9",
            "pzrq": "2016/04/07",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "15g﹕酮康唑0.15g+甲硝唑0.3g+薄荷脑0.15g",
            "tym": "复方酮康唑软膏",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160215-57"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150512745",
            "spm": "无",
            "itemid": "388BB49CB0794BF2AC545EE3F833C524",
            "pzrq": "2016/04/01",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:10g（1000万单位）",
            "tym": "硫酸安普霉素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160105-29"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150512045",
            "spm": "诺球",
            "itemid": "A1A440BA23B543329E1011247593CBF1",
            "pzrq": "2016/04/01",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "0.5%",
            "tym": "地克珠利溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020160106-103"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150511326",
            "spm": "无",
            "itemid": "6DF6FF4305A6475CB4A7A207896E78B1",
            "pzrq": "2016/03/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:8.8g（880万单位）",
            "tym": "磷酸泰乐菌素预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020151210-84"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150512193",
            "spm": "无",
            "itemid": "E8313076087640AC80C7AF118C2504D5",
            "pzrq": "2016/03/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "20%",
            "tym": "替米考星预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020151210-17"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150516080",
            "spm": "无",
            "itemid": "F82D61AEE0324B58B154D97284D72006",
            "pzrq": "2016/03/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "5%",
            "tym": "盐酸林可霉素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020151210-178"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150516245",
            "spm": "无",
            "itemid": "1E258564CB1B40C5B8D1C80C0F8BEC3D",
            "pzrq": "2016/03/04",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100ml:戊二醛5g+癸甲溴铵5g",
            "tym": "戊二醛癸甲溴铵溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020151229-27"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150512700",
            "spm": "乐肤",
            "itemid": "5AC9393CDF5D418CA0E16CE36815C8EF",
            "pzrq": "2016/03/04",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "0.5%",
            "tym": "硫酸新霉素软膏",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020151229-135"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150516015",
            "spm": "尔肤康",
            "itemid": "67E9491F269D48F5A0C53858D03F1357",
            "pzrq": "2016/03/04",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "20ml：氟苯尼考500mg＋甲硝唑60mg",
            "tym": "氟苯尼考甲硝唑滴耳液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020151229-136"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2016）150511575",
            "spm": "无",
            "itemid": "82C319CAB84B4ADB8E237926ADAEE33A",
            "pzrq": "2016/03/04",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "5%",
            "tym": "聚维酮碘溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020151229-57"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药添字（2015）150511377",
            "spm": "/",
            "itemid": "84BB0396E84C4B32BF903C5665D81E37",
            "pzrq": "2015/03/30",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "盐酸氯苯胍预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020150128-2"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药添字（2015）150511478",
            "spm": "/",
            "itemid": "D66A7A0A9FFF4C019BA4DA4D71074F5D",
            "pzrq": "2015/03/30",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "25%",
            "tym": "氯羟吡啶预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020150128-3"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2015）150512252",
            "spm": "/",
            "itemid": "E5EB71365B2D40B4AD5606E43277BAA3",
            "pzrq": "2015/03/30",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "1000g:磺胺氯达嗪钠625g与甲氧苄啶125g",
            "tym": "复方磺胺氯达嗪钠粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "07040020150128-4"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2014）150512306",
            "spm": "雅球",
            "itemid": "1B7BFA73496C45FAADBF596AF770AED8",
            "pzrq": "2014/11/02",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "3%",
            "tym": "癸氧喹酯溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704243384"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2014）150513008",
            "spm": "/",
            "itemid": "23E23ACBF48442B3A7DEB3273B13F686",
            "pzrq": "2014/08/12",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "45%",
            "tym": "延胡索酸泰妙菌素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704239021"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2014）150512111",
            "spm": "/",
            "itemid": "D7D7585DD9E042E1B5445ED71D3D7622",
            "pzrq": "2014/06/10",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "氟苯尼考溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704235410"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2014）150512264",
            "spm": "/",
            "itemid": "EBEB1EBAC6334CFEAA3E898D3E3CBA14",
            "pzrq": "2014/06/10",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "替米考星溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704235411"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2014）150512606",
            "spm": "/",
            "itemid": "5F543627023B427FAC052247B67CE47D",
            "pzrq": "2014/04/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "盐酸环丙沙星可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704232089"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2014）150512313",
            "spm": "/",
            "itemid": "AE876C5B12674B568B5E1073A43A639B",
            "pzrq": "2014/04/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "50%",
            "tym": "卡巴匹林钙可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704231758"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2014）150512526",
            "spm": "/",
            "itemid": "EE15DA0A5E5E4D20ABDB1F9A4BFA772D",
            "pzrq": "2014/04/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "恩诺沙星可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704231757"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2014）150511143",
            "spm": "/",
            "itemid": "F81E7D17F9464BCA907004B3BCE7F4FB",
            "pzrq": "2014/04/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "20%",
            "tym": "地美硝唑预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704232088"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2014）150512078",
            "spm": "诺蝇净",
            "itemid": "2E202BEBBEE2410B8408AC69FB5F3B87",
            "pzrq": "2014/01/07",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "1%",
            "tym": "环丙氨嗪预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704226420"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2013）150512583",
            "spm": "/",
            "itemid": "B121DD3529934A6197F3D2F19D7ED5B8",
            "pzrq": "2013/09/30",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "40%",
            "tym": "辛硫磷浇泼溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704219198"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2013）150512110",
            "spm": "/",
            "itemid": "BA66659A745645F2A09BD32B069CDC98",
            "pzrq": "2013/09/30",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "氟苯尼考粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704219199"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2013）150512118",
            "spm": "/",
            "itemid": "CF05AFE7F4024BCB8DA7EFF34FD60810",
            "pzrq": "2013/09/30",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "2.50%",
            "tym": "恩诺沙星可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704219200"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2013）150516041",
            "spm": "/",
            "itemid": "F15CE981CFBF4AF0BFCBC739BA6CE1F0",
            "pzrq": "2013/09/22",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "500g﹕马度米星2.5g+尼卡巴嗪62.5g",
            "tym": "马度米星铵尼卡巴嗪预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704218637"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2013）150512510",
            "spm": "/",
            "itemid": "188D1B54BDD64978924390D114C8D3A5",
            "pzrq": "2013/08/22",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "氨苄西林钠可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704215722"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2013）150513120",
            "spm": "雅球",
            "itemid": "19C466FBBC0B4116A446E43867A25A75",
            "pzrq": "2013/05/23",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:6g",
            "tym": "癸氧喹酯预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704209392"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药添字（2013）150511015",
            "spm": "/",
            "itemid": "ADC3EB38CC5D4DDAAD910EA240F5A655",
            "pzrq": "2013/05/23",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "25%",
            "tym": "二硝托胺预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704209391"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150516291",
            "spm": "/",
            "itemid": "2186C51EFD7647E082E5852F1AEDF1B5",
            "pzrq": "2012/11/28",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "0.1%",
            "tym": "伊维菌素溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704195021"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150516207",
            "spm": "/",
            "itemid": "CA8BDB71C4174050A4B6BB49E0C15229",
            "pzrq": "2012/08/31",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "50%",
            "tym": "吡喹酮粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704186977"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150513119",
            "spm": "/",
            "itemid": "815F6777CA064EC3B05542CCF9EBB051",
            "pzrq": "2012/08/21",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "",
            "tym": "癸氧喹酯",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704185227"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150512069",
            "spm": "//",
            "itemid": "B9E08D72A5D84A78B62718AFE376C7C2",
            "pzrq": "2012/08/09",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100ml:500mg",
            "tym": "阿维菌素透皮溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704183954"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药添字（2012）150512217",
            "spm": "/",
            "itemid": "E7E4CD1D16AC488CB350A5A40CDC5055",
            "pzrq": "2012/08/09",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "硫酸黏菌素预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704183953"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150511523",
            "spm": "/",
            "itemid": "F0304209723A4296B78C7E23303031E7",
            "pzrq": "2012/08/09",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:6.5g（650万单位）",
            "tym": "硫酸新霉素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704183952"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150511199",
            "spm": "/",
            "itemid": "46492C4D107F40C3923C1C937214D8CE",
            "pzrq": "2012/06/19",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "阿莫西林可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704180126"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150511492",
            "spm": "/",
            "itemid": "D4496398530E4202B902D1D7B38FE225",
            "pzrq": "2012/06/19",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "5%",
            "tym": "硫氰酸红霉素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704180125"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药添字（2012）150512528",
            "spm": "诺球",
            "itemid": "EF30BDDE8EF944E6A04BCAD88704A940",
            "pzrq": "2012/06/19",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "5.00%",
            "tym": "地克珠利预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704180124"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150511628",
            "spm": "/",
            "itemid": "AF79CBAF89EE4A3782679203C54576D3",
            "pzrq": "2012/06/01",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "30%",
            "tym": "磺胺氯吡嗪钠可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704177009"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150516087",
            "spm": "/",
            "itemid": "82AC0A3C0C234693B4E44272922A2785",
            "pzrq": "2012/03/12",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "盐酸左旋咪唑粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "",
            "slh": "0704166666"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150516205",
            "spm": "/",
            "itemid": "4754F6EAD12A4F7394B6EC331E39869A",
            "pzrq": "2012/01/14",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g︰阿苯达唑6g+伊维菌素0.25g",
            "tym": "阿苯达唑伊维菌素预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704161746"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2012）150516011",
            "spm": "/",
            "itemid": "E3BE37FFA3DF4DA79AF8BDCDAA9A2558",
            "pzrq": "2012/01/14",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "盐酸多西环素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704161747"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150516002",
            "spm": "/",
            "itemid": "563A391088E144F2B10A6270CE413913",
            "pzrq": "2011/12/26",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g﹕土霉素50g（5000万单位）",
            "tym": "土霉素预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704156122"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150512757",
            "spm": "/",
            "itemid": "1A2DA715777347F8BC417534E5B95173",
            "pzrq": "2011/11/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:5g（1.5亿粘菌素单位）",
            "tym": "硫酸黏菌素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704151721"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150511046",
            "spm": "/",
            "itemid": "AED576287A17474296CEC38BCA74F477",
            "pzrq": "2011/11/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "12.50%",
            "tym": "双甲脒溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704151722"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150511189",
            "spm": "/",
            "itemid": "C0F0BAA4BBF24788894E40010FA3E2F4",
            "pzrq": "2011/11/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "5%",
            "tym": "芬苯达唑粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704151720"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药添字（2011）150511141",
            "spm": "诺球",
            "itemid": "040DAF7ED27742CEA8E40588A12442AB",
            "pzrq": "2011/09/13",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:0.5g",
            "tym": "地克珠利预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "该文号已换发",
            "slh": "0704145120"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150512079",
            "spm": "//",
            "itemid": "6214760F0510405E8DADF3E835755CDF",
            "pzrq": "2011/08/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "环丙氨嗪预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过有效期",
            "slh": "0704137926"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150511139",
            "spm": "诺球",
            "itemid": "A18D5158DD3B40D8BAD0E9F60BD25823",
            "pzrq": "2011/08/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "",
            "tym": "地克珠利",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过有效期",
            "slh": "0704139481"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150512731",
            "spm": "/",
            "itemid": "09985981F3124505AC832305A7A8BC35",
            "pzrq": "2011/07/27",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:10g（1000万单位）",
            "tym": "酒石酸泰乐菌素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "该文号已换发",
            "slh": "0704137924"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150512077",
            "spm": "//",
            "itemid": "7C8CD035F87742A19F7F92F92F0120AF",
            "pzrq": "2011/07/27",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "",
            "tym": "环丙氨嗪",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过有效期",
            "slh": "0704137923"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150512745",
            "spm": "/",
            "itemid": "E2AEBCC3A66642AE958718DA0707DE9C",
            "pzrq": "2011/07/27",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "硫酸安普霉素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "该文号已换发",
            "slh": "0704137925"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150512159",
            "spm": "/",
            "itemid": "642836E8FDB34C2EB82F4774F2AB1D27",
            "pzrq": "2011/07/04",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "2%",
            "tym": "盐酸环丙沙星可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704135809"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2011）150512045",
            "spm": "诺球",
            "itemid": "662A3FE7442B4D9D87D35B84FB8CBC5A",
            "pzrq": "2011/07/04",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "0.50%",
            "tym": "地克珠利溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "该文号已换发",
            "slh": "0704135807"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2010）150512501",
            "spm": "/",
            "itemid": "1C0C5B25222648E491D38259EA2E6FCF",
            "pzrq": "2011/01/17",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "5%",
            "tym": "氰戊菊酯溶液",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704127355"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2010）150512003",
            "spm": "//",
            "itemid": "EA1A2F5A047247E3ADCB20282C670514",
            "pzrq": "2011/01/17",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "60%",
            "tym": "二嗪农溶液（600EC）",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704127356"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2010）150512067",
            "spm": "/",
            "itemid": "977866CC0C3A40FAB28392CC31417905",
            "pzrq": "2010/10/21",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "2%",
            "tym": "阿维菌素粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704123268"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2010）150511326",
            "spm": "/",
            "itemid": "8358F822B29C429780D2493E0A9046DF",
            "pzrq": "2010/08/23",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:8.8g（880万单位）",
            "tym": "磷酸泰乐菌素预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704122104"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2010）150512193",
            "spm": "/",
            "itemid": "29103F5B65B74E05B46B88B7621EFEEB",
            "pzrq": "2010/06/18",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:20g",
            "tym": "替米考星预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704119820"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2010）150512252",
            "spm": "/",
            "itemid": "5F0DC99578AF4709ABEB099640BA90C7",
            "pzrq": "2010/04/30",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:磺胺氯达嗪钠62.5g+甲氧苄啶12.5g",
            "tym": "复方磺胺氯达嗪钠粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704118253"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2010）150511339",
            "spm": "/",
            "itemid": "C6AD6EC45D7345E3B9EE97B9C48011DF",
            "pzrq": "2010/04/09",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:大观霉素40g（4000万单位）+林可霉素20g（2000万单位）",
            "tym": "盐酸大观霉素盐酸林可霉素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704118002"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2009）150512078",
            "spm": "诺蝇净",
            "itemid": "0A8907A287CF421E98C94A1ADBEAA111",
            "pzrq": "2009/02/25",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "1%",
            "tym": "环丙氨嗪预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704101490"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2009）150511624",
            "spm": "///",
            "itemid": "983F429AE7D347C2A1BB293ECE585FE4",
            "pzrq": "2009/02/25",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "磺胺喹噁啉钠可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704101602"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2009）150512149",
            "spm": "///",
            "itemid": "16F203F32859451DA72694F3B77EEF27",
            "pzrq": "2009/02/11",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "2.50%",
            "tym": "盐酸沙拉沙星可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704100955"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2009）150512118",
            "spm": "///",
            "itemid": "E5B8FEE9687249A6A10A5D1DC11A762E",
            "pzrq": "2009/02/11",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "2.50%",
            "tym": "恩诺沙星可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704101046"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2008）150512620",
            "spm": "畜禽康泰",
            "itemid": "B290B292911C407BB54A3D6716A9EB60",
            "pzrq": "2009/01/15",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "5%",
            "tym": "盐酸林可霉素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704100293"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2008）150512110",
            "spm": "氟尔康泰",
            "itemid": "6D623319CEE144D3BAA2E2C50C277584",
            "pzrq": "2009/01/09",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "氟苯尼考粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已重新取得文号",
            "slh": "0704100124"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2008）150512730",
            "spm": "//",
            "itemid": "B51970C6DDA24BD2913266F5C6ACB7B2",
            "pzrq": "2009/01/09",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "10%",
            "tym": "酒石酸吉他霉素可溶性粉",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704100125"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2008）150515165",
            "spm": "瘟之康",
            "itemid": "5E0DE146125C48DEB9DA0B6DB9926666",
            "pzrq": "2008/03/25",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "",
            "tym": "清瘟败毒散",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704085201"
        },
        {
            "sxyy": "",
            "qymc": "山东鲁西兽药股份有限公司",
            "pzwh": "兽药字（2008）150513120",
            "spm": "雅球",
            "itemid": "0B1B415828094F9D9C4A5CB7422593C4",
            "pzrq": "2008/03/07",
            "shr": "",
            "zxbz": "",
            "zt": "",
            "yxq": "",
            "gg": "100g:6g",
            "tym": "癸氧喹酯预混剂",
            "byx": "0",
            "shrq": "2017/02/22",
            "bgqk": "已过5年有效期",
            "slh": "0704083589"
        }
    ]';
        var_dump($data);
        include $this->display('yjj.html');

    }

}
