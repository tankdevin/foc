<?php /*a:2:{s:66:"D:\workspaces\www.ntf.com\application\admin\view\config\index.html";i:1605256763;s:68:"D:\workspaces\www.ntf.com\application\admin\view\public\content.html";i:1595314206;}*/ ?>
<!-- 右则内容区域 开始 -->

<style>
    .left_setting h3 {
        position: absolute;
        font-size: 16px;
        left: 30px;
    }
    .layui-form input[type=checkbox], .layui-form input[type=radio], .layui-form select {
        display: inline-block;
    }
    .showsell{
        margin-top: 9px;
    }
    .showsell input{
        margin-left:3px;
    }
</style>


<div class="layui-card">
    <!--<?php if(!(empty($title) || (($title instanceof \think\Collection || $title instanceof \think\Paginator ) && $title->isEmpty()))): ?>-->
    <div class="layui-header notselect">
        <div class="pull-left"><span><?php echo htmlentities($title); ?></span></div>
        <div class="pull-right margin-right-15 nowrap"></div>
    </div>
    <!--<?php endif; ?>-->
    <div class="layui-card-body">
<form autocomplete="off" onsubmit="return false;" action="<?php echo request()->url(); ?>" data-auto="true" method="post"
      class='form-horizontal layui-form padding-top-20'>

    <div class="form-group">
        <label class="col-sm-2 control-label">
            AppName<br><span class="nowrap color-desc">程序名称</span>
        </label>
        <div class='col-sm-8'>
            <input name="app_name" required="required" title="请输入程序名称" placeholder="请输入程序名称"
                   value="<?php echo sysconf('app_name'); ?>" class="layui-input">
            <p class="help-block">当前程序名称，在后台主标题上显示</p>
        </div>
    </div>

    <!-- <div class="form-group">
        <label class="col-sm-2 control-label">
            AppVersion<br><span class="nowrap color-desc">程序版本</span>
        </label>
        <div class='col-sm-8'>
            <input name="app_version" required="required" title="请输入程序版本" placeholder="请输入程序版本"
                   value="<?php echo sysconf('app_version'); ?>" class="layui-input">
            <p class="help-block">当前程序版本号，在后台主标题上标显示</p>
        </div>
    </div> -->

    <div class="hr-line-dashed"></div>

    <div class="form-group">
        <label class="col-sm-2 control-label">
            SiteName<br><span class="nowrap color-desc">网站名称</span>
        </label>
        <div class='col-sm-8'>
            <input name="site_name" required="required" title="请输入网站名称" placeholder="请输入网站名称"
                   value="<?php echo sysconf('site_name'); ?>" class="layui-input">
            <p class="help-block">网站名称，显示在浏览器标签上</p>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">
            Copyright<br><span class="nowrap color-desc">版权信息</span>
        </label>
        <div class='col-sm-8'>
            <input name="site_copy" required="required" title="请输入版权信息" placeholder="请输入版权信息"
                   value="<?php echo sysconf('site_copy'); ?>" class="layui-input">
            <p class="help-block">程序的版权信息设置，在后台登录页面显示</p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
           <span class="nowrap color-desc">USDT汇率</span>
        </label>
        <div class='col-sm-3'>
            <input name="usdt_rmb" required="required" title="USDT汇率" placeholder="USDT汇率"
                   value="<?php echo sysconf('usdt_rmb'); ?>" class="layui-input">
            <p class="help-block">1USDT兑换RMB比例</p>
        </div>
    </div>
    
    <div class="hr-line-dashed"></div>
    <div class="form-group">
            <label class="col-sm-2 control-label">
                客服中心二维码:
            </label>
            <div class='col-sm-8'>
                <img data-tips-image style="height:auto;max-height:32px;min-width:32px" src="<?php echo sysconf('kefu_img'); ?>"/>
                <input type="hidden" name="kefu_img" onchange="$(this).prev('img').attr('src', this.value)"
                       value="<?php echo sysconf('kefu_img'); ?>" class="layui-input">
                <a class="btn btn-link" data-file="one" data-uptype="local" data-type="jpg,png" data-field="kefu_img">上传图片</a>

            </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            是否启用交易所
        </label>
        <div class="layui-input-block showsell">

            是<input type="radio" name="openC2c" value="1" title="是"  <?php
             if(sysconf('openC2c') == 1){
              echo 'checked';
             }
            ?>>
            否<input type="radio" name="openC2c" value="0" title="否"   <?php
             if(sysconf('openC2c') == 0){
              echo 'checked';
             }
            ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            矿机是否启用金本位（是金本位，否配置固定数量）
        </label>
        <div class="layui-input-block showsell">

            是<input type="radio" name="openjin_wei" value="1" title="是"  <?php
             if(sysconf('openjin_wei') == 1){
              echo 'checked';
             }
            ?>>
            否<input type="radio" name="openjin_wei" value="0" title="否"   <?php
             if(sysconf('openjin_wei') == 0){
              echo 'checked';
             }
            ?>>
        </div>
    </div>
     <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            当天发币量(枚)
        </label>
        <div class='col-sm-3'>
            <input name="amount_of_coins" title="当天发币量(枚)" type="number" placeholder="当天发币量(枚)" value="<?php echo sysconf('amount_of_coins'); ?>"
                   class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            共振额度失效时间（小时）
        </label>
        <div class='col-sm-3'>
            <input name="gz_delete_time" title="共振额度失效时间（小时）" type="number" placeholder="共振额度失效时间（小时）" value="<?php echo sysconf('gz_delete_time'); ?>"
                   class="layui-input">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            持币生息系数
        </label>
        <div class='col-sm-3'>
            <input name="hold_interest_coe" title="持币生息系数"  placeholder="持币生息系数" value="<?php echo sysconf('hold_interest_coe'); ?>"
                   class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            每台矿机每天收益
        </label>
        <div class='col-sm-3'>
            <input name="mlin_foc_num" title="每台矿机每天收益"  placeholder="每台矿机每天收益" value="<?php echo sysconf('mlin_foc_num'); ?>"
                   class="layui-input">
        </div>
    </div>
     <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            交易FOC限价(单价$)
        </label>
        <div class='col-sm-3'>
            <input name="jiaoyi_foc" title="交易FOC限价(单价$)" type="number" placeholder="交易NF限价(单价$)" value="<?php echo sysconf('jiaoyi_foc'); ?>"
                   class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            是否启用显示市场价
        </label>
        <div class="col-sm-3">

            是&nbsp;<input type="radio" name="open_shijia" value="1" title="是"  <?php
             if(sysconf('open_shijia') == 1){
              echo 'checked';
             }
            ?>>
            否&nbsp;<input type="radio" name="open_shijia" value="0" title="否"   <?php
             if(sysconf('open_shijia') == 0){
              echo 'checked';
             }
            ?>>
        </div>
    </div>
    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            交易FOC最低价(单价$)
        </label>
        <div class='col-sm-3'>
            <input name="jiaoyi_foc_low" title="交易FOC最低价(单价$)" type="number" placeholder="交易FOC最低价(单价$)" value="<?php echo sysconf('jiaoyi_foc_low'); ?>"
                   class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            交易FOC最高价(单价$)
        </label>
        <div class='col-sm-3'>
            <input name="jiaoyi_foc_high" title="交易FOC最高价(单价$)" type="number" placeholder="交易FOC最高价(单价$)" value="<?php echo sysconf('jiaoyi_foc_high'); ?>"
                   class="layui-input">
        </div>
        </div>
    <!-- <div class="form-group">
        <label class="col-sm-2 control-label">
            申购总发行量
        </label>
        <div class='col-sm-2'>
            <input name="shengou_nums" title="总发行量" type="number" placeholder="总发行量" value="<?php echo sysconf('shengou_nums'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            每天虚拟数量
        </label>
        <div class='col-sm-2'>
            <input name="shengou_dayxuni" title="每天虚拟数量" type="number" placeholder=" 每天虚拟数量" value="<?php echo sysconf('shengou_dayxuni'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            每天实发数量
        </label>
        <div class='col-sm-2'>
            <input name="shengou_dayshifa" title="每天实发数量" type="number" placeholder=" 每天实发数量" value="<?php echo sysconf('shengou_dayshifa'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            申购实际价格
        </label>
        <div class='col-sm-2'>
            <input name="shengou_newjiage" title="实际价格" type="number" placeholder=" 实际价格" value="<?php echo sysconf('shengou_newjiage'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            申购市场均价
        </label>
        <div class='col-sm-2'>
            <input name="shengou_junjia" title="市场均价" type="number" placeholder=" 市场均价" value="<?php echo sysconf('shengou_junjia'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            最佳持币
        </label>
        <div class='col-sm-2'>
            <input name="shengou_zuijia" title="最佳持币" type="number" placeholder=" 最佳持币" value="<?php echo sysconf('shengou_zuijia'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            子账号申购限制
        </label>
        <div class='col-sm-2'>
            <input name="shengou_xianzi" title="子账号申购限制" type="number" placeholder=" 子账号申购限制" value="<?php echo sysconf('shengou_xianzi'); ?>" class="layui-input">
        </div>
    </div>
    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            是否启用交易所
        </label>
        <div class="layui-input-block showsell">

            是<input type="radio" name="openC2c" value="1" title="是"  <?php
             if(sysconf('openC2c') == 1){
              echo 'checked';
             }
            ?>>
            否<input type="radio" name="openC2c" value="0" title="否"   <?php
             if(sysconf('openC2c') == 0){
              echo 'checked';
             }
            ?>>
        </div>
    </div>
     <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            交易NF限价(单价$)
        </label>
        <div class='col-sm-3'>
            <input name="jiaoyi_nf" title="交易NF限价(单价$)" type="number" placeholder="交易NF限价(单价$)" value="<?php echo sysconf('jiaoyi_nf'); ?>"
                   class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            交易NTF限价(单价$)
        </label>
        <div class='col-sm-3'>
            <input name="jiaoyi_nfc" title="交易NF限价(单价$)" type="number" placeholder="交易NF限价(单价$)" value="<?php echo sysconf('jiaoyi_nfc'); ?>"
                   class="layui-input">
        </div>
    </div>
    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            矿池每天申购人数
        </label>
        <div class='col-sm-2'>
            <input name="kuangchi_body" title="矿池每天申购人数" type="number" placeholder="矿池每天申购人数" value="<?php echo sysconf('kuangchi_body'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            产币配置
        </label>
        <div class='col-sm-5'>
            <input name="kuangchi_peizi" title="产币配置" type="text" placeholder=" 产币配置" value="<?php echo sysconf('kuangchi_peizi'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            起始投币
        </label>
        <div class='col-sm-2'>
            <input name="kuangchi_qishi" title="起始投币" type="number" placeholder=" 起始投币" value="<?php echo sysconf('kuangchi_qishi'); ?>" class="layui-input">
        </div>
    </div>

    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-1 control-label">
            认证
        </label>
        <div class='col-sm-2'>
            <input name="renzheng_money" title="认证" type="number" placeholder="认证" value="<?php echo sysconf('renzheng_money'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            内部转账手续费/NF
        </label>
        <div class='col-sm-2'>
            <input name="shouxuf_neibu" title="内部转账手续费/NF" type="number" placeholder=" 内部转账手续费/NF" value="<?php echo sysconf('shouxuf_neibu'); ?>" class="layui-input">
            <p class="help-block">收取固定手续费</p>
        </div>
        <label class="col-sm-2 control-label">
            内部转账手续费/USDT
        </label>
        <div class='col-sm-2'>
            <input name="shouxuf_neibuusdt" title="内部转账手续费/USDT" type="number" placeholder=" 内部转账手续费/USDT" value="<?php echo sysconf('shouxuf_neibuusdt'); ?>" class="layui-input">
            <p class="help-block">收取固定手续费</p>
        </div>
    </div>
    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-1 control-label">
            锁仓币数
        </label>
        <div class='col-sm-3'>
            <input name="suocang_num" title="锁仓币数" type="text" placeholder="锁仓币数" value="<?php echo sysconf('suocang_num'); ?>" class="layui-input">
        </div>
        <label class="col-sm-1 control-label">
            释放比(%)
        </label>
        <div class='col-sm-3'>
            <input name="suocang_bili" title="释放比" type="text" placeholder="释放比" value="<?php echo sysconf('suocang_bili'); ?>" class="layui-input">
        </div>
    </div>
    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-1 control-label">
            开发者部落
        </label>
        <div class='col-sm-3'>
            <input name="kaifazhebuluo" title="开发者部落" type="text" placeholder="开发者部落" value="<?php echo sysconf('kaifazhebuluo'); ?>" class="layui-input">
        </div>
        <label class="col-sm-1 control-label">
            区块链浏览器
        </label>
        <div class='col-sm-3'>
            <input name="qukuaillqi" title="区块链浏览器" type="text" placeholder="区块链浏览器" value="<?php echo sysconf('qukuaillqi'); ?>" class="layui-input">
        </div>
        <label class="col-sm-1 control-label">
            开源地址
        </label>
        <div class='col-sm-3'>
            <input name="kaiyuandizhi" title="开源地址" type="text" placeholder="开源地址" value="<?php echo sysconf('kaiyuandizhi'); ?>" class="layui-input">
        </div>
    </div>

    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            快照时间(点)
        </label>
        <div class='col-sm-2'>
            <input name="time_kuaizhao" title="快照时间" type="text" placeholder="快照时间" value="<?php echo sysconf('time_kuaizhao'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            申购时间(点)
        </label>
        <div class='col-sm-2'>
            <input name="time_shengouadd" title="开始时间" type="text" placeholder="开始时间" value="<?php echo sysconf('time_shengouadd'); ?>" class="layui-input">
            <p class="help-block">几点开始申购</p>
        </div>
        <div class='col-sm-2'>
            <input name="time_shengouold" title="结束时间" type="text" placeholder="结束时间" value="<?php echo sysconf('time_shengouold'); ?>" class="layui-input">
            <p class="help-block">几点结束申购</p>
        </div>

    </div>

    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            提现手续费（usdt）
        </label>
        <div class='col-sm-3'>
            <input name="tx_rate" title="提现手续费" type="number" placeholder="提现手续费" value="<?php echo sysconf('tx_rate'); ?>"
                   class="layui-input">
                   <p class="help-block">收取固定手续费</p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            最少起提倍数（usdt）
        </label>
        <div class='col-sm-3'>
            <input name="tx_min" title="最少起提倍数" type="number" placeholder="最少起提倍数" value="<?php echo sysconf('tx_min'); ?>"
                   class="layui-input">
        </div>
    </div>
    <div class="hr-line-dashed"></div>
      <div class="form-group">
        <label class="col-sm-2 control-label">
            每天最多提现数量（usdt）
        </label>
        <div class='col-sm-3'>
            <input name="day_lc_fd" title="美金数量" type="number" placeholder="美金数量" value="<?php echo sysconf('day_lc_fd'); ?>"
                   class="layui-input">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            理财最大封顶值单账户限定数量（$）
        </label>
        <div class='col-sm-3'>
            <input name="xd_lc_fd" title="美金数量" type="number" placeholder="美金数量" value="<?php echo sysconf('xd_lc_fd'); ?>"
                   class="layui-input">
        </div>
    </div>
    <div class="form-group">
            <label class="col-sm-2 control-label">
                NFlogo:
            </label>
            <div class='col-sm-8'>
                <img data-tips-image style="height:auto;max-height:32px;min-width:32px" src="<?php echo sysconf('nf_img'); ?>"/>
                <input type="hidden" name="nf_img" onchange="$(this).prev('img').attr('src', this.value)"
                       value="<?php echo sysconf('nf_img'); ?>" class="layui-input">
                <a class="btn btn-link" data-file="one" data-uptype="local" data-type="ico,png" data-field="nf_img">上传图片</a>

            </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            NTFlogo:
        </label>
        <div class='col-sm-8'>
            <img data-tips-image style="height:auto;max-height:32px;min-width:32px" src="<?php echo sysconf('nfc_img'); ?>"/>
            <input type="hidden" name="nfc_img" onchange="$(this).prev('img').attr('src', this.value)"
                   value="<?php echo sysconf('nfc_img'); ?>" class="layui-input">
            <a class="btn btn-link" data-file="one" data-uptype="local" data-type="ico,png" data-field="nfc_img">上传图片</a>

        </div>
    </div> -->
    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--提现手续费%（nubc）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="tx_rate_lxc" title="提现手续费" type="number" placeholder="提现手续费" value="<?php echo sysconf('tx_rate_lxc'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--最少起提倍数（nubc）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="tx_min_lxc" title="最少起提倍数" type="number" placeholder="最少起提倍数" value="<?php echo sysconf('tx_min_lxc'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="hr-line-dashed"></div>-->
      <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--每天最多提现数量（nubc）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="day_lc_fd_lxc" title="美金数量" type="number" placeholder="美金数量" value="<?php echo sysconf('day_lc_fd_lxc'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            交易所手续费（%）
        </label>
        <div class='col-sm-3'>
            <input name="jys_rate" title="交易所手续费" type="number" placeholder="交易所手续费" value="<?php echo sysconf('jys_rate'); ?>"
                   class="layui-input">
        </div>
    </div>
    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--直推奖励（usdt）%-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="zt_rate" title="直推奖励" type="number" placeholder="直推奖励" value="<?php echo sysconf('zt_rate'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--回购期（%）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="hgq_rate" title="回购期" type="number" placeholder="回购期" value="<?php echo sysconf('hgq_rate'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--收益期（%）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="syq_rate" title="收益期" type="number" placeholder="收益期" value="<?php echo sysconf('syq_rate'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--支付usdt（%）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="pay_type1" title="支付usdt" type="number" placeholder="支付usdt" value="<?php echo sysconf('pay_type1'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--支付nubc（%）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="pay_type2" title="支付nubc" type="number" placeholder="支付nubc" value="<?php echo sysconf('pay_type2'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--上下各转账人数-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="number_of_transfer" title="上下各转账人数" type="number" placeholder="上下各转账人数" value="<?php echo sysconf('number_of_transfer'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--vip积分超过数量-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="score_up" title="vip积分超过数量" type="number" placeholder="vip积分超过数量" value="<?php echo sysconf('score_up'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
        <!--<label class="col-sm-2 control-label">-->
            <!--所有转账（%）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="transfer_rate" title="所有转账（%）" type="number" placeholder="所有转账（%）" value="<?php echo sysconf('transfer_rate'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->

    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--acc每天释放（%）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="acc_sf_rate" title="acc每天释放（%）" type="number" placeholder="acc每天释放（%）" value="<?php echo sysconf('acc_sf_rate'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    
    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--后台审核通过赠送待释放acc（数量）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="shtg_dsf_score" title="后台审核通过赠送待释放acc（数量）" type="number" placeholder="后台审核通过赠送待释放acc（数量）" value="<?php echo sysconf('shtg_dsf_score'); ?>" class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
           <!--后台审核通过赠送推荐人待释放acc（数量）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="shtg_dsf__tj_score" title="后台审核通过赠送推荐人待释放acc（数量）" type="number" placeholder="后台审核通过赠送推荐人待释放acc（数量）" value="<?php echo sysconf('shtg_dsf__tj_score'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    
    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
           <!--一个手机号注册（数量）-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="phone_reg" title="一个手机号注册（数量）" type="number" placeholder="一个手机号注册（数量）" value="<?php echo sysconf('phone_reg'); ?>" class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
     <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--虚拟OPF全网算力(OPF_web)-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="pdr_web" title="虚拟OPF全网算力" type="number"placeholder="虚拟OPF全网算力" value="<?php echo sysconf('pdr_web'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--抽奖消耗OPF-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="lottery_num" title="抽奖消耗OPF" type="number"placeholder="抽奖消耗OPF" value="<?php echo sysconf('lottery_num'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->

    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--买家交易挖矿奖励%-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="buy_mining" title="买家交易挖矿奖励" type="number"placeholder="买家交易挖矿奖励%" value="<?php echo sysconf('buy_mining'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--卖家交易挖矿奖励%-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="seller_mining" title="卖家交易挖矿奖励" type="number"placeholder="卖家交易挖矿奖励%" value="<?php echo sysconf('seller_mining'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->

    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--转账收款方OPF利率百分点-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="merchant_pdr_shou" title="转账收款方OPF利率百分点" type="number"placeholder="转账收款方OPF利率百分点%" value="<?php echo sysconf('merchant_pdr_shou'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--扫码转账(收款方分享挖矿百分点)-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="merchant_mining_shou" title="扫码转账(收款方分享挖矿百分点)" type="number"placeholder="扫码转账(收款方分享挖矿百分点)" value="<?php echo sysconf('merchant_mining_shou'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--实名认证赠送分享算力-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="sharenoting" title="实名认证赠送分享算力" placeholder="实名认证赠送分享算力" value="<?php echo sysconf('sharenoting'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->

    <!--</div>-->
    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
    <!--    <label class="col-sm-2 control-label">-->
    <!--       区块地址：-->
    <!--    </label>-->
    <!--    <div class='col-sm-3'>-->
    <!--        <input name="qukuai" title="区块地址" placeholder="区块地址" value="<?php echo sysconf('qukuai'); ?>"-->
    <!--               class="layui-input">-->
    <!--    </div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
    <!--    <label class="col-sm-2 control-label">-->
    <!--        区块地址收款码:-->
    <!--    </label>-->
    <!--    <div class='col-sm-8'>-->
    <!--        <img data-tips-image style="height:auto;max-height:32px;min-width:32px" src="<?php echo sysconf('qukuaisk'); ?>"/>-->
    <!--        <input type="hidden" name="qukuaisk" onchange="$(this).prev('img').attr('src', this.value)"-->
    <!--               value="<?php echo sysconf('qukuaisk'); ?>" class="layui-input">-->
    <!--        <a class="btn btn-link" data-file="one" data-uptype="local" data-type="ico,png" data-field="qukuaisk">上传图片</a>-->

    <!--    </div>-->
    <!--</div>-->

    <!--<div class="hr-line-dashed"></div>-->
        <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--支付宝姓名：-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="zhifubaoname" title="支付宝姓名" placeholder="支付宝姓名" value="<?php echo sysconf('zhifubaoname'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
        <!--<label class="col-sm-2 control-label">-->
            <!--支付宝账号：-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="zhifubao" title="支付宝账号" placeholder="支付宝账号" value="<?php echo sysconf('zhifubao'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--支付宝收款码:-->
        <!--</label>-->
        <!--<div class='col-sm-8'>-->
            <!--<img data-tips-image style="height:auto;max-height:32px;min-width:32px" src="<?php echo sysconf('zhifubaosk'); ?>"/>-->
            <!--<input type="hidden" name="zhifubaosk" onchange="$(this).prev('img').attr('src', this.value)"-->
                   <!--value="<?php echo sysconf('zhifubaosk'); ?>" class="layui-input">-->
            <!--<a class="btn btn-link" data-file="one" data-uptype="local" data-type="ico,png" data-field="zhifubaosk">上传图片</a>-->

        <!--</div>-->
    <!--</div>-->

    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--微信名称：-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="weixinname" title="微信名称" placeholder="微信名称" value="<?php echo sysconf('weixinname'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
        <!--<label class="col-sm-2 control-label">-->
            <!--微信账号：-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="weixin" title="微信账号" placeholder="微信账号" value="<?php echo sysconf('weixin'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--微信收款码:-->
        <!--</label>-->
        <!--<div class='col-sm-8'>-->
            <!--<img data-tips-image style="height:auto;max-height:32px;min-width:32px" src="<?php echo sysconf('weixinsk'); ?>"/>-->
            <!--<input type="hidden" name="weixinsk" onchange="$(this).prev('img').attr('src', this.value)"-->
                   <!--value="<?php echo sysconf('weixinsk'); ?>" class="layui-input">-->
            <!--<a class="btn btn-link" data-file="one" data-uptype="local" data-type="ico,png" data-field="weixinsk">上传图片</a>-->

        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--银行卡姓名：-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="usename" title="银行卡姓名" placeholder="银行卡姓名" value="<?php echo sysconf('usename'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
        <!--<label class="col-sm-2 control-label">-->
            <!--开户行：-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="yhname" title="开户行" placeholder="开户行" value="<?php echo sysconf('yhname'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--银行卡卡号：-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="yhkh" title="银行卡卡号" placeholder="银行卡卡号" value="<?php echo sysconf('yhkh'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!-- <div class="form-group">
        <label class="col-sm-2 control-label">
            清空数据库
        </label>
        <div class="layui-input-block showsell">

            是<input type="radio" name="is_clearSql" value="-1" title="是"  <?php
             if(sysconf('is_clearSql') == -1){
              echo 'checked';
             }
            ?>>
            否<input type="radio" name="is_clearSql" value="1" title="否"   <?php
             if(sysconf('is_clearSql') == 1){
              echo 'checked';
             }
            ?>>
        </div>
    </div> -->
    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            联系客服邮箱 ：
        </label>
        <div class='col-sm-3'>
            <input name="kfweixin" title="联系客服邮箱" placeholder="联系客服邮箱" value="<?php echo sysconf('kfweixin'); ?>"
                   class="layui-input">
        </div>
    </div>

    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--客服工作时间：-->
        <!--</label>-->
        <!--<div class='col-sm-3'>-->
            <!--<input name="kefutime" title="客服工作时间" placeholder="客服工作时间" value="<?php echo sysconf('kefutime'); ?>"-->
                   <!--class="layui-input">-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--客服二维码:-->
        <!--</label>-->
        <!--<div class='col-sm-8'>-->
            <!--<img data-tips-image style="height:auto;max-height:32px;min-width:32px" src="<?php echo sysconf('kefuphoto'); ?>"/>-->
            <!--<input type="hidden" name="kefuphoto" onchange="$(this).prev('img').attr('src', this.value)"-->
                   <!--value="<?php echo sysconf('kefuphoto'); ?>" class="layui-input">-->
            <!--<a class="btn btn-link" data-file="one" data-uptype="local" data-type="ico,png" data-field="kefuphoto">上传图片</a>-->

        <!--</div>-->
    <!--</div>-->




    <!--<div class="hr-line-dashed"></div>-->
    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--Browser<br><span class="nowrap color-desc">浏览器图标</span>-->
        <!--</label>-->
        <!--<div class='col-sm-8'>-->
            <!--<img data-tips-image style="height:auto;max-height:32px;min-width:32px" src="<?php echo sysconf('browser_icon'); ?>"/>-->
            <!--<input type="hidden" name="browser_icon" onchange="$(this).prev('img').attr('src', this.value)"-->
                   <!--value="<?php echo sysconf('browser_icon'); ?>" class="layui-input">-->
            <!--<a class="btn btn-link" data-file="one" data-uptype="local" data-type="ico,png" data-field="browser_icon">上传图片</a>-->
            <!--<p class="help-block">建议上传ICO图标的尺寸为128x128px，此图标用于网站标题前，<a href="http://www.favicon-icon-generator.com/"-->
                                                                       <!--target="_blank">ICON在线制作</a></p>-->
        <!--</div>-->
    <!--</div>-->

    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--Browser<br><span class="nowrap color-desc">logo图片</span>-->
        <!--</label>-->
        <!--<div class='col-sm-8'>-->
            <!--<img data-tips-image style="height:auto;max-height:32px;min-width:32px" src="<?php echo sysconf('image_logo'); ?>"/>-->
            <!--<input type="hidden" name="image_logo" onchange="$(this).prev('img').attr('src', this.value)"-->
                   <!--value="<?php echo sysconf('image_logo'); ?>" class="layui-input">-->
            <!--<a class="btn btn-link" data-file="one" data-uptype="local" data-type="png" data-field="image_logo">上传图片</a>-->
        <!--</div>-->
    <!--</div>-->

    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--Miitbeian<br><span class="nowrap color-desc">网站备案</span>-->
        <!--</label>-->
        <!--<div class='col-sm-8'>-->
            <!--<input name="miitbeian" title="请输入网站备案号" placeholder="请输入网站备案号" value="<?php echo sysconf('miitbeian'); ?>"-->
                   <!--class="layui-input">-->
            <!--<p class="help-block">网站备案号，可以在<a target="_blank" href="http://www.miitbeian.gov.cn">备案管理中心</a>查询获取</p>-->
        <!--</div>-->

    <!--</div>-->

    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--Android下载链接<br><span class="nowrap color-desc">Android下载链接下载链接</span>-->
        <!--</label>-->
        <!--<div class='col-sm-8'>-->
            <!--<input name="android_app_download_link" required="required" title="Android下载链接下载链接" placeholder="Android下载链接下载链接"-->
                   <!--value="<?php echo sysconf('android_app_download_link'); ?>" class="layui-input">-->
            <!--<p class="help-block">程序的版权信息设置，在后台登录页面显示</p>-->
        <!--</div>-->
    <!--</div>-->

    <!--<div class="form-group">-->
        <!--<label class="col-sm-2 control-label">-->
            <!--ios下载链接<br><span class="nowrap color-desc">ios下载链接下载链接</span>-->
        <!--</label>-->
        <!--<div class='col-sm-8'>-->
            <!--<input name="ios_app_download_link" required="required" title="ios下载链接下载链接" placeholder="ios下载链接下载链接"-->
                   <!--value="<?php echo sysconf('ios_app_download_link'); ?>" class="layui-input">-->
            <!--<p class="help-block">程序的版权信息设置，在后台登录页面显示</p>-->
        <!--</div>-->
    <!--</div>-->
    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            版本号
        </label>
        <div class='col-sm-3'>
            <input name="banbenhao" title="版本号" type="text" placeholder="版本号" value="<?php echo sysconf('banbenhao'); ?>" class="layui-input">
        </div>
        <label class="col-sm-2 control-label">
            更新地址
        </label>
        <div class='col-sm-3'>
            <input name="gengxindizi" title="更新地址" type="text" placeholder="更新地址" value="<?php echo sysconf('gengxindizi'); ?>" class="layui-input">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">
            contract<br><span class="nowrap color-desc">更新说明</span>
        </label>
        <div class='col-sm-4'>
            <textarea name="contract"><?php echo sysconf('contract'); ?></textarea>
            <script>
                require(['ckeditor'], function () {
                    var editor = window.createEditor('[name="contract"]');
                });
            </script>
        </div>

    </div>
    <div class="hr-line-dashed"></div>

    <div class="col-sm-4 col-sm-offset-2">
        <div class="layui-form-item text-center">
            <button class="layui-btn" type="submit">保存配置</button>
        </div>
    </div>

</form>
<script>
    //监听指定开关
    form.on('switch(switchTest)', function(data){
        layer.msg('开关checked：'+ (this.checked ? 'true' : 'false'), {
            offset: '6px'
        });
        layer.tips('温馨提示：请注意开关状态的文字可以随意定义，而不仅仅是ON|OFF', data.othis)
    });

</script>

</div>
</div>

<!-- 右则内容区域 结束 -->