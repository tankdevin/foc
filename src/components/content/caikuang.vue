<template>
  <div class="content">
    <img class="back" src="../../../static/image/icon_back.png" alt="" @click="back">
    <div class="head">采矿</div>
    <div class="title">
      <div>
        <p>累计分享：<span>200</span></p>
        <p>累计分享：<span>200</span></p>
      </div>
      <div>
        <p>连续采矿 <span>1</span> 天</p>
        <p>距离奖励还有 <span>6</span> 天</p>
      </div>
    </div>

    <div class="main">
        <div class="main_title">收益明细</div>
        <div class="main_txt">
          防盗门时离开父母的买房了是免费的十来块蜂蜜水代理费没收到发什么呆分离式的马卡洛夫分明是的快乐风方面的水立方没多少
        </div>
        <div class="main_title">详情说明</div>
        <div class="main_txt">
          防盗门时离开父母的买房了是免费的十来块蜂蜜水代理费没收到发什么呆分离式的马卡洛夫分明是的快乐风方面的水立方没多少
        </div>
    </div>

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
          }
        },
        created() {

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
    background: url("../../../static/image/kuang_bg.png") no-repeat;
    background-size: cover;
    .back{
      width: 17px/$ppr;
      height: 29px/$ppr;
      display: block;
      position: fixed;
      top: 40px/$ppr;
      left: 44px/$ppr;
    }
    .head{
      width: 100%;
      height: 110px/$ppr;
      line-height: 110px/$ppr;
      text-align: center;
      font-size: 32px/$ppr;
      color: #fff;
    }
    .title{
      width: 616px/$ppr;
      height: 112px/$ppr;
      background-image: linear-gradient(-90deg,
        #6a95fe 0%,
        #5d79fb 30%,
        #505df7 100%),
      linear-gradient(
          #11133a,
          #11133a);
      background-blend-mode: normal,
      normal;
      border-radius: 20px/$ppr;
      margin: 0 auto;
      margin-top: 366px/$ppr;
      div:nth-child(1){
        height: 112px/$ppr;
        padding-left: 48px/$ppr;
        float: left;
        p{
          font-size: 24px/$ppr;
          padding-top: 16px/$ppr;
          color: #fff;
          span{
            color: #2dcdfb;
          }
        }
      }
      div:nth-child(2){
        height: 112px/$ppr;
        padding-right: 48px/$ppr;
        float: right;
        p{
          font-size: 24px/$ppr;
          padding-top: 16px/$ppr;
          color: #fff;
          text-align: right;
          span{
            color: #2dcdfb;
          }
        }
      }
    }
    .main{
      width: 616px/$ppr;
      height: 667px/$ppr;
      background-color: #17134e;
      box-shadow: 0px 8px/$ppr 28.5px/$ppr 3.5px/$ppr rgba(0, 0, 0, 0.1);
      border-radius: 20px/$ppr;
      margin: 0 auto;
      margin-top: 28px/$ppr;
      overflow-y: auto;
      .main_title{
        width: 179px/$ppr;
        height: 51px/$ppr;
        margin: 0 auto;
        margin-top: 48px/$ppr;
        font-size: 26px/$ppr;
        color: #73d2e6;
        text-align: center;
        line-height: 51px/$ppr;
        background: #3b13ab;
        border-radius: 20px/$ppr;
      }
      .main_txt{
        width: 490px/$ppr;
        padding-top: 20px/$ppr;
        line-height: 40px/$ppr;
        font-size: 22px/$ppr;
        color: #fff;
        margin: 0 auto;
      }
    }
  }
</style>
