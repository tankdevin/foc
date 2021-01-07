import Vue from 'vue'
import Router from 'vue-router'

let routes = [
    //四个tab页
    {
        path: "/", //首页
        component: (resolve) => require(['../components/index/index'], resolve)
    },
    {
        path: "/transaction", //交易
        component: (resolve) => require(['../components/index/transaction'], resolve)
    },
    {
        path: "/notice", //消息
        component: (resolve) => require(['../components/index/notice'], resolve)
    },
    {
        path: "/wallet", //钱包
        component: (resolve) => require(['../components/index/wallet'], resolve)
    },
    {
        path: "/noticedetail", //新闻详情
        component: (resolve) => require(['../components/content/noticedetail'], resolve)
    },

    {
        path: "/login", //登陆
        component: (resolve) => require(['../components/user/login'], resolve)
    },
    {
        path: "/findpass", //找回密码
        component: (resolve) => require(['../components/user/findpass'], resolve)
    },

    //个人中心
    {
        path: "/shoukuan", //个人中心-收款管理
        component: (resolve) => require(['../components/user/shoukuan'], resolve)
    },
    {
        path: "/bankcard", //个人中心-收款管理-银行卡绑定
        component: (resolve) => require(['../components/user/bankcard'], resolve)
    },
    {
        path: "/alipay", //个人中心-收款管理-支付宝绑定
        component: (resolve) => require(['../components/user/alipay'], resolve)
    },
    {
        path: "/wechat", //个人中心-收款管理-微信绑定
        component: (resolve) => require(['../components/user/wechat'], resolve)
    },
    {
        path: "/loginpass", //个人中心-修改登陆密码
        component: (resolve) => require(['../components/user/loginpass'], resolve)
    },
    {
        path: "/paypass", //个人中心-修改交易密码
        component: (resolve) => require(['../components/user/paypass'], resolve)
    },
    {
        path: "/shiming", //个人中心-实名认证
        component: (resolve) => require(['../components/user/shiming'], resolve)
    },
    {
        path: "/shouyi", //个人中心-收益明细
        component: (resolve) => require(['../components/user/shouyi'], resolve)
    },
    {
        path: "/dingdan", //我的订单
        component: (resolve) => require(['../components/user/dingdan'], resolve)
    },
    {
        path: "/shangcheng", //商城
        component: (resolve) => require(['../components/user/shangcheng'], resolve)
    },
    {
        path: "/details", //订单商城
        component: (resolve) => require(['../components/user/details'], resolve)
    },
    {
        path: "/order", //订单
        component: (resolve) => require(['../components/user/order'], resolve)
    },
    {
        path: "/shipaddress", //收货地址
        component: (resolve) => require(['../components/user/shipaddress'], resolve)
    },
    {
        path: "/newaddress", //新增收货地址
        component: (resolve) => require(['../components/user/newaddress'], resolve)
    },
    {
        path: "/share", //个人中心-我的分享
        component: (resolve) => require(['../components/user/share'], resolve)
    },
    {
        path: "/chongzhi", //在线充值
        component: (resolve) => require(['../components/user/chongzhi'], resolve)
    },
    {
        path: "/record", //充值记录
        component: (resolve) => require(['../components/user/record'], resolve)
    },

    {
        path: "/slwk", //算力挖矿
        component: (resolve) => require(['../components/content/slwk'], resolve)
    },
    {
        path: "/caikuang", //采矿
        component: (resolve) => require(['../components/content/caikuang'], resolve)
    },
    {
        path: "/duihuan", //pdr兑换
        component: (resolve) => require(['../components/content/duihuan'], resolve)
    },
    

    //订单
    {
        path: "/pipei", //订单匹配中
        component: (resolve) => require(['../components/order/pipei'], resolve)
    },
    {
        path: "/jiaoyizhong", //订单进行中
        component: (resolve) => require(['../components/order/jiaoyizhong'], resolve)
    },
    {
        path: "/yiwancheng", //订单已完成
        component: (resolve) => require(['../components/order/yiwancheng'], resolve)
    },
    {
        path: "/shengji", //社区升级
        component: (resolve) => require(['../components/content/shengji'], resolve)
    },
    {
        path: "/pdrlist", //c2c记录
        component: (resolve) => require(['../components/content/pdrlist'], resolve)
    },
    {
        path: "/zhuanpan", //转盘
        component: (resolve) => require(['../components/content/zhuanpan'], resolve)
    },
    {
        path: "/wenti", //常见问题
        component: (resolve) => require(['../components/content/wenti'], resolve)
    },
    {
        path: "/zhinan", //新手指南
        component: (resolve) => require(['../components/content/zhinan'], resolve)
    },
    {
        path: "/kefu", //联系客服
        component: (resolve) => require(['../components/content/kefu'], resolve)
    },
    {
        path: "/about", //关于我们
        component: (resolve) => require(['../components/content/about'], resolve)
    },
    {
        path: "/codes", //x
        component: (resolve) => require(['../components/user/code'], resolve)
    },
    {
        path: "/jinri", //今日收益
        component: (resolve) => require(['../components/user/jinri'], resolve)
    },
    {
        path: "/shensu", //申诉
        component: (resolve) => require(['../components/user/shensu'], resolve)
    },
    {
        path: "/yidakuan", //已打款
        component: (resolve) => require(['../components/order/yidakuan'], resolve)
    },
    {
        path: "/zhuanpanlist", //抽奖记录
        component: (resolve) => require(['../components/content/zhuanpanlist'], resolve)
    },
    {
        path: "/sllist", //算力挖矿记录
        component: (resolve) => require(['../components/content/sllist'], resolve)
    },
    {
        path: "/shensuzhong", //申诉详情
        component: (resolve) => require(['../components/order/shensuzhong'], resolve)
    },
    {
        path: "/sharelist", //分享记录
        component: (resolve) => require(['../components/user/sharelist'], resolve)
    },
    {
        path: "/newlist", //pdr记录
        component: (resolve) => require(['../components/content/newlist'], resolve)
    },
    {
        path: "/payment", //收款码
        component: (resolve) => require(['../components/content/payment'], resolve)
    },
    {
        path: "/pRecord", //收款记录
        component: (resolve) => require(['../components/content/pRecord'], resolve)
    },
    {
        path: "/transfer", //zhuanzhang
        component: (resolve) => require(['../components/content/transfer'], resolve)
    },
    {
        path: "/zhuanzhangjilu", //转账记录
        component: (resolve) => require(['../components/content/zhuanzhangjilu'], resolve)
    },
    {
        path: "/integral", //消费积分
        component: (resolve) => require(['../components/user/integral'], resolve)
    },
  {
    path: "/sb_list", //新增列表
    component: (resolve) => require(['../components/content/sb_list'], resolve)
  },
];


Vue.use(Router);


export default new Router({
    mode: 'hash',
    routes
})
