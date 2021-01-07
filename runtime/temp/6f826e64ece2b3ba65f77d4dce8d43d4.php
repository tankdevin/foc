<?php /*a:2:{s:65:"D:\workspaces\www.ntf.com\application\wechat\view\fans\index.html";i:1605085981;s:68:"D:\workspaces\www.ntf.com\application\admin\view\public\content.html";i:1595314206;}*/ ?>
<!-- 右则内容区域 开始 -->

<div class="layui-card">
    <!--<?php if(!(empty($title) || (($title instanceof \think\Collection || $title instanceof \think\Paginator ) && $title->isEmpty()))): ?>-->
    <div class="layui-header notselect">
        <div class="pull-left"><span><?php echo htmlentities($title); ?></span></div>
        <div class="pull-right margin-right-15 nowrap"></div>
    </div>
    <!--<?php endif; ?>-->
    <div class="layui-card-body">
<!-- 表单搜索 开始 -->
<form autocomplete="off" class="layui-form layui-form-pane form-search" action="<?php echo request()->url(); ?>" onsubmit="return false" method="get">
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">用户手机号</label>
        <div class="layui-input-inline">
            <input name="phone" placeholder="请输入用户手机号" value="<?php echo htmlentities((app('request')->get('phone') ?: '')); ?>"autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">用户地址</label>
        <div class="layui-input-inline">
            <input name="address" placeholder="请输入用户地址" value="<?php echo htmlentities((app('request')->get('address') ?: '')); ?>"autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">用户推荐码</label>
        <div class="layui-input-inline">
            <input name="num_id" placeholder="请输入用户推荐码" value="<?php echo htmlentities((app('request')->get('num_id') ?: '')); ?>"autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">上级推荐码</label>
        <div class="layui-input-inline">
            <input name="fir_num_id" placeholder="请输入上级推荐码" value="<?php echo htmlentities((app('request')->get('fir_num_id') ?: '')); ?>"autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">激活状态</label>
        <div class="layui-input-inline">
            <select name="is_renzheng"  lay-search="">
                <option value=''>全部</option>
                <option value='1' <?php if(app('request')->get('is_renzheng') == '1'): ?>selected<?php endif; ?>>未激活</option>
                <option value='2' <?php if(app('request')->get('is_renzheng') == '2'): ?>selected<?php endif; ?>>已激活</option>
            </select>
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">会员级别</label>
        <div class="layui-input-inline">
            <select name="level"  lay-search="">
                <option value=''>全部</option>
                <option value='0' <?php if(app('request')->get('level') == '0'): ?>selected<?php endif; ?>>普通用户</option>
                <option value='1' <?php if(app('request')->get('level') == '1'): ?>selected<?php endif; ?>>会员</option>
                <option value='2' <?php if(app('request')->get('level') == '2'): ?>selected<?php endif; ?>>节点</option>
                <option value='3' <?php if(app('request')->get('level') == '3'): ?>selected<?php endif; ?>>董事</option>
                <option value='4' <?php if(app('request')->get('level') == '4'): ?>selected<?php endif; ?>>联创</option>
                <option value='5' <?php if(app('request')->get('level') == '5'): ?>selected<?php endif; ?>>动态股东</option>
                <option value='6' <?php if(app('request')->get('level') == '6'): ?>selected<?php endif; ?>>预备节点</option>
            </select>
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">时 间</label>
        <div class="layui-input-inline">
            <input name="create_at" id='create_at' value="<?php echo htmlentities((app('request')->get('create_at') ?: '')); ?>" placeholder="时间" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <button class="layui-btn layui-btn-primary"><i class="layui-icon">&#xe615;</i> 搜 索</button>
    </div>
    <style>
        .right_label_ne{
            float: right;
            margin-right:20px;
        }
    </style>
    <div class="clearfix"></div>
