<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      收款码
      <!-- <span @click="transfer">转账</span> -->
    </div>
    <!-- 申请收款码须知 -->
    <div>
        <div class="main" v-if="status == -1 && boon == true">
            <p class="text">商家名称</p>
            <input class="input" type="text" placeholder="请输入商家名称" v-model="vendor_name">
        </div>
        <div class="close" @click="sure"  v-if="status == -1 && boon == true">确定</div>
        <div class="mask" v-if="status == -1 && sm_code == true"></div>
        <div class="txt_sure" v-if="status == -1 && sm_code == true">
            <div class="sure_title">《申请收款二维码须知》</div>
            <div class="sure_div">
                <p class="sure_text">商家应用场景的意义就是创造更多的流通应用场景，把OPF利用起来，有价值的存在就通过流通来体现，黄金和钞票也是同理，大家都认同了就有了流通，而数字资产自带流通属性，我们只要通过达成认同价值的属性就可以让数字资产成为真正的流通资产。这就是应用场景给数字资产带来的价值</p>
            </div>
            <div class="win_btn" @click="close_sure()"><p>同意并了解</p></div>
        </div>
        <!-- 审核 -->
        <div class="mask" v-if="status == 2 && shenhe == true"></div>
        <div v-if="status == 2  && shenhe == true">
            <div class="txt_audit">
                <div class="audit_p">
                    <p><img src="../../../static/image/men.png" alt=""></p>
                    <div class="text_p">
                        <p>审核中...</p>
                        <p>请耐心等待</p>
                    </div>    
                </div>
                <div class="win_btn1" @click="close_audit">
                    确定
                </div>
            </div>
        </div>
    </div>
    <!-- 收款码 -->
    
    <div v-if="status == 1 && shoukuan == true">
        <div class="erweima">
            <img class="money" src="../../../static/image/money.png" alt="">
            <p>扫描二维码 向我付款</p>
            <img class="fukuanma" :src="url"  alt="">
            <div class="erweima_span">
                <span @click="downs">保存收款码</span>
            </div>
        </div>
        <div class="collection"  @click="paymentRecord">
            <img class="zhangben" src="../../../static/image/zhangben.png" alt="">
            <p>收款账本</p>
            <img class="jiantou" src="../../../static/image/jiantou.png" alt="">
        </div>
    </div>
    
        
  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        vendor_name:'',//商家名称
        status:'',
        sm_code: false,
        shenhe: false,
        shoukuan: false,
        url:''
      }
    },
    components: {},
    methods: {
      downloadIamge(imgsrc, name) {//下载图片地址和图片名
            var image = new Image();
            // 解决跨域 Canvas 污染问题
            image.setAttribute("crossOrigin", "anonymous");
            image.onload = function() {
              var canvas = document.createElement("canvas");
              canvas.width = image.width;
              canvas.height = image.height;
              var context = canvas.getContext("2d");
              context.drawImage(image, 0, 0, image.width, image.height);
              var url = canvas.toDataURL("image/png"); //得到图片的base64编码数据

              var a = document.createElement("a"); // 生成一个a元素
              var event = new MouseEvent("click"); // 创建一个单击事件
              a.download = name || "photo"; // 设置图片名称
              a.href = url; // 将生成的URL设置为a.href属性
              a.dispatchEvent(event); // 触发a的单击事件
            };
            image.src = imgsrc;
          },
          downs(){
              var alink = document.createElement("a");
              alink.href = this.url;
              alink.download = "pic"; //图片名
              alink.click();
          },
      
      back(){
        this.$router.go(-1)
      },
      sure(){
          let that = this;
          if(that.vendor_name == ''){
              that.$msg('请输入商家名称')
          }
          that.$post('/apiv1/index/applyMerchant',{merchatname: that.vendor_name}).then(res=>{
              if(res.code == 1){
                  this.getinfo()
              }else if(res.code == 0){
                  this.$msg(res.msg)
              }
          })
      },
      close_sure(){
          this.sm_code = false;
          this.boon = true;
          console.info(this.boon)
          
      },
      close_audit(){
          this.$router.push("/")
      },
      paymentRecord(){
          this.$router.push('/pRecord')
      },
      getinfo(){
        this.$post('/apiv1/index/generateMerchantQrcode',).then(res=>{
                console.info(res.code)
                this.status = res.code
                if(res.code == 1){
                    console.log(res)
                    this.shoukuan = true;
                    this.url = res.data.qrcodeUrl;
                }else if(res.code == -1){
                    this.sm_code = true;
                }else if(res.code == 2){
                    this.shenhe = true;
                }
            })
      }
    },
    created() {
      this.$post('/apiv1/index/notice',{type: 'about'}).then(res=>{
        if(res.code == 1){
          this.content = res.msg[0].content
        }
      })
      this.getinfo()
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
    //申请须知
    .txt_sure{
        width: 550px/$ppr;
        height: 580px/$ppr;
        background-color: #ffffff;
        border-radius: 10px/$ppr;
        position:fixed;
        top:0;
        right:0;
        bottom:0;
        left:0;
        margin:auto;
        z-index:310;
        .sure_title{
            width: 440px/$ppr;
            height: 34px/$ppr;
            font-family: PingFang-SC-Medium;
            font-size: 36px/$ppr;
            font-weight: normal;
            font-stretch: normal;
            line-height: 42px/$ppr;
            letter-spacing: 1px/$ppr;
            color: #000000;
            margin:0 auto;
            margin-top:54px/$ppr;
        }
        .sure_div{
            width: 466px/$ppr;
            height: 338px/$ppr;
            margin:0 auto;
            .sure_text{
                font-family: PingFang-SC-Medium;
                font-size: 24px/$ppr;
                font-weight: normal;
                letter-spacing: 2px/$ppr;
                color: #666666;
                margin-top:74px/$ppr;
            }
        }
        
        .win_btn{
            width: 550px/$ppr;
            height: 84px/$ppr;
            background-color: #008aff;
            border-bottom-right-radius:10px/$ppr;
            border-bottom-left-radius:10px/$ppr;
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
            }
        }
    }
    //审核
    .txt_audit{
        width: 550px/$ppr;
        height: 308px/$ppr;
        background-color: #ffffff;
        border-radius: 10px/$ppr;
        position:fixed;
        top:0;
        right:0;
        bottom:0;
        left:0;
        margin:auto;
        z-index:350;
        .audit_p{
            display:flex;
            // margin:0 auto;
            justify-content:space-around;
            img{
                width: 54px/$ppr;
	            height: 162px/$ppr;
                margin-top:31px/$ppr;
            }
            .text_p{
                display:flex;
                flex-direction:column;
                text-align: center;
                font-family: PingFang-SC-Medium;
                font-size: 36px/$ppr;
                font-weight: normal;
                font-stretch: normal;
                margin-top:59px/$ppr;
            }
            .audit_text{
                width: 190px/$ppr;
                height: 99px/$ppr;
                font-family: PingFang-SC-Medium;
                font-size: 36px/$ppr;
                font-weight: normal;
                font-stretch: normal;
                line-height: 65px/$ppr;
                letter-spacing: 3px/$ppr;
                color: #000000;
                margin-top:59px/$ppr;
            }
        }
        .win_btn1{
            width: 550px/$ppr;
            height: 84px/$ppr;
            background-color: #008aff;
            border-bottom-right-radius:10px/$ppr;
            border-bottom-left-radius:10px/$ppr;
            text-align: center;
            line-height: 84px/$ppr;
            font-size: 24px/$ppr;
            color: #fff;
            margin-top: 35px/$ppr;
        }
        .win_btn{
            width: 550px/$ppr;
            height: 84px/$ppr;
            background-color: #008aff;
            border-bottom-right-radius:10px/$ppr;
            border-bottom-left-radius:10px/$ppr;
            margin-top:31px/$ppr;
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
            }
        }
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
          left: 650px/$ppr;
          
      }
    }
    .main{
        width: 665px/$ppr;
        height: 98px/$ppr;
        margin: 0 auto;
        padding-top: 376px/$ppr;        
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
            padding-top:20px/$ppr;
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
            width: 350px/$ppr;
	        height: 350px/$ppr;
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
