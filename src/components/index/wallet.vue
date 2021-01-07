<template>
  <div class="content">
    <Foot :status="4"></Foot>
    <div class="head">OPF</div>
    <div class="top1">
      <p>{{profile.account_money*1}}</p>
      <p>总资产</p>
    </div>
    <!--<div class="top2">-->
      <!--<p>挖矿：0.000</p>-->
      <!--<p>兑换</p>-->
    <!--</div>-->

    <div class="title">
      <p></p>
      <p>主流币</p>
    </div>

    <div class="xxx">
      <div class="box" v-for="i in list">
        <div>
          <img src="../../../static/image/y.png" alt="" v-if="i.name == 'ETH以太坊'">
          <img src="../../../static/image/b.png" alt="" v-if="i.name == 'BTC比特币'">
          <img src="../../../static/image/e.png" alt="" v-if="i.name == 'EOS柚子'">
          <img src="../../../static/image/newxx.png" alt="" v-if="i.name == 'BTT比特流'">
        </div>
        <div>
          <p>{{i.name}}</p>
          <p>{{i.hash_money}}</p>
        </div>
        <div>
          <p>${{i.dollar_money*1}}</p>
          <p>≈{{i.last*1}} CNY</p>
        </div>
      </div>
    </div>

  </div>
</template>

<script type="text/ecmascript-6">
  import foot from '../bass/foot'
  export default {
    data() {
      return {
        info:{},
        profile:{},
        list:[]
      }
    },
    components: {
      Foot: foot
    },
    methods: {},
    created() {
      let that = this;
      that.$post('/apiv1/index/profile').then(res=>{
        if(res.code == 1){
          that.profile = res.msg
        }
      })
      that.$post('/apiv1/common/getWalletList').then(res=>{
        if(res.code == 1){
          that.list = res.data
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
    background: url("../../../static/image/wallet_bg.png") no-repeat;
    background-size: cover;
    .head{
      width: 676px/$ppr;
      height: 60px/$ppr;
      margin: 0 auto;
      line-height: 60px/$ppr;
      padding-top: 82px/$ppr;
      font-size: 36px/$ppr;
      color: #fff;
    }
    .top1{
      width: 676px/$ppr;
      height: 79px/$ppr;
      padding-top: 20px/$ppr;
      margin: 0 auto;
      border-bottom: 2px/$ppr solid #5c728f;
      p:nth-child(1){
        float: left;
        font-size: 40px/$ppr;
        color: #fff;
      }
      p:nth-child(2){
        width: 144px/$ppr;
        height: 41px/$ppr;
        float: right;
        background: #f19703;
        text-align: center;
        line-height: 41px/$ppr;
        font-size: 24px/$ppr;
        color: #fff;
        border-radius: 20px/$ppr;
      }
    }
    .top2{
      width: 676px/$ppr;
      height: 180px/$ppr;
      padding-top: 20px/$ppr;
      margin: 0 auto;
      p:nth-child(1){
        float: left;
        font-size: 28px/$ppr;
        color: #fff;
      }
      p:nth-child(2){
        width: 144px/$ppr;
        height: 41px/$ppr;
        float: right;
        background: #f19703;
        text-align: center;
        line-height: 41px/$ppr;
        font-size: 24px/$ppr;
        color: #fff;
        border-radius: 20px/$ppr;
      }
    }
    .title{
      width: 676px/$ppr;
      height: 86px/$ppr;
      background-color: #121d4b;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      line-height: 86px/$ppr;
      font-size: 30px/$ppr;
      color: #fff;
      margin-top: 100px/$ppr;
      p:nth-child(1){
        float: left;
        width: 12px/$ppr;
        height: 12px/$ppr;
        background-color: #8b98bb;
        border-radius: 50%;
        margin: 37px/$ppr 19px/$ppr 0 29px/$ppr;
      }
      p:nth-child(2){
        float: left;
      }
    }
    .xxx{
      width: 100%;
      height: auto;
      padding-bottom: 200px/$ppr;
    }
    .box{
      width: 676px/$ppr;
      height: 97px/$ppr;
      background-color: #121d4b;
      padding: 69px/$ppr 0;
      margin: 0 auto;
      margin-top: 34px/$ppr;
      border-radius: 10px/$ppr;
      div:nth-child(1){
        width: 97px/$ppr;
        height: 97px/$ppr;
        border-radius: 50%;
        float: left;
        margin: 0 32px/$ppr 0 32px/$ppr;
        background: #b1c0ff;
        img{
          width: 97px/$ppr;
          height: 97px/$ppr;
        }
      }
      div:nth-child(2){
        width: 240px/$ppr;
        height: 97px/$ppr;
        float: left;
        font-size: 28px/$ppr;
        color: #fff;
        p:nth-child(1){
          width: 100%;
          height: 56px/$ppr;
          line-height: 60px/$ppr;
          text-align: center;
        }
        p:nth-child(2){
          width: 100%;
          text-align: center;
        }
      }
      div:nth-child(3){
        /*width: 160px/$ppr;*/
        height: 97px/$ppr;
        float: right;
        font-size: 28px/$ppr;
        color: #fff;
        padding-right: 36px/$ppr;
        p:nth-child(1){
          width: 100%;
          height: 56px/$ppr;
          line-height: 60px/$ppr;
          text-align: right;
        }
        p:nth-child(2){
          width: 100%;
          text-align: right;
        }
      }
    }
  }
</style>
