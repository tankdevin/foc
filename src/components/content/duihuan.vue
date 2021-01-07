<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      兑换
      <p>记录</p>
    </div>

    <div class="title">
      兑换倒计时 <p> <span>01</span> 天 <span>03</span> 时 <span>23</span> 分 <span>15</span> 秒</p>
    </div>

    <div class="box1">
      <p></p>
      <p>OPF</p>
    </div>
    
    <div class="box2">
      <div>
        <p>OPF可用余额</p>
        <p>0.00</p>
      </div>
      <div>
        <p>GKC可用余额</p>
        <p>0.00</p>
      </div>
      <div>
        <p>当日可兑换量</p>
        <p>0.00</p>
      </div>
    </div>

    <div class="xian"></div>

    <div class="main">
      <input type="text" placeholder="请输入兑换数量">
      <p>OPF / </p>
      <span>全部</span>
    </div>
    <div class="main">
      <input type="text" placeholder="请输入交易密码">
      <p>交易密码</p>
    </div>

    <div class="send">确认兑换</div>

  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {}
        },
        components: {},
        methods: {
          back(){
            this.$router.go(-1)
          },
        },
        created() {

        },
        destroyed() {

        }
    }
</script>

<style lang="scss" scoped>
  @import "../../assets/scss/mixin";
  input::-webkit-input-placeholder{
    color: #6978b0;
  }
  .content{
    width: 100%;
    min-height: 100vh;
    height: 100%;
    background: #0d1637;
    .head{
      width: 100%;
      height: 110px/$ppr;
      position: relative;
      text-align: center;
      line-height: 110px/$ppr;
      font-size: 36px/$ppr;
      color: #fff;
      background: #0d1637;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 100;
      img{
        width: 17px/$ppr;
        height: 29px/$ppr;
        display: block;
        position: absolute;
        top: 40px/$ppr;
        left: 44px/$ppr;
      }
      p{
        height: 110px/$ppr;
        line-height: 110px/$ppr;
        font-size: 30px/$ppr;
        color: #fff;
        position: absolute;
        top: 0;
        right: 45px/$ppr;
      }
    }
    .title{
      width: 704px/$ppr;
      height: 120px/$ppr;
      border-bottom: 2px/$ppr solid #141f4c;
      margin: 0 auto;
      font-size: 30px/$ppr;
      color: #ff3363;
      line-height: 120px/$ppr;
      text-align: center;
      padding-top: 110px/$ppr;
      p{
        display: inline-block;
        font-size: 26px/$ppr;
        color: #4d577b;
        span{
          height: 40px/$ppr;
          border-radius: 5px/$ppr;
          color: #fff;
          padding: 0 5px/$ppr;
        }
        span:nth-child(1),span:nth-child(2),span:nth-child(3){
          background: #394e9a;
        }
        span:nth-child(4){
          background: #d93f4e;
        }
      }
    }
    .box1{
      width: 630px/$ppr;
      height: 93px/$ppr;
      margin: 0 auto;
      p:nth-child(1){
        width: 14px/$ppr;
        height: 14px/$ppr;
        background: #fff;
        border-radius: 50%;
        float: left;
        margin: 38px/$ppr 22px/$ppr;
      }
      p:nth-child(2){
        float: left;
        line-height: 93px/$ppr;
        font-size: 24px/$ppr;
        color: #fff;
      }
    }
    .box2{
      width: 675px/$ppr;
      height: 155px/$ppr;
      background-color: #1b285a;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      div{
        width: 33.3%;
        height: 155px/$ppr;
        float: left;
        p:nth-child(1){
          width: 100%;
          height: 90px/$ppr;
          line-height: 90px/$ppr;
          text-align: center;
          font-size: 20px/$ppr;
          color: #7981a5;
        }
        p:nth-child(2){
          width: 100%;
          text-align: center;
          font-size: 30px/$ppr;
          color: #fff;
        }
      }
    }
    .xian{
      width: 704px/$ppr;
      height: 2px/$ppr;
      background: #141f4c;
      margin: 40px/$ppr auto;
    }
    .main{
      width: 670px/$ppr;
      height: 74px/$ppr;
      margin: 0 auto;
      margin-bottom: 46px/$ppr;
      input{
        width: 421px/$ppr;
        height: 74px/$ppr;
        padding-left: 20px/$ppr;
        background-color: #1b285a;
        border-radius: 10px/$ppr;
        display: inline-block;
        border: none;
        outline: none;
        margin-right: 38px/$ppr;
      }
      p{
        display: inline-block;
        line-height: 74px/$ppr;
        color: #fff;
        font-size: 30px/$ppr;
      }
      span{
        display: inline-block;
        line-height: 74px/$ppr;
        color: #008aff;
        font-size: 30px/$ppr;
      }
    }
    .send{
      width: 675px/$ppr;
      height: 95px/$ppr;
      background-color: #008aff;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      margin-top: 120px/$ppr;
      text-align: center;
      line-height: 95px/$ppr;
      font-size: 30px/$ppr;
      color: #fff;
    }
  }
</style>
