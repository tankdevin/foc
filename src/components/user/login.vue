<template>
  <div class="content">
    <div class="zhanwei"></div>
    <img src="../../../static/image/login_logo.png" alt="" class="logo">

    <div class="main">
      <div class="box">
        <div>
          <img src="../../../static/image/login1.png" alt="" class="img1">
        </div>
        <input type="text" placeholder="请输入手机号码" v-model="info.phone">
      </div>
      <div class="box">
        <div>
          <img src="../../../static/image/login2.png" alt="" class="img2">
        </div>
        <input type="password" placeholder="请输入密码" v-model="info.password">
      </div>
      <div class="box">
        <div>
          <img src="../../../static/image/login3.png" alt="" class="img3">
        </div>
        <input type="text" placeholder="请输入验证码" v-model="info.yzm">
        <img :src="url" alt="" class="img4" @click="check">
      </div>

      <div class="send" @click="login">登陆</div>
      <div class="pass" @click="findpass">
        <p></p>
        <p>找回密码</p>
      </div>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              url:'',
              info:{
                phone:'',
                password:'',
                yzm:''
              },
              num: 1
            }
        },
        components: {},
        methods: {
          findpass(){
            this.$router.push('/findpass')
          },
          check(){
            this.num++
            this.url = baseNetworkUrl + '/captcha?num=' + this.num;
          },
          login(){
            let that = this;
            if(that.info.phone == ''){
              that.$msg('请输入用户名')
            }else if(that.info.password == ''){
              that.$msg('请输入密码')
            }else if(that.info.yzm == ''){
              that.$msg('请输入验证码')
            }else{
              that.$post('/apiv1/login/login',that.info).then(res=>{
                that.$msg(res.msg)
                if(res.code == 1){
                  localStorage.setItem('token',res.data.token);
                  localStorage.setItem('close_banner',0);
                  localStorage.setItem('is_login',1);
                  //console.log(close_banner)
                  //全局请求用户数据
                  that.$post('/apiv1/index/profile').then(res => {
                    if(res.code == 1){
                      localStorage.setItem('profile', JSON.stringify(res.msg));
                      setTimeout(()=>{
                        that.$router.replace('/')
                      },1000)
                    }
                  }) 
                }else{
                  this.url = baseNetworkUrl + '/captcha?num=' + this.num;
                }
              })
            }
          },
          register(){
            this.$router.push('/register')
          }
        },
        created() {
          this.url = baseNetworkUrl + '/captcha';
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
    .zhanwei{
      width: 100%;
      height: 222px/$ppr;
    }
    .logo{
      width: 331px/$ppr;
      height: 57px/$ppr;
      display: block;
      margin: 0 auto;
    }
    .main{
      width: 560px/$ppr;
      margin: 0 auto;
      padding-top: 270px/$ppr;
      .box{
        width: 100%;
        height: 109px/$ppr;
        border-bottom: 1px solid #192b6c;
        position: relative;
        div{
          width: 78px/$ppr;
          height: 109px/$ppr;
          float: left;
          position: relative;
          img{
            display: block;
            position: absolute;
            left: 50%;
            top: 53px/$ppr;
          }
          .img1{
            width: 33px/$ppr;
            height: 37px/$ppr;
            margin-left: -15.5px/$ppr;
          }
          .img2{
            width: 36px/$ppr;
            height: 36px/$ppr;
            margin-left: -18px/$ppr;
          }
          .img3{
            width: 37px/$ppr;
            height: 39px/$ppr;
            margin-left: -18.5px/$ppr;
          }
        }
        input{
          display: block;
          float: left;
          width: 450px/$ppr;
          height: 70px/$ppr;
          margin-top: 39px/$ppr;
          border: none;
          outline: none;
          background: #0d1637;
          color: #fff;
        }
        .img4{
          width: 150px/$ppr;
          height: 70px/$ppr;
          position: absolute;
          top: 39px/$ppr;
          right: 20px/$ppr;
          background: #fff;
        }
      }
      .send{
        width: 100%;
        height: 80px/$ppr;
        text-align: center;
        line-height: 80px/$ppr;
        background: #008aff;
        font-size: 28px/$ppr;
        color: #fff;
        margin-top: 95px/$ppr;
        border-radius: 10px/$ppr;
      }
      .pass{
        width: 100%;
        padding-top: 44px/$ppr;
        font-size: 22px/$ppr;
        color: #fff;
        p:nth-child(1){
          float: left;
        }
        p:nth-child(2){
          float: right;
        }
      }
    }
  }
</style>
