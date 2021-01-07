<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      更改交易密码
    </div>

    <div class="main">
      <div class="box1">
        <p>新密码</p>
        <input type="password" placeholder="请输入新密码" v-model="info.paypassword">
      </div>
      <div class="box1">
        <p>确认新密码</p>
        <input type="password" placeholder="请输入新密码" v-model="info.repaypassword">
      </div>
      <div class="box2">
        <p>手机验证码</p>
        <input type="text" placeholder="请输入验证码" v-model="info.code">
        <span @click="sendcode" v-if="nums == 60">发送验证码</span>
        <span v-else>{{nums}}S重试</span>
      </div>
      <div class="send" @click="send">确定</div>
    </div>

  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        info:{
          paypassword: '',
          repaypassword: '',
          code: ''
        },
        nums: 60,
        timer: null
      }
    },
    components: {},
    methods: {
      back(){
        this.$router.go(-1)
      },
      sendcode(){
        let that = this;
        if(that.nums == 60){
          this.$post('/apiv1/index/sendMobileMessage').then(res => {
            this.$msg(res.msg.message)
            if(res.code == 1){
              that.timer = setInterval(()=>{
                that.nums--
                if(that.nums == 0){
                  that.nums = 60;
                  clearInterval(that.timer)
                }
              },1000)
            }
          })
        }
      },
      send(){
        let that = this;
        if(that.info.password == ''){
          that.$msg('请输入新密码')
        }else if(that.info.repassword == ''){
          that.$msg('请重复新密码')
        }else if(that.info.password != that.info.repassword){
          that.$msg('两次密码不一致')
        }else{
          that.$post('/apiv1/index/paypassword',that.info).then(res=>{
            localStorage.removeItem('register_send_code_time')
            that.$msg(res.msg)
            if(res.code == 1){
              that.$router.go(-1)
            }
          })
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
          width: 150px/$ppr;
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
        margin-top: 58px/$ppr;
      }
    }
  }
</style>
