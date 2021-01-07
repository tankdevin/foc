<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      转账
      <!-- <span @click="transfer">转账记录</span> -->
    </div>
    <!-- 申请收款码须知 -->

        <div class="main">
            <p class="text">商家名称</p>
            <input class="input" type="text" placeholder="请输入商家名称" v-model="name" readonly>
        </div>

        <div class="pdr">
            <p class="text">OPF数量</p>
            <input class="input" type="number" placeholder="请输入pdr数量" v-model="num">
        </div>

        <div class="pdr">
            <p class="text">交易密码</p>
            <input class="input" type="password" placeholder="请输入交易密码" v-model="paypassword">
        </div>
        <div class="win_btn" @click="close_audit">
            <p>确定</p>
        </div>
        <!-- 审核 -->


    <!-- 收款码 -->




  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        name:'',
        num:'',
        paypassword:''
      }
    },
    components: {},
    methods: {
      transfer(){
          this.$router.push('/zhuanzhangjilu')
      },
      back(){
        this.$router.go(-1)
      },

      paymentRecord(){
          this.$router.push('/pRecord')
      },
      close_audit(){
        let that = this;
        if(that.num == ''){
          that.$msg('请输入数量')
        }else if(that.paypassword == ''){
          that.$msg('请输入交易密码')
        }else{
          that.$post('/apiv1/index/transferMerchat',{
            merchatname : that.name,
            num: that.num,
            paypassword: that.paypassword
          }).then(res=>{
            console.log(res)
            that.$msg(res.msg)
            if(res.code == 1){
              that.$msg(res.msg)
              setTimeout(res=>{
                this.$router.push('/')
              },500)
            }
          })
        }
      }
    },
    created() {
        // 获取二维码参数
        let qrcode = this.$route.query.s;
        // let qrcode ='{"uid":50}';
        qrcode = JSON.parse(decodeURIComponent(qrcode));
        console.log(qrcode)
        this.$post('/apiv1/index/getMerchatInfo',{merchatId:qrcode.uid}).then(res=>{
          console.log(res);
          if(res.code == -4001){
            this.$msg(res.msg)
            setTimeout(res=>{
                this.$router.push('/')
              },500)
          }else if(res.code == 1){
            this.name = res.msg.title
          }
        })

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
    .mask{
      width: 100%;
      height: 100vh;
      z-index: 300;
      position: fixed;
      top: 0;
      left: 0;
      background: #000;
      opacity: 0.5;
    }
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
      span{
          position: fixed;
          top: 0px/$ppr;
          left: 580px/$ppr;

      }
    }
    .main{
        width: 665px/$ppr;
        height: 98px/$ppr;
        margin: 0 auto;
        padding-top: 196px/$ppr;
        .text{
            width: 100%;
            height: 100px/$ppr;
            font-family: PingFang-SC-Regular;
            font-size: 36px/$ppr;
            font-weight: normal;
            font-stretch: normal;
            letter-spacing: 1px/$ppr;
            color: #ffffff;
            margin: 0 auto;
            text-align: center;
        }
        input{
            // line-height: 700px/$ppr;
            width: 441px/$ppr;
            height: 74px/$ppr;
            display:block;
            background-color: #1b285a;
            border-radius: 10px;
            text-align: center;
            color:#fff;
            margin: 0 auto;
            border: none;
            outline: none;
        }
    }
    .pdr{
        width: 665px/$ppr;
        height: 98px/$ppr;
        margin: 0 auto;

        margin-top: 196px/$ppr;
        .text{
            width: 100%;
            height: 100px/$ppr;
            font-family: PingFang-SC-Regular;
            font-size: 36px/$ppr;
            font-weight: normal;
            font-stretch: normal;
            letter-spacing: 1px/$ppr;
            color: #ffffff;
            margin: 0 auto;
            text-align: center;
        }
        .input{
            // line-height: 700px/$ppr;
            width: 441px/$ppr;
            height: 74px/$ppr;
            display:block;
            background-color: #1b285a;
            border-radius: 10px;
            text-align: center;
            color:#fff;
            margin: 0 auto;
        }
    }
    .password{
        width: 665px/$ppr;
        height: 98px/$ppr;
        margin: 0 auto;
        padding-top: 196px/$ppr;

        .text{
            width: 100%;
            height: 100px/$ppr;
            font-family: PingFang-SC-Regular;
            font-size: 36px/$ppr;
            font-weight: normal;
            font-stretch: normal;
            letter-spacing: 1px/$ppr;
            color: #ffffff;
            margin: 0 auto;
            text-align: center;
        }
        .input{
            // line-height: 700px/$ppr;
            width: 441px/$ppr;
            height: 74px/$ppr;
            display:block;
            background-color: #1b285a;
            border-radius: 10px;
            text-align: center;
            color:#fff;
            margin: 0 auto;
        }
    }
    .win_btn{
            width: 550px/$ppr;
            height: 84px/$ppr;
            background-color: #008aff;
            border-radius: 10px/$ppr;
            margin:0 auto;
            margin-top:200px/$ppr;
            p{
                width: 130px/$ppr;
                height: 23px/$ppr;
                font-family: PingFang-SC-Medium;
                font-size: 24px/$ppr;
                font-weight: normal;
                font-stretch: normal;
                line-height: 80px/$ppr;
                letter-spacing: 2px/$ppr;
                color: #ffffff;
                margin:0 auto;
                text-align: center;
            }
        }
    .close{
      width: 441px/$ppr;
      height: 90px/$ppr;
      background-color: #008aff;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      margin-top: 400px/$ppr;
      margin-bottom: 305px/$ppr;
      text-align: center;
      line-height: 90px/$ppr;
      font-size: 30px/$ppr;
      color: #fff;
    }
    .erweima{
        width: 644px/$ppr;
        height: 656px/$ppr;
        background-color: #008aff;
        border-radius: 10px/$ppr;
        margin:0 auto;
        position: fixed;
        top: 150px/$ppr;
        left: 60px/$ppr;
        text-align: center;
        .money{
            width: 79px/$ppr;
            height: 67px/$ppr;
            margin:0 auto;
            margin-top:55px/$ppr;
        }
        .erweima_span{
            width: 275px/$ppr;
            height: 23px/$ppr;
            font-family: PingFang-SC-Regular;
            font-size: 24px/$ppr;
            font-weight: normal;
            font-stretch: normal;
            line-height: 42px/$ppr;
            letter-spacing: 1px/$ppr;
            color: #ffffff;
            padding-top:66px/$ppr;
            margin: 0 auto;
        }
        p{
            width: 240px/$ppr;
            height: 23px/$ppr;
            font-family: PingFang-SC-Regular;
            font-size: 24px/$ppr;
            font-weight: normal;
            font-stretch: normal;
            line-height: 42px/$ppr;
            letter-spacing: 1px/$ppr;
            color: #ffffff;
            margin:0 auto;
            margin-top:42px/$ppr;
        }
        .fukuanma{
            width: 259px/$ppr;
	        height: 259px/$ppr;
            margin:0 auto;
            margin-top:42px/$ppr;

        }

    }
    .collection{
        width: 641px/$ppr;
        height: 90px/$ppr;
        background-color: #008aff;
        border-radius: 10px/$ppr;
        position: fixed;
        top: 839px/$ppr;
        left: 60px/$ppr;
        .zhangben{
            width: 40px/$ppr;
	        height: 45px/$ppr;
            margin: 22px/$ppr 0 23px/$ppr 30px/$ppr;
        }
        p{
            width: 140px/$ppr;
            height: 29px/$ppr;
            font-family: PingFang-SC-Regular;
            font-size: 30px/$ppr;
            font-weight: normal;
            font-stretch: normal;
            line-height: 42px/$ppr;
            letter-spacing: 1px/$ppr;
            color: #ffffff;
            position: relative;
            top: -70px/$ppr;
            left: 120px/$ppr;
        }
        .jiantou{
            float:right;
            width: 16px/$ppr;
	        height: 28px/$ppr;
            position: relative;
            top: -80px/$ppr;
            left: -20px/$ppr;
        }
    }
  }
</style>
