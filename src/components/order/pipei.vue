<template>
  <div class="content">
    <img src="../../../static/image/icon_back.png" alt="" class="back" @click="back">
    <div class="head">{{info.interval_info}}订单撮合中...</div>
    <div class="main">
      <div>
        <p>订单金额</p>
        <p style="color: #0495ff;">{{info.real_price*1}}</p>
      </div>
      <div>
        <p>交易单价</p>
        <p>{{info.real_price/info.num}}</p>
      </div>
      <div>
        <p>交易数量</p>
        <p>{{info.num}}</p>
      </div>
      <div>
        <p>订单编号</p>
        <p>{{info.order_no}}</p>
      </div>
      <div>
        <p>交易状态</p>
        <p style="color: #fec86b;">等待撮合中</p>
      </div>
    </div>
    <div class="send" @click="cencel">取消</div>
  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              id:'',
              info:{}
            }
        },
        components: {},
        methods: {
          back(){
            this.$router.go(-1)
          },
          cencel(){
            let that = this;
            that.$post('/apiv1/index/cancelC2cOrder',{orderId: that.id}).then(res=>{
              that.$msg(res.msg)
              if(res.code == 1){
                setTimeout(()=>{
                  that.$router.go(-1)
                },1000)
              }
            })
          }
        },
        created() {
          let that = this;
          that.id = that.$route.query.id;
          that.$post('/apiv1/index/getC2cOrderDetail',{orderId: that.id}).then(res=>{
            if(res.code == 1){
              that.info = res.data;
            }
          })
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
    background: #11133a;
    .back{
      width: 17px/$ppr;
      height: 29px/$ppr;
      position: fixed;
      top: 40px/$ppr;
      left: 40px/$ppr;
      z-index: 100;
    }
    .head{
      width: 100%;
      height: 343px/$ppr;
      line-height: 343px/$ppr;
      text-align: center;
      font-size: 36px/$ppr;
      color: #8084a6;
    }
    .main{
      width: 683px/$ppr;
      height: 450px/$ppr;
      background-color: #151e47;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      div{
        width: 100%;
        height: 90px/$ppr;
        line-height: 90px/$ppr;
        font-size: 26px/$ppr;
        color: #fff;
        p:nth-child(1){
          float: left;
          margin-left: 40px/$ppr;
        }
        p:nth-child(2){
          float: right;
          margin-right: 40px/$ppr;
        }
      }
    }
    .send{
      width: 675px/$ppr;
      height: 95px/$ppr;
      background-color: #ff3859;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      margin-top: 240px/$ppr;
      text-align: center;
      line-height: 95px/$ppr;
      font-size: 30px/$ppr;
      color: #fff;
    }
  }
</style>
