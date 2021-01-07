<?php /*a:2:{s:64:"D:\workspaces\www.ntf.com\application\admin\view\index\main.html";i:1605002608;s:68:"D:\workspaces\www.ntf.com\application\admin\view\public\content.html";i:1595314206;}*/ ?>
<!-- 右则内容区域 开始 -->

<div class="layui-card">
    <!--<?php if(!(empty($title) || (($title instanceof \think\Collection || $title instanceof \think\Paginator ) && $title->isEmpty()))): ?>-->
    <div class="layui-header notselect">
        <div class="pull-left"><span><?php echo htmlentities($title); ?></span></div>
        <div class="pull-right margin-right-15 nowrap"></div>
    </div>
    <!--<?php endif; ?>-->
    <div class="layui-card-body">

<table class="layui-table" style="width:50%" lay-even lay-skin="line">
    <colgroup>
        <col width="20%">
        <col width="30%">
        <col width="20%">
        <col width="30%">
    </colgroup>
    <thead>
    <tr>
        <!--<th class="text-left" colspan="2">总计</th>-->
        <!--<th class="text-left" colspan="2">每天</th>-->
    </tr>
    </thead>
    <tbody>

    <tr>
        <td style="max-width:200px" class="nowrap">总人数</td>
        <td class="nowrap"><?php echo htmlentities($total_num); ?></td>
        <td style="max-width:200px" class="nowrap">今日注册人数</td>
        <td class="nowrap"><?php echo htmlentities($new_add_num); ?></td>
        <!--<td style="max-width:200px" class="nowrap">每天新激活人数</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($new_jl_add_num); ?></td>-->
    </tr>
    <tr>
        <td style="max-width:200px" class="nowrap">USDT总和</td>
        <td class="nowrap"><?php echo htmlentities($total_money); ?></td>
        <td style="max-width:200px" class="nowrap">矿池FOC总和</td>
        <td class="nowrap"><?php echo htmlentities($total_score); ?></td>
        <td style="max-width:200px" class="nowrap">交易FOC总和</td>
        <td class="nowrap"><?php echo htmlentities($total_nfc); ?></td>
        <!--<td style="max-width:200px" class="nowrap">每天新激活人数</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($new_jl_add_num); ?></td>-->
    </tr>
    <tr>
        <td style="max-width:200px" class="nowrap">USDT冻结总和</td>
        <td class="nowrap"><?php echo htmlentities($total_usdt_s); ?></td>
        <td style="max-width:200px" class="nowrap">矿机持有总和</td>
        <td class="nowrap"><?php echo htmlentities($total_mach); ?></td>
        <td style="max-width:200px" class="nowrap">今日交易USDT</td>
        <td class="nowrap"><?php echo htmlentities($sell_usdt); ?></td>
        <!--<td style="max-width:200px" class="nowrap">今日冻结USDT数量</td>
        <td class="nowrap"><?php echo htmlentities($shengou_usdt); ?></td> -->
    </tr>
    <!-- <tr>
        <td style="max-width:200px" class="nowrap">申购成功NF数量</td>
        <td class="nowrap"><?php echo htmlentities($shengou_nfgood); ?></td>
        <td style="max-width:200px" class="nowrap">扣除USDT数量</td>
        <td class="nowrap"><?php echo htmlentities($shengou_usdtgood); ?></td>
        <td style="max-width:200px" class="nowrap">今日申购NF数量</td>
        <td class="nowrap"><?php echo htmlentities($shengou_nf); ?></td>
        <td style="max-width:200px" class="nowrap">今日冻结USDT数量</td>
        <td class="nowrap"><?php echo htmlentities($shengou_usdt); ?></td>
    </tr>

    <tr>
        <td style="max-width:200px" class="nowrap">矿池成功NF数量</td>
        <td class="nowrap"><?php echo htmlentities($kuangchi_nfgood); ?></td>
        <td style="max-width:200px" class="nowrap">成功收益NTF数量</td>
        <td class="nowrap"><?php echo htmlentities($kuangchi_nfcgood); ?></td>
        <td style="max-width:200px" class="nowrap">今日矿池NF数量</td>
        <td class="nowrap"><?php echo htmlentities($kuangchi_nf); ?></td>
        <td style="max-width:200px" class="nowrap">今日应收益NTF数量</td>
        <td class="nowrap"><?php echo htmlentities($kuangchi_nfc); ?></td>
    </tr> -->

    <!--<tr>-->
        <!--<td class="nowrap">总USDT充值</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($total_usdt); ?></td>-->
        <!--<td class="nowrap">今日USDT充值</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($day_usdt); ?></td>-->
    <!--</tr>-->
    <!--<tr>-->
        <!--<td class="nowrap">总NUBC充值</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($total_lxc); ?></td>-->
        <!--<td class="nowrap">今日NUBC充值</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($day_lxc); ?></td>-->
    <!--</tr>-->
    <!--<tr>-->
        <!--<td class="nowrap">今日已通过提币笔数</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($day_withdraw); ?></td>-->
        <!--<td class="nowrap">今日待审核提币笔数</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($day_withdraw_dsh); ?></td>-->
    <!--</tr>-->
    <!--<tr>-->
        <!--<td class="nowrap">今日提币USDT数量</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($day_withdraw_usdt); ?></td>-->
        <!--&lt;!&ndash;<td class="nowrap">今日提币NUBC数量</td>&ndash;&gt;-->
        <!--&lt;!&ndash;<td class="nowrap"><?php echo htmlentities($day_withdraw_lxc); ?></td>&ndash;&gt;-->
    <!--</tr>-->
    <!--<tr>-->
        <!--<td class="nowrap">每天转账总金额</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($day_zz_account); ?></td>-->
        <!--<td class="nowrap">每日拆红包总金额</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($hongbao); ?></td>-->
    <!--</tr>-->
    <!--<tr>-->
        <!--<td class="nowrap">acc总数</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($acc_num); ?></td>-->
        <!--<td class="nowrap">acc提现金额</td>-->
        <!--<td class="nowrap"><?php echo htmlentities($acc_tx_num); ?></td>-->
    <!--</tr>-->
    </tbody>
</table>
</div>
</div>

<!-- 右则内容区域 结束 -->