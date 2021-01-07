<?php /*a:2:{s:67:"D:\workspaces\www.ntf.com\application\admin\view\c2c\complaint.html";i:1599115644;s:68:"D:\workspaces\www.ntf.com\application\admin\view\public\content.html";i:1595314206;}*/ ?>
<!-- 右则内容区域 开始 -->

<div class="layui-card">
    <!--<?php if(!(empty($title) || (($title instanceof \think\Collection || $title instanceof \think\Paginator ) && $title->isEmpty()))): ?>-->
    <div class="layui-header notselect">
        <div class="pull-left"><span><?php echo htmlentities($title); ?></span></div>
        <div class="pull-right margin-right-15 nowrap">
<!--<?php if(auth("$classuri/add")): ?>-->
<!--<button data-modal='<?php echo url("@$classuri/add"); ?>' data-title="添加等级" class='layui-btn layui-btn-sm layui-btn-primary'>添加等级</button>-->
<!--<?php endif; ?>-->
<!--<?php if(auth("$classuri/del")): ?>-->
<!--<?php endif; ?>-->
</div>
    </div>
    <!--<?php endif; ?>-->
    <div class="layui-card-body">
<!-- 表单搜索 开始 -->
<form class="layui-form layui-form-pane form-search" action="<?php echo request()->url(); ?>" onsubmit="return false" method="get">

    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">会员地址</label>
        <div class="layui-input-inline">
            <input name="address" value="<?php echo htmlentities((app('request')->get('address') ?: '')); ?>" placeholder="会员地址" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <label class="layui-form-label">时间</label>
        <div class="layui-input-inline">
            <input name="create_at" id='range-date' value="<?php echo htmlentities((app('request')->get('create_at') ?: '')); ?>"
                   placeholder="请选择时间" class="layui-input">
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <button class="layui-btn layui-btn-primary"><i class="layui-icon">&#xe615;</i> 搜 索</button>
    </div>
</form>
<script>
    window.laydate.render({range: true, elem: '#range-date'});
</script>
<form autocomplete="off" onsubmit="return false;" data-auto="true" method="post">
    <!--<?php if(empty($list)): ?>-->
    <p class="help-block text-center well">没 有 记 录 哦！</p>
    <!--<?php else: ?>-->
    <input type="hidden" value="resort" name="action">
    <table class="layui-table" lay-skin="line">
        <thead>
        <tr>
            <!--<th class='list-table-check-td think-checkbox'>-->
            <!--<input data-auto-none="none" data-check-target='.list-check-box' type='checkbox'>-->
            <!--</th>-->
            <th class='text-left nowrap'>ID</th>
            <th class='text-left nowrap'>用户地址</th>
            <th class='text-left nowrap'>反馈内容</th>
            <th class='text-left nowrap'>是否处理</th>
            <th class='text-left nowrap'>反馈时间</th>
            <th class='text-left nowrap'>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($list as $key=>$vo): ?>
        <tr>
            <td class='text-left nowrap'>
                <span class="color-desc"><?php echo htmlentities($vo['id']); ?></span>
            </td>
            <td class='text-left nowrap'>
                <span class="color-desc"><?php echo htmlentities(useraddress($vo['uid'])); ?></span>
            </td>
            <td class='text-left nowrap'>
                <span class="color-desc"><?php echo htmlentities($vo['content']); ?></span>
            </td>
            <td class='text-left nowrap'>
                <?php if($vo['status'] == 1): ?>
                <span class="color-red">已处理</span>
                <?php else: ?>
                <span class="color-green">未处理</span>
                <?php endif; ?>
            </td>



            <td class='text-left nowrap'>
                <p>反馈时间：
                    <span class="color-desc"><?php echo htmlentities(datetime($vo['caeate_at'])); ?></span>
                </p>
                <p>处理时间：
                    <span class="color-desc"><?php echo htmlentities(datetime($vo['endtime'])); ?></span>
                </p>
            </td>

            <td class='text-left nowrap'>
                <?php if($vo['status'] == 0): ?>
                <a data-modal="<?php echo url('admin/c2c/complaintcz'); ?>?member_id=<?php echo htmlentities($vo['id']); ?>&type=<?php echo htmlentities($vo['type']); ?>" data-title="操作">操作</a>
                <?php else: ?>
                <span style="color: red">已处理</span>
                <?php endif; ?>
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

<!-- 右则内容区域 结束 -->