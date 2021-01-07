<template>
  <div class="content">
    <Foot :status="2"></Foot>
    <div class="nav">
      <div :class="tabs == 1 ? 'nav_check' : 'nav_no'" @click="checktab(1)">买入</div>
      <div :class="tabs == 2 ? 'nav_check' : 'nav_no'" @click="checktab(2)">卖出</div>
    </div>

    <div class="main">
      <div class="box">
        <p v-html="tabs == 1 ? '买入区间' : '卖出区间'"></p>
        <select name="" id="" v-model="select_val">
          <option :value="index" v-for="(i,index) in sele">{{i.title}}</option>
        </select>
      </div>
      <div class="box1">
        <p>OPF价格</p>
        <input type="text" v-model="money" readonly>
        <span>元</span>
      </div>
      <div class="box">
        <p v-html="tabs == 1 ? '买入数量' : '卖出数量'"></p>
        <input type="number" v-model="num" >
      </div>
      <div class="box1">
        <p>当前总价</p>
        <input type="text" :value="jiequ(num*money)" readonly>
        <span>元</span>
      </div>
      <div class="box1">
        <p>可用OPF</p>
        <input type="text" v-model="profile.account_money*1" readonly>
        <span>OPF</span>
      </div>
      <div class="box2" v-html="tabs == 1 ? '买入' : '卖出'" @click="jiaoyi"></div>
    </div>

    <div class="show" v-if="tabs == 1">
      <p>交易状态</p>
      <p @click="loadmore()">查看全部</p>
    </div>
    <div class="list" v-for="i in list" v-if="tabs == 1">
      <div class="mairu"></div>
      <div>{{i.title}}</div>
      <div>数量：{{i.num}}</div>
      <div v-if="i.status == 1">待匹配</div>
      <div @click="buy(i)">买入</div>
    </div>

  </div>
</template>

<script type="text/ecmascript-6">
  import foot from '../bass/foot'
  export default {
    data() {
      return {
        tabs: 1,
        sele:[],
        select_val: 0,
        list:[],
        money:'',//买入卖出价格
        profile:{},
        num:'',
      }
    },
    components: {
      Foot: foot
    },
    methods: {
      checktab(e){
        this.tabs = e;
      },
      loadmore(){
        this.$router.push('/pdrlist')
      },
      getall(){
        let that = this;
        that.list = [];
        that.$post('/apiv1/index/getTransCate').then(res=>{
          if(res.code == 1){
            that.sele = res.msg.select_list;
            that.money = res.msg.pdr_money;
          }
        })

        that.$post('/apiv1/index/profile').then(res=>{
          if(res.code == 1){
            that.profile = res.msg
          }
        })

        that.$post('/apiv1/index/getOtherPayOutOrder').then(res=>{
          if(res.code == 1){
            that.list = res.msg;
          }
        })
      },
      jiaoyi(){
        let that = this;
        if(that.num == ''){
          that.$msg(that.tabs == 1 ? '请输入买入数量' : '请输入卖出数量');
        }else{

          layer.open({
            content: that.tabs == 1 ? '您确定要买入吗？' : '您确定要卖出吗？',
            btn: ['确定', '取消'],
            yes:function (index) {
              layer.close(index);
              that.$post('/apiv1/index/buyOrderOutOrInto',{
                type: that.tabs,
                interval_id: that.sele[that.select_val].id,
                num: that.num,
                real_price: that.num*that.money
              }).then(res=>{
                that.$msg(res.msg)
                if(res.code == 1){
                  that.num = ''
                  if(that.tabs == 2){
                    that.$router.replace('/')
                  }
                }
              })
            }
          })
        }
      },
      buy(i){
        let that = this;
        layer.open({
          content: '您确定要买入吗？',
          btn: ['确定', '取消'],
          yes: function(index){
            layer.close(index);
            that.$post('/apiv1/index/buyOrderOutOrInto',{
              type: 1,
              interval_id: i.interval_id,
              num: i.num,
              real_price: i.real_price,
              orderid: i.id
            }).then(res=>{
              that.$msg(res.msg)
              if(res.code == 1){
                that.$router.push('/jiaoyizhong?id=' + res.data.orderid)
              }
            })
          }
        });
      }
    },
    created() {
      this.getall()
    },
    destroyed() {

    }
  }
</script>

