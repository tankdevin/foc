<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      联系客服
    </div>
    <div class="zhanwei"></div>
    <iframe src="https://chat10.live800.com/live800/chatClient/chatbox.jsp?companyID=1131298&configID=236344&jid=7467217094&s=1" class="box" id="box"></iframe>
  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
      }
    },
    components: {},
    methods: {
      back(){
        this.$router.go(-1)
      }
    },
    created() {
      let iframe = document.getElementById('box');
      if(iframe){
        console.log(111)
        let iframeWin = iframe.contentWindow || iframe.contentDocument.parentWindow;
        if (iframeWin.document.body) {
          iframe.height = iframeWin.document.documentElement.scrollHeight || iframeWin.document.body.scrollHeight;
        }
      }
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
    .head{
      width: 100%;
      height: 2.75rem;
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
    .zhanwei{
      width: 100%;
      height: 2.75rem;
    }
    .box{
      width: 100%;
      height: 90vh;
      overflow-x: hidden;
      overflow-y: hidden;
    }
  }
</style>
