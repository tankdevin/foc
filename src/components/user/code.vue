<template>
  <div class="content">
    <img src="../../../static/image/icon_back.png" alt="" class="back" @click="back">
      <div class="tops">
        <img src="../../../static/image/code1.png" alt="">
      </div>
    <div class="txt">
      <img src="../../../static/image/code2.png" alt="">
    </div>
    <div class="codes">
        <img :src="url" alt="" @click="downs()"/>
    </div>
    <div class="code_txt">扫一扫 了解更多</div>
  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
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
          back(){
            this.$router.go(-1)
          },
          downs(){
              var alink = document.createElement("a");
              alink.href = this.url;
              alink.download = "pic"; //图片名
              alink.click();
          }
        },
        created() {
          let that = this;
          that.$post('/apiv1/index/qrcode').then(res=>{
            if(res.code == 1){
              that.url = res.msg.qrcodeUrl
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
    background: url("../../../static/image/code_bg.jpg") no-repeat;
    background-size: cover;
    .back{
      width: 17px/$ppr;
      height: 29px/$ppr;
      position: fixed;
      top: 40px/$ppr;
      left: 40px/$ppr;
      z-index: 100;
    }
    .tops{
      width: 100%;
      margin: 0 auto;
      img{
        width: 100%;
      }
    }
    .txt{
      width: 360px/$ppr;
      margin: 0 auto;
      img{
        width: 360px/$ppr;
      }
    }
    .codes{
      width: 200px/$ppr;
      height: 200px/$ppr;
      background: #fff;
      border-radius: 20px/$ppr;
      margin: 0 auto;
      margin-top: 20px/$ppr;
      position: relative;
      img{
        display: block;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        margin: auto;
        width: 180px/$ppr;
        height: 180px/$ppr;
      }
    }
    .code_txt{
      width: 100%;
      padding-top: 20px/$ppr;
      text-align: center;
      font-size: 18px/$ppr;
      color: #fff;
    }
  }
</style>