<style lang="scss" scoped>
  @import "../../assets/scss/mixin";
  .content{
    width: 100%;
    min-height: 100vh;
    height: 100%;
    background: #0d1637;
    .nav{
      width: 100%;
      height: 108px/$ppr;
      background: #161d49;
      div{
        width: 50%;
        height: 108px/$ppr;
        float: left;
        font-size: 30px/$ppr;
        color: #fff;
        line-height: 108px/$ppr;
        text-align: center;
      }
      .nav_check{
        border-bottom: 5px/$ppr solid #02d882;
      }
      .nav_no{
        border: none;
      }
    }
    .main{
      width: 660px/$ppr;
      height: auto;
      margin: 0 auto;
      clear: both;
      .box{
        width: 300px/$ppr;
        height: 190px/$ppr;
        margin: 0 15px/$ppr;
        float: left;
        p{
          width: 100%;
          height: 108px/$ppr;
          line-height: 124px/$ppr;
          font-size: 26px/$ppr;
          color: #949dbe;
        }
        input{
          width: 280px/$ppr;
          height: 82px/$ppr;
          padding-left: 20px/$ppr;
          background: #14204a;
          display: block;
          border: none;
          outline: none;
          color: #fff;
        }
        select{
          width: 300px/$ppr;
          height: 82px/$ppr;
          padding-left: 20px/$ppr;
          background: #14204a;
          display: block;
          border: none;
          outline: none;
          color: #fff;
          appearance: none;/*清除select下拉框默认样式*/
        }
      }
      .box1{
        width: 300px/$ppr;
        height: 190px/$ppr;
        margin: 0 15px/$ppr;
        float: left;
        position: relative;
        p{
          width: 100%;
          height: 108px/$ppr;
          line-height: 124px/$ppr;
          font-size: 26px/$ppr;
          color: #949dbe;
        }
        input{
          width: 280px/$ppr;
          height: 82px/$ppr;
          padding-left: 20px/$ppr;
          background: #14204a;
          display: block;
          border: none;
          outline: none;
          color: #fff;
        }
        span{
          display: block;
          height: 82px/$ppr;
          line-height: 82px/$ppr;
          font-size: 26px/$ppr;
          color: #949dbe;
          position: absolute;
          bottom: 0;
          right: 20px/$ppr;
        }
      }
      .box2{
        width: 300px/$ppr;
        height: 82px/$ppr;
        margin: 108px/$ppr 15px/$ppr 0 15px/$ppr;
        float: left;
        font-size: 30px/$ppr;
        color: #fff;
        text-align: center;
        line-height: 82px/$ppr;
        background-color: #02d882;
      }
    }
    .show{
      width: 633px/$ppr;
      height: 60px/$ppr;
      margin: 0 auto;
      padding-top: 70px/$ppr;
      clear: both;
      font-size: 30px/$ppr;
      color: #949dbe;
      p:nth-child(1){
        float: left;
      }
      p:nth-child(2){
        float: right;
      }
    }
    .list{
      width: 676px/$ppr;
      height: 57px/$ppr;
      margin: 0 auto;
      border-radius: 10px/$ppr;
      margin-bottom: 13px/$ppr;
      background: #5d97df;
      .mairu{
        background-color: #5ce6ac;
      }
      .maichu{
        background-color: red;
      }
      div{
        display: inline-block;
      }
      div:nth-child(1){
        width: 16px/$ppr;
        height: 16px/$ppr;
        border-radius: 50%;
        margin: 21px/$ppr 22px/$ppr 0 22px/$ppr;
      }
      div:nth-child(2){
        width: 140px/$ppr;
        height: 57px/$ppr;
        line-height: 57px/$ppr;
        font-size: 24px/$ppr;
        color: #fff;
      }
      div:nth-child(3){
        width: 140px/$ppr;
        height: 57px/$ppr;
        line-height: 57px/$ppr;
        font-size: 24px/$ppr;
        color: #fff;
      }
      div:nth-child(4){
        width: 140px/$ppr;
        height: 57px/$ppr;
        line-height: 57px/$ppr;
        font-size: 24px/$ppr;
        color: #fff;
      }
      div:nth-child(5){
        width: 128px/$ppr;
        height: 39px/$ppr;
        background-color: #0957b8;
        border-radius: 10px/$ppr;
        /*margin-top: 6px/$ppr;*/
        font-size: 24px/$ppr;
        color: #fff;
        text-align: center;
        line-height: 39px/$ppr;
      }
    }
  }
</style>
