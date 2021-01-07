<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\App;
use think\Db;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 10:41
 */
class Index extends BasicAdmin
{

    public function __construct( App $app = null )
    {
        parent::__construct($app);
        if(!session('user')){
            $this->redirect('@admin/login');
        }
    }

    /**
     * 后台框架布局
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        NodeService::applyAuthNode();
        $list = (array)Db::name('SystemMenu')->where(['status' => '1'])->order('sort asc,id asc')->select();
        $menus = $this->buildMenuData(ToolsService::arr2tree($list), NodeService::get(), !!session('user'));
        if (empty($menus) && !session('user.id')) {
            $this->redirect('@admin/login');
        }
        return $this->fetch('', ['title' => '系统管理', 'menus' => $menus]);
    }

    /**
     * 后台主菜单权限过滤
     * @param array $menus 当前菜单列表
     * @param array $nodes 系统权限节点数据
     * @param bool $isLogin 是否已经登录
     * @return array
     */
    private function buildMenuData($menus, $nodes, $isLogin)
    {
        foreach ($menus as $key => &$menu) {
            !empty($menu['sub']) && $menu['sub'] = $this->buildMenuData($menu['sub'], $nodes, $isLogin);
            if (!empty($menu['sub'])) {
                $menu['url'] = '#';
            } elseif (preg_match('/^https?\:/i', $menu['url'])) {
                continue;
            } elseif ($menu['url'] !== '#') {
                $node = join('/', array_slice(explode('/', preg_replace('/[\W]/', '/', $menu['url'])), 0, 3));
                $menu['url'] = url($menu['url']) . (empty($menu['params']) ? '' : "?{$menu['params']}");
                if (isset($nodes[$node]) && $nodes[$node]['is_login'] && empty($isLogin)) {
                    unset($menus[$key]);
                } elseif (isset($nodes[$node]) && $nodes[$node]['is_auth'] && $isLogin && !auth($node)) {
                    unset($menus[$key]);
                }
            } else {
                unset($menus[$key]);
            }
        }
        return $menus;
    }

