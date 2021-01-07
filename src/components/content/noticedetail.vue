<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      资讯详情
    </div>
    <div class="title">
      {{info.title}}
    </div>
    <div class="time">{{$times(info.addtime)}}</div>
    <!--<div class="imgs">-->
      <!--<img :src="info.img" alt="">-->
    <!--</div>-->
    <div class="box" v-html="info.content"></div>
  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              info:{}
            }
        },
        components: {},
        methods: {
          back(){
            this.$router.go(-1)
          }
        },
        created() {
          let id = this.$route.query.id;
          this.$post('/apiv1/index/detail',{id: id}).then(res=>{
            if(res.code == 1){
              this.info = res.msg;
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
    background: #0d1637;
    .head{
      width: 100%;
      height: 110px/$ppr;
      position: fixed;
      top: 0;
      left: 0;
      text-align: center;
      line-height: 110px/$ppr;
      font-size: 36px/$ppr;
      color: #fff;
      background: #0d1637;
      z-index: 100;
      img{
        width: 17px/$ppr;
        height: 29px/$ppr;
        display: block;
        position: fixed;
        top: 40px/$ppr;
        left: 44px/$ppr;
      }
    }
    .title{
      width: 690px/$ppr;
      height: 60px/$ppr;
      padding-top: 130px/$ppr;
      margin: 0 auto;
      font-size: 30px/$ppr;
      color: #fff;
      overflow: hidden;
      text-overflow:ellipsis;
      white-space: nowrap;
    }
    .time{
      width: 690px/$ppr;
      height: 100px/$ppr;
      margin: 0 auto;
      font-size: 20px/$ppr;
      color: #aaa;
    }
    .imgs{
      width: 690px/$ppr;
      height: auto;
      margin: 0 auto;
      img{
        max-width: 100%;
      }
    }
    .box{
      width: 690px/$ppr;
      height: auto;
      margin: 0 auto;
      color: #fff;
      overflow: hidden;
      font-size: 20px/$ppr;
      p{
        width: 690px/$ppr!important;
      }
      img{
        max-width: 690px/$ppr;
        width: 690px/$ppr!important;
      }
    }
  }
</style>