</form>
<!-- 表单搜索 结束 -->
<form onsubmit="return false;" data-auto="true" method="post">
    <!--<?php if(empty($list)): ?>-->
    <p class="help-block text-center well">没 有 记 录 哦！</p>
    <!--<?php else: ?>-->
    <input type="hidden" value="resort" name="action">
    <table class="layui-table" lay-skin="line">
        <thead>
        <tr>
            <th class='list-table-check-td think-checkbox'>
                <input data-auto-none="none" data-check-target='.list-check-box' type='checkbox'/>
            </th>
            <th class='text-left'>ID</th>
            <th class='text-left'>昵称</th>
            <th class='text-left'>地址</th>
            <th class='text-left'>推荐人推荐码</th>
            <th class='text-left'>USDT</th>
            <th class='text-left'>FOC矿池</th>
            <th class='text-left'>FOC交易</th>
            <th class='text-left'>直推人数</th>
            <th class='text-left'>有效直推</th>
            <th class='text-left'>持有矿机</th>
            <th class='text-left'>推荐码</th>
            <th class='text-left'>会员级别</th>
            <th class='text-left'>是否认证</th>
            <!-- <th class='text-left'>锁仓数量</th>
            <th class='text-left'>已发数量</th> -->
            <th class='text-left'>注册时间</th>
            <!--<th class='text-left'>激活时间</th>-->
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($list as $key=>$vo): ?>
        <tr>
            <td class='list-table-check-td think-checkbox'>
                <input class="list-check-box" value='<?php echo htmlentities($vo['id']); ?>' type='checkbox'/>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities($vo['id']); ?>
            </td>
            <td class='text-left nowrap'>
            <?php echo htmlentities((isset($vo['nickname']) && ($vo['nickname'] !== '')?$vo['nickname']:$vo['phone'])); ?><br>
            <?php echo htmlentities(getusernamebyid($vo['id'])); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities($vo['address']); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities(invite_code($vo['first_leader'])); ?><br>
                <?php echo htmlentities(getusernamebyid($vo['first_leader'])); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities((isset($vo['account_money']) && ($vo['account_money'] !== '')?$vo['account_money']:'0')); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities((isset($vo['account_score']) && ($vo['account_score'] !== '')?$vo['account_score']:'0')); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities((isset($vo['account_foc']) && ($vo['account_foc'] !== '')?$vo['account_foc']:'0')); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities(getzhitui($vo['id'])); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities(getyouxiao($vo['id'])); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities((int)($vo['wallet_six'])); ?>
            </td>
            <!-- <td class='text-left nowrap'>
                <?php echo htmlentities(getTdnum($vo['id'])); ?>
            </td> -->
            <!--<td class='text-left nowrap'>-->
                <!--<?php echo htmlentities((isset($vo['total_performance']) && ($vo['total_performance'] !== '')?$vo['total_performance']:'0')); ?>-->
            <!--</td>-->
            <!--<td class='text-left nowrap'>-->
                <!--<?php echo htmlentities((isset($vo['team_performance']) && ($vo['team_performance'] !== '')?$vo['team_performance']:'0')); ?>-->
            <!--</td>-->

            <!--<td class='text-left nowrap'>-->
                <!--<?php echo htmlentities((isset($vo['mining']) && ($vo['mining'] !== '')?$vo['mining']:'0')); ?>-->
            <!--</td>-->
            <!--<td class='text-left nowrap'>-->
                <!--<?php echo htmlentities((isset($vo['integral']) && ($vo['integral'] !== '')?$vo['integral']:'0')); ?>-->
            <!--</td>-->
            <!--<td class='text-left nowrap'>-->
                <!--<?php echo htmlentities((isset($vo['credit']) && ($vo['credit'] !== '')?$vo['credit']:'0')); ?>-->
            <!--</td>-->
            <td class='text-left nowrap'>
                <?php echo htmlentities($vo['invite_code']); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities(member_level($vo['level'])); ?>
            </td>
            <td class='text-left nowrap'>
                <?php if($vo['is_renzheng'] == 1): ?>
                <span style="color:#008800;">否</span>
                <?php elseif($vo['is_renzheng'] == 2): ?>
                <span style="color:red;">是</span>
                <?php endif; ?>
            </td>
            <!-- <td class='text-left nowrap'>
                <?php echo htmlentities($vo['suocang_num']); ?>
            </td>
            <td class='text-left nowrap'>
                <?php echo htmlentities($vo['suocang_fafang']); ?>
            </td> -->
            <td class='text-left nowrap'>
                <?php echo htmlentities($vo['create_at']); ?>
            </td>
            <!--<td class='text-left nowrap'><?php if(!(empty($vo['create_jh']) || (($vo['create_jh'] instanceof \think\Collection || $vo['create_jh'] instanceof \think\Paginator ) && $vo['create_jh']->isEmpty()))): ?><?php echo htmlentities(date("Y-m-d H:i:s",!is_numeric($vo['create_jh'])? strtotime($vo['create_jh']) : $vo['create_jh'])); ?><?php endif; ?></td>-->
            <td class="text-center nowrap">
                <a title="修改用户信息" data-modal="<?php echo url('wechat/fans/user_add'); ?>?member_id=<?php echo htmlentities($vo['id']); ?>">编辑</a> |
                <a data-open="<?php echo url('admin/finance/index'); ?>?member_id=<?php echo htmlentities($vo['id']); ?>">查看流水</a> |
                <a data-modal="<?php echo url('wechat/fans/user_balance'); ?>?member_id=<?php echo htmlentities($vo['id']); ?>" data-title="操作usdt">操作usdt</a>|
                <a data-modal="<?php echo url('wechat/fans/user_kyacc'); ?>?member_id=<?php echo htmlentities($vo['id']); ?>" data-title="操作FOC矿池账户">操作矿池账户</a>|
                <a data-modal="<?php echo url('wechat/fans/user_foc'); ?>?member_id=<?php echo htmlentities($vo['id']); ?>" data-title="操作FOC交易账户">操作交易账户</a>|
                <a data-open="<?php echo url('admin/member/member_org'); ?>?member_id=<?php echo htmlentities($vo['id']); ?>">查看关系</a> |
                <a data-update="<?php echo htmlentities($vo['id']); ?>" data-action="<?php echo url('backadd'); ?>"><?php if($vo['is_disable'] == 1): ?>拉黑<?php else: ?>取消拉黑<?php endif; ?></a>|
                <a data-update="<?php echo htmlentities($vo['id']); ?>" data-action="<?php echo url('dongjie'); ?>"><?php if($vo['dongjie'] == 0): ?>冻结<?php else: ?>取消冻结<?php endif; ?></a>|
                <a data-update="<?php echo htmlentities($vo['id']); ?>" data-action="<?php echo url('delete'); ?>">删除</a>
                <!-- <?php if($vo['is_renzheng'] == 1): ?>
                |<a data-update="<?php echo htmlentities($vo['id']); ?>" data-action="<?php echo url('backaddad'); ?>">激活</a>
                <?php endif; ?> -->
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if(isset($page)): ?><p><?php echo $page; ?></p><?php endif; ?>
    <!--<?php endif; ?>-->
</form>
</div>
</div>

<!--表单初始化-->
<script>
    window.laydate.render({range: true, elem: '#create_at'});
    window.form.render();
</script>


<!-- 右则内容区域 结束 -->