<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      {{info.title}}升级条件
    </div>

    <div class="main">
      <div class="box1">
        <p>社区级别</p>
        <input type="text" v-model="info.title" readonly>
      </div>
      <div class="box1">
        <p>直推正式用户</p>
        <input type="text" v-model="info.share" readonly>
      </div>
      <div class="box1">
        <p>团队粉丝人数</p>
        <input type="text" v-model="info.fans" readonly>
      </div>
      <div class="box1">
        <p>最低算力挖矿</p>
        <input type="text" v-model="info.force" readonly>
      </div>
      <div class="box1">
        <p>余额消耗数量</p>
        <input type="text" v-model="info.num" readonly>
      </div>
      <div class="box1">
        <p>最高算力挖矿</p>
        <input type="text" v-model="info.mining" readonly>
      </div>
      <div class="box1">
        <p>享受团队挖矿</p>
        <input type="text" v-model="info.teaming" readonly>
      </div>
      <div class="box1">
        <p>分享挖矿系数</p>
        <input type="text" v-model="info.coefficient">
      </div>
      <div class="send" @click="send">确认升级</div>
    </div>

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
      },
      send(){
        let that = this;
        that.$post('/apiv1/index/getUserUpgradeTips').then(res=>{
          if(res.code == 1){
            layer.open({
              content: res.msg,
              btn: ['确定', '取消'],
              yes: function(index){
                layer.close(index);
                that.$post('/apiv1/index/upgradeUserLevel').then(res=>{
                  that.$msg(res.msg)
                  if(res.code == 1){
                    that.$router.replace('/')
                  }
                })
              }
            });
          }
        })


      }
    },
    created() {
      let that = this;
      that.$post('/apiv1/index/upgradeUser').then(res=>{
        if(res.code == 1){
          that.info = res.msg
        }
      })
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
      img{
        width: 17px/$ppr;
        height: 29px/$ppr;
        display: block;
        position: absolute;
        top: 40px/$ppr;
        left: 44px/$ppr;
      }
    }
    .main{
      width: 620px/$ppr;
      height: auto;
      margin: 0 auto;
      padding-top: 47px/$ppr;
      .box1{
        width: 100%;
        height: 74px/$ppr;
        margin-bottom: 36px/$ppr;
        p{
          width: 200px/$ppr;
          height: 74px/$ppr;
          line-height: 74px/$ppr;
          text-align: right;
          padding-right: 20px/$ppr;
          float: left;
          font-size: 30px/$ppr;
          color: #fff;
        }
        input{
          width: 380px/$ppr;
          height: 74px/$ppr;
          padding-left: 20px/$ppr;
          background: #1b285a;
          border: none;
          outline: none;
          display: block;
          float: left;
          border-radius: 10px/$ppr;
          color: #fff;
        }
        select{
          width: 440px/$ppr;
          height: 74px/$ppr;
          padding-left: 20px/$ppr;
          background: #1b285a;
          border: none;
          outline: none;
          display: block;
          float: left;
          border-radius: 10px/$ppr;
          color: #fff;
        }
      }
      .box2{
        width: 100%;
        height: 74px/$ppr;
        margin-bottom: 36px/$ppr;
        position: relative;
        p{
          width: 160px/$ppr;
          height: 74px/$ppr;
          line-height: 74px/$ppr;
          text-align: right;
          padding-right: 20px/$ppr;
          float: left;
          font-size: 30px/$ppr;
          color: #fff;
        }
        input{
          width: 420px/$ppr;
          height: 74px/$ppr;
          padding-left: 20px/$ppr;
          background: #1b285a;
          border: none;
          outline: none;
          display: block;
          float: left;
          border-radius: 10px/$ppr;
          color: #fff;
        }
        span{
          position: absolute;
          top: 14px/$ppr;
          right: 12px/$ppr;
          width: 136px/$ppr;
          height: 46px/$ppr;
          background-color: #008aff;
          border-radius: 10px/$ppr;
          color: #fff;
          text-align: center;
          line-height: 46px/$ppr;
          font-size: 16px/$ppr;
        }
      }
      .send{
        width: 100%;
        height: 90px/$ppr;
        background-color: #008aff;
        border-radius: 10px/$ppr;
        text-align: center;
        line-height: 90px/$ppr;
        font-size: 30px/$ppr;
        color: #fff;
        margin-top: 108px/$ppr;
      }
    }
  }
</style>