    /**
     * 主机信息显示
     * @return string
     */
    public function main()
    {
        $_version = Db::query('select version() as ver');
        $now = strtotime(date('Y-m-d'));
        $total_money = Db::name('store_member')->sum('account_money');
        $total_num = Db::name('store_member')->count();//总的人数
        $now_date = date('Y-m-d');
        $now_time = strtotime(date('Y-m-d'));
        $new_add_num = Db::name('store_member')->where('create_at','gt',"{$now_date} 00:00:00")->count();//今日新增人数
        //今日投资总额
        $day_tz_num = db::name('financial_order')->where('create_at','gt',"$now_time")->sum('market_price');
        //投资总额
        $tz_num = db::name('financial_order')->sum('market_price');

        //每天转账总金额
        $day_zz_account = Db::name('bags_log')->where(['type'=>'account_money','status'=>'6'])->where('money','lt','0')->where('create_time','gt',"$now_time")->sum('money');
        //每天拆红包总金额
        
        //acc总数
        $acc_num ='';
        //acc提现总额
        $acc_tx_num = Db::name('store_withdraw_money')->where(['type'=>2,'state'=>1])->sum('num');
        //总USDT充值
        $total_usdt = Db::name('bags_log')->where(['type'=>'account_money','extends'=>'rechangUsdt'])->sum('money');
        //今日USDT充值	
        $day_usdt = Db::name('bags_log')->where(['type'=>'account_money','extends'=>'rechangUsdt'])->where('create_time','gt',"$now_time")->sum('money');
        //总LXC充值
        $total_lxc = Db::name('bags_log')->where(['type'=>'account_score','extends'=>'rechanglxc'])->sum('money');
        //今日lxc充值
        $day_lxc = Db::name('bags_log')->where(['type'=>'account_score','extends'=>'rechanglxc'])->where('create_time','gt',"$now_time")->sum('money');
        //今日已通过提币笔数	
        $day_withdraw = Db::name('store_withdraw_money')->where(['state'=>'1'])->where('reply_time','gt',"$now_time")->count();
        //今日待审核提币笔数
        $day_withdraw_dsh = Db::name('store_withdraw_money')->where(['state'=>'0'])->where('addtime','gt',"$now_time")->count();
        //今日提币USDT数量	
        $day_withdraw_usdt = Db::name('store_withdraw_money')->where(['state'=>'1','type'=>1])->where('reply_time','gt',"$now_time")->sum('real_num');
        //今日提币LXC数量	
        $day_withdraw_lxc = Db::name('store_withdraw_money')->where(['state'=>'1','type'=>2])->where('reply_time','gt',"$now_time")->sum('real_num');
        //pos有效算力  poe有效算力
//         $wallet_one = Db::name('store_member')->where(['is_disable'=>'1'])->sum('wallet_one');//算力(购买pos的算力)
//         $wallet_two = Db::name('store_member')->where(['is_disable'=>'1'])->sum('wallet_two');//直推投入算力（pos有效）
//         $wallet_four = Db::name('store_member')->where(['is_disable'=>'1'])->sum('wallet_four');//算力（购买pot的算力）
        //平台总的usdt和nac
        $total_money = Db::name('store_member')->where(['is_disable'=>'1'])->sum('account_money');
        $total_score = Db::name('store_member')->where(['is_disable'=>'1'])->sum('account_score');
        $total_nfc = Db::name('store_member')->where(['is_disable'=>'1'])->sum('account_foc');
        $total_mach = Db::name('store_member')->sum('wallet_six');
        $total_usdt_s = Db::name('store_member')->where(['is_disable'=>'1'])->sum('usdt_suo');
        $sell_usdt = Db::name('jys_selllist')->field('sum((totalnum-leavenum)*price) as money')->where('addtime','>',strtotime(date('Y-m-d 00:00:00')))->select();
        $sell_usdt = empty($sell_usdt[0]['money'])?0.00:$sell_usdt[0]['money'];
        $this->assign('sell_usdt',$sell_usdt);
        $this->assign('total_money',$total_money);
        $this->assign('total_score',$total_score);
        $this->assign('total_nfc',$total_nfc);
//         $this->assign('pos_sl',$wallet_one+$wallet_two);
        $this->assign('total_mach',$total_mach);
        $this->assign('total_usdt_s',$total_usdt_s);
        //申购
//         $shengou_nf = Db::name('wang_shengou')->where(['status'=>'1'])->where('create_at','>=',strtotime(date('Y-m-d 0:0:1')))->sum('num');
//         $shengou_usdt = Db::name('wang_shengou')->where(['status'=>'1'])->where('create_at','>=',strtotime(date('Y-m-d 0:0:1')))->sum('usdt');
//         $shengou_nfgood = Db::name('wang_shengou')->where(['status'=>'2'])->sum('good_num');
//         $shengou_usdtgood = Db::name('wang_shengou')->where(['status'=>'2'])->sum('good_usdt');
//         $this->assign('shengou_nfgood',$shengou_nfgood);
//         $this->assign('shengou_usdtgood',$shengou_usdtgood);
//         $this->assign('shengou_nf',$shengou_nf);
//         $this->assign('shengou_usdt',$shengou_usdt);
        //矿池
//         $kuangchi_nfgood = Db::name('wang_kuangchi')->where(['status'=>'2'])->sum('num');
//         $kuangchi_nfcgood = Db::name('wang_kuangchi')->where(['status'=>'2'])->sum('good_num');
//         $this->assign('kuangchi_nfgood',$kuangchi_nfgood);
//         $this->assign('kuangchi_nfcgood',$kuangchi_nfcgood);
//         $kuangchi_nf = Db::name('wang_kuangchi')->where(['status'=>'1'])->where('create_at','>=',strtotime(date('Y-m-d 0:0:1')))->sum('num');
//         $kuangchi_nfc = Db::name('wang_kuangchi')->where(['status'=>'1'])->where('create_at','>=',strtotime(date('Y-m-d 0:0:1')))->sum('good_num');
//         $this->assign('kuangchi_nf',$kuangchi_nf);
//         $this->assign('kuangchi_nfc',$kuangchi_nfc);
//         //直推算力显示
//         $wjs_tj = db::name('store_award')->where(['state'=>0,'type'=>0])->sum('money');
//         $yjs_tj = db::name('store_award')->where(['state'=>1,'type'=>0])->sum('money');
//         $ysx_tj = db::name('store_award')->where(['state'=>2,'type'=>0])->sum('money');
//         //见点算力显示
//         $wjs_jd = db::name('store_award')->where(['state'=>0,'type'=>1])->sum('money');
//         $yjs_jd = db::name('store_award')->where(['state'=>1,'type'=>1])->sum('money');
//         $ysx_jd = db::name('store_award')->where(['state'=>2,'type'=>1])->sum('money');
//         $this->assign('wjs_tj',$wjs_tj);
//         $this->assign('yjs_tj',$yjs_tj);
//         $this->assign('ysx_tj',$ysx_tj);
//         $this->assign('wjs_jd',$wjs_jd);
//         $this->assign('yjs_jd',$yjs_jd);
//         $this->assign('ysx_jd',$ysx_jd);
        $this->assign('new_add_num',$new_add_num);
//         $this->assign('new_jl_add_num',$new_jl_add_num);
//         $this->assign('day_zz_account',-$day_zz_account);
//         //var_dump($hongbao);
        $this->assign('total_num',$total_num);
//         $this->assign('acc_num',$acc_num);
//         $this->assign('acc_tx_num',$acc_tx_num);
//         $this->assign('jh_num',$jh_num);

//         $this->assign('day_tz_num',$day_tz_num);
//         $this->assign('tz_num',$tz_num);
//         $this->assign('total_usdt',$total_usdt);
//         $this->assign('day_usdt',$day_usdt);
//         $this->assign('total_lxc',$total_lxc);
//         $this->assign('day_lxc',$day_lxc);
//         $this->assign('day_withdraw',$day_withdraw);
//         $this->assign('day_withdraw_dsh',$day_withdraw_dsh);
//         $this->assign('day_withdraw_usdt',$day_withdraw_usdt);
//         $this->assign('day_withdraw_lxc',$day_withdraw_lxc);
        return $this->fetch('', [
            'title'     => '后台首页',
            'think_ver' => App::VERSION,
            'mysql_ver' => array_pop($_version)['ver'],
        ]);
    }

    /**
     * 修改密码
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function pass()
    {
        if (intval($this->request->request('id')) !== intval(session('user.id'))) {
            $this->error('只能修改当前用户的密码！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', true);
            return $this->_form('SystemUser', 'user/pass');
        }
        $data = $this->request->post();
        if ($data['password'] !== $data['repassword']) {
            $this->error('两次输入的密码不一致，请重新输入！');
        }
        $user = Db::name('SystemUser')->where('id', session('user.id'))->find();
        if (md5($data['oldpassword']) !== $user['password']) {
            $this->error('旧密码验证失败，请重新输入！');
        }
        if (DataService::save('SystemUser', ['id' => session('user.id'), 'password' => md5($data['password'])])) {
            $this->success('密码修改成功，下次请使用新密码登录！', '');
        }
        $this->error('密码修改失败，请稍候再试！');
    }

    /**
     * 修改资料
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info()
    {
        if (intval($this->request->request('id')) === intval(session('user.id'))) {
            return $this->_form('SystemUser', 'user/form');
        }
        $this->error('只能修改当前用户的资料！');
    }

}
