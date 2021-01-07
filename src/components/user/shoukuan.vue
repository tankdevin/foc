<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      收款管理
    </div>
    <div class="list" v-for="i in nav" @click="to(i.type)">
      <img :src="i.url" alt="" class="list1">
      <div class="list2">{{i.name}}</div>
      <img src="../../../static/image/icon14.png" alt="" class="list3">
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              nav:[
                {url:require('../../../static/image/zhifu1.png'),name:'微信',type:1},
                {url:require('../../../static/image/zhifu2.png'),name:'支付宝',type:2},
                {url:require('../../../static/image/zhifu3.png'),name:'银行卡',type:3},
              ]
            }
        },
        components: {},
        methods: {
          back(){
            this.$router.go(-1)
          },
          to(type){
            if(type == 1){//微信
              this.$router.push('/wechat')
            }else if(type == 2){//支付宝
              this.$router.push('/alipay')
            }else if(type == 3){//银行卡
              this.$router.push('/bankcard')
            }
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
    background: #0d1637;
    .head{
      width: 100%;
      height: 110px/$ppr;
      position: relative;
      text-align: center;
      line-height: 110px/$ppr;
      font-size: 36px/$ppr;
      color: #fff;
      img{
        width: 17px/$ppr;
        height: 29px/$ppr;
        display: block;
        position: absolute;
        top: 40px/$ppr;
        left: 44px/$ppr;
      }
    }
    
    .list{
      width: 665px/$ppr;
      height: 98px/$ppr;
      border-bottom: 1px solid #141948;
      margin: 0 auto;
      .list1{
        display: block;
        width: 50px/$ppr;
        height: 50px/$ppr;
        float: left;
        margin: 24px/$ppr 48px/$ppr;
      }
      .list2{
        float: left;
        width: 470px/$ppr;
        height: 98px/$ppr;
        line-height: 98px/$ppr;
        font-size: 30px/$ppr;
        color: #fff;
      }
      .list3{
        display: block;
        float: left;
        width: 11px/$ppr;
        height: 19px/$ppr;
        margin-top: 40px/$ppr;
      }
    }
  }
</style>
