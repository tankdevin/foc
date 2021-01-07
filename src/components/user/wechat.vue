<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      微信绑定
    </div>

    <div class="main">
      <div class="box1">
        <p>微信账号</p>
        <input type="text" placeholder="请输入微信账号" v-model="info.payment">
      </div>

      <div class="box3">
        <p>微信收款码</p>
        <img :src="info.paymentimg" alt="" v-if="info.paymentimg">
        <input type="file" @change="upload">
      </div>

      <div class="box2">
        <p>手机验证码</p>
        <input type="text" placeholder="请输入验证码" v-model="info.code">
        <span @click="sendmsg" v-if="nums == 60">发送验证码</span>
        <span v-else>{{nums}}S重试</span>
      </div>
      <div class="box4">
        *验证码将发送到您注册时绑定的手机号上
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
          payment: "",
          paymentimg: require("../../../static/image/upload1.png"),
          code: "",
          type: 1
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
      sendmsg(){
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
      upload(e) {
        let image = e.target.value;
        let reader = new FileReader();
        let that = this;
        reader.onload = (function (file) {
          return function (e) {
            let bs64 = this.result;
            that.$post('/apiv1/common/fileImage', {
              image: bs64
            }).then(res => {
              if (res.code == 1) {
                that.info.paymentimg = baseNetworkUrl + res.msg.image_url
              }
            })
          };
        })(e.target.files[0]);
        reader.readAsDataURL(e.target.files[0]);
      },
      send(){
        let that = this;
        if(that.info.paymentimg == require("../../../static/image/upload1.png")){
          that.$msg('请上传收款码')
        }else if(that.info.payment == ''){
          that.$msg('请输入微信账号')
        }else if(that.info.code == ''){
          that.$msg('请输入验证码')
        }else{
          that.$post('/apiv1/index/payment',that.info).then(res=>{
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
      this.$post('/apiv1/index/paymentlist',{type: 1}).then(res=>{
        if(res.code == 1){
          if(res.msg){
            this.info.payment = res.msg.payment;
            this.info.paymentimg = res.msg.paymentimg;
          }
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
        margin-top: 30px/$ppr;
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
      .box3{
        width: 100%;
        height: 374px/$ppr;
        margin: 60px/$ppr auto;
        position: relative;
        p{
          width: 100%;
          height: 50px/$ppr;
          text-align: center;
          font-size: 30px/$ppr;
          color: #fff;
        }
        img{
          width: 324px/$ppr;
          height: 324px/$ppr;
          display: block;
          margin: 0 auto;
        }
        input{
          width: 324px/$ppr;
          height: 324px/$ppr;
          position: absolute;
          top: 50px/$ppr;
          left: 50%;
          margin-left: -162px/$ppr;
          z-index: 100;
          opacity: 0;
        }
      }
      .box4{
        width: 100%;
        text-align: right;
        font-size: 16px/$ppr;
        color: #59679b;
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
        margin-top: 68px/$ppr;
      }
    }
  }
</style>
