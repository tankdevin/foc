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

namespace app\financial\controller;

use app\financial\service\GoodsService;
use controller\BasicAdmin;
use service\DataService;
use service\ToolsService;
use think\Db;
use think\exception\HttpResponseException;

/**
 * 商店商品管理
 * Class Goods
 * @package app\store\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Licai extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'financialLicai';

    /**
     * 普通商品
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '商品管理';
        $get = $this->request->get();
        $db = Db::name($this->table)->where(['is_deleted' => '0']);
//        if (isset($get['tags_id']) && $get['tags_id'] !== '') {
//            $db->whereLike('tags_id', "%,{$get['tags_id']},%");
//        }
        if (isset($get['title']) && $get['title'] !== '') {
            $db->whereLike('title', "%{$get['title']}%");
        }

        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        return parent::_list($db->order('status desc,sort asc,id desc'));
    }

    /**
     * 商城数据处理
     * @param array $data
     */
    protected function _data_filter(&$data)
    {
        $result = GoodsService::buildGoodsList($data);
        $this->assign([
            'brands' => $result['brand'],
            'cates'  => ToolsService::arr2table($result['cate']),
        ]);
    }

    /**
     * 添加商品
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function add()
    {
        if ($this->request->isGet()) {
            $this->title = '添加理财';
            $this->_form_assign();
            return $this->_form($this->table, 'form');
        }
        $data = $this->_form_build_data();
        $goodsID = Db::name($this->table)->insertGetId($data['main']);
        list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('financial/licai/index')];
        if($goodsID){
            $this->success('添加理财成功！', "{$base}#{$url}?spm={$spm}");
        }else{
            $this->success('添加理财失败！');
        }

        $this->success('添加理财成功！', "{$base}#{$url}?spm={$spm}");
    }

    /**
     * 编辑商品
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        if (!$this->request->isPost()) {
            $goods_id = $this->request->get('id');
            $goods = Db::name($this->table)->where(['id' => $goods_id, 'is_deleted' => '0'])->find();
            $this->_form_assign();
            return $this->fetch('form', ['vo' => $goods, 'title' => '编辑商品']);
        }
        try {
            $data = $this->_form_build_data();
            $goods_id = $this->request->post('id');
            $goods = Db::name($this->table)->where(['id' => $goods_id, 'is_deleted' => '0'])->find();
            empty($goods) && $this->error('商品编辑失败，请稍候再试！');
            foreach ($data['list'] as &$vo) {
                $vo['goods_id'] = $goods_id;
            }
            Db::transaction(function () use ($data, $goods_id, $goods) {
                // 更新商品主表
                $where = ['id' => $goods_id, 'is_deleted' => '0'];
                Db::name('financialLicai')->where($where)->update(array_merge($goods, $data['main']));
            });
        } catch (HttpResponseException $exception) {
            return $exception->getResponse();
        } catch (\Exception $e) {
            $this->error('理财产品编辑失败，请稍候再试！' . $e->getMessage());
        }
        list($base, $spm, $url) = [url('@admin'), $this->request->get('spm'), url('financial/licai/index')];
        $this->success('理财产品成功！', "{$base}#{$url}?spm={$spm}");
    }

    /**
     * 表单数据处理
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _form_assign()
    {
        list($where, $order) = [['status' => '1', 'is_deleted' => '0'], 'sort asc,id desc'];
        $specs = (array)Db::name('StoreGoodsSpec')->where($where)->order($order)->select();
        $brands = (array)Db::name('StoreGoodsBrand')->where($where)->order($order)->select();
        $cates = (array)Db::name('StoreGoodsCate')->where($where)->order($order)->select();
        // 所有的商品信息
        $where = ['is_deleted' => '0', 'status' => '1'];
        $goodsListField = 'goods_id,goods_spec,goods_stock,goods_sale';
        $goods = Db::name('StoreGoods')->field('id,goods_title,hot')->where($where)->select();
        $list = Db::name('StoreGoodsList')->field($goodsListField)->where($where)->select();
        foreach ($goods as $k => $g) {
            $goods[$k]['list'] = [];
            foreach ($list as $v) {
                ($g['id'] === $v['goods_id']) && $goods[$k]['list'][] = $v;
            }
        }
        array_unshift($specs, ['spec_title' => ' - 不使用规格模板 -', 'spec_param' => '[]', 'id' => '0']);
        $this->assign([
            'specs'  => $specs,
            'cates'  => ToolsService::arr2table($cates),
            'brands' => $brands,
            'all'    => $goods,
        ]);
    }

    /**
     * 读取POST表单数据
     * @return array
     */
    protected function _form_build_data()
    {

        list($main, $list, $post, $verify) = [[], [], $this->request->post(), false];
        empty($post['logo']) && $this->error('商品LOGO不能为空，请上传后再提交数据！');
        // 商品主数据组装
        $main['cate_id'] = $this->request->post('cate_id', '');
        $main['goods_content'] = $this->request->post('goods_content', '');
        $main['logo'] = $this->request->post('logo', '');
        $main['title'] = $this->request->post('title', '');
        $main['market_price'] = $this->request->post('market_price', '');
        $main['day'] = $this->request->post('day', '');
        $main['sy_rate'] = $this->request->post('sy_rate', '');
        $main['approach'] = $this->request->post('approach', '');
        return ['main' => $main, 'list' => $list];
    }

    /**
     * 商品库存信息更新
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function stock()
    {
        if (!$this->request->post()) {
            $goods_id = $this->request->get('id');
            $goods = Db::name('StoreGoods')->where(['id' => $goods_id, 'is_deleted' => '0'])->find();
            empty($goods) && $this->error('该商品无法操作入库操作！');
            $where = ['goods_id' => $goods_id, 'status' => '1', 'is_deleted' => '0'];
            $goods['list'] = Db::name('StoreGoodsList')->where($where)->select();
            return $this->fetch('', ['vo' => $goods]);
        }
        // 入库保存
        $goods_id = $this->request->post('id');
        list($post, $data) = [$this->request->post(), []];
        foreach ($post['spec'] as $key => $spec) {
            if ($post['stock'][$key] > 0) {
                $data[] = [
                    'goods_stock' => $post['stock'][$key],
                    'stock_desc'  => $this->request->post('desc'),
                    'goods_spec'  => $spec, 'goods_id' => $goods_id,
                ];
            }
        }
        empty($data) && $this->error('无需入库的数据哦！');
        if (Db::name('StoreGoodsStock')->insertAll($data) !== false) {
            GoodsService::syncGoodsStock($goods_id);
            $this->success('商品入库成功！', '');
        }
        $this->error('商品入库失败，请稍候再试！');
    }

    /**
     * 删除商品
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("理财产品删除成功！", '');
        }
        $this->error("理财产品删除失败，请稍候再试！");
    }

    /**
     * 商品禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("理财产品下架成功！", '');
        }
        $this->error("理财产品下架失败，请稍候再试！");
    }

    /**
     * 商品禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("理财产品上架成功！", '');
        }
        $this->error("理财产品上架失败，请稍候再试！");
    }

}
