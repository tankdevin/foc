<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      关于我们
    </div>
    <div class="main" v-html="content"></div>
  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        content:''
      }
    },
    components: {},
    methods: {
      back(){
        this.$router.go(-1)
      }
    },
    created() {
      this.$post('/apiv1/index/notice',{type: 'about'}).then(res=>{
        if(res.code == 1){
          this.content = res.msg[0].content
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
      text-align: center;
      line-height: 110px/$ppr;
      font-size: 36px/$ppr;
      color: #fff;
      position: fixed;
      top: 0;
      left: 0;
      background: #0d1637;
      img{
        width: 17px/$ppr;
        height: 29px/$ppr;
        display: block;
        position: fixed;
        top: 40px/$ppr;
        left: 44px/$ppr;
      }
    }
    .main{
      width: 660px/$ppr;
      height: auto;
      margin: 0 auto;
      padding-top: 110px/$ppr;
      color: #fff;
      font-size: 24px/$ppr;
      line-height: 60px/$ppr;
      overflow: hidden;
      img{
        width: 100%;
      }
    }
  }
</style>
