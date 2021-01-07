<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      实名认证
    </div>

    <div class="main">
      <div class="box1">
        <p>证件类型</p>
        <li>身份证</li>
      </div>
      <div class="box1">
        <p>真实姓名</p>
        <input type="text" placeholder="请输入真实姓名" v-model="post.truename">
      </div>
      <div class="box1">
        <p>证件号码</p>
        <input type="text" placeholder="请输入证件号码" v-model="post.idcard">
      </div>
      <div class="box2">
        <img :src="post.zidcardimg" alt="">
        <input type="file" @change="upload1">
      </div>
      <div class="box2">
        <img :src="post.fidcardimg" alt="">
        <input type="file" @change="upload2">
      </div>
      <!--<div class="box2">-->
        <!--<img :src="post.sidcardimg" alt="">-->
        <!--<input type="file" @change="upload3">-->
      <!--</div>-->
      <div class="send" @click="send">提交验证</div>
    </div>

  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              post:{
                truename:'',
                idcard:'',
                zidcardimg: require('../../../static/image/card1.png'),//正面照
                fidcardimg: require('../../../static/image/card2.png'),//反面
                // sidcardimg:'../../../static/image/card3.png',//手持
              }
            }
        },
        components: {},
        methods: {
          back(){
            this.$router.go(-1)
          },
          send(){
            let that = this;
            if(that.post.truename == ''){
              that.$msg('请输入真实姓名')
            }else if(that.post.idcard == ''){
              that.$msg('请输入身份证号')
            }else if(that.post.zidcardimg == require('../../../static/image/card1.png')){
              that.$msg('请上传身份证正面照')
            }else if(that.post.fidcardimg == require('../../../static/image/card2.png')){
              that.$msg('请上传身份证背面照')
            }else{
              that.$post('/apiv1/index/idcard',that.post).then(res=>{
                that.$msg(res.msg)
                if(res.code == 1){
                  setTimeout(()=>{
                    this.$router.go(-1)
                  },1000)
                }
              })
            }
          },
          upload1(e){
            let image = e.target.value;
            let reader = new FileReader();
            let that = this;
            reader.onload = (function (file) {
              layer.open({
                type: 2,
                shadeClose: false,
                content:'图片上传中...'
              });
              return function (e) {
                let bs64 = this.result;
                that.$post('/apiv1/common/fileImage', {
                  image: bs64
                }).then(res => {
                  if (res.code == 1) {
                    that.post.zidcardimg = baseNetworkUrl + '/' + res.msg.image_url;
                    //关闭所有层
                    layer.closeAll()
                  }
                })
              };
            })(e.target.files[0]);
            reader.readAsDataURL(e.target.files[0]);
          },
          upload2(e){
            let image = e.target.value;
            let reader = new FileReader();
            let that = this;
            reader.onload = (function (file) {
              layer.open({
                type: 2,
                shadeClose: false,
                content:'图片上传中...'
              });
              return function (e) {
                let bs64 = this.result;
                that.$post('/apiv1/common/fileImage', {
                  image: bs64
                }).then(res => {
                  if (res.code == 1) {
                    that.post.fidcardimg = baseNetworkUrl + '/' + res.msg.image_url;
                    layer.closeAll()
                  }
                })
              };
            })(e.target.files[0]);
            reader.readAsDataURL(e.target.files[0]);
          },
          // upload3(e){
          //   let image = e.target.value;
          //   let reader = new FileReader();
          //   let that = this;
          //   reader.onload = (function (file) {
          //     layer.open({
          //       type: 2,
          //       shadeClose: false,
          //       content:'图片上传中...'
          //     });
          //     return function (e) {
          //       let bs64 = this.result;
          //       that.$post('/apiv1/common/fileImage', {
          //         image: bs64
          //       }).then(res => {
          //         if (res.code == 1) {
          //           that.post.sidcardimg = baseNetworkUrl + '/' + res.msg.image_url;
          //           layer.closeAll()
          //         }
          //       })
          //     };
          //   })(e.target.files[0]);
          //   reader.readAsDataURL(e.target.files[0]);
          // }
        },
        created() {
          this.$post('/apiv1/index/idcardlist ').then(res=>{
            if(res.code == 1){
              this.post.truename = res.msg.truename
              this.post.idcard = res.msg.idcard
              this.post.zidcardimg = res.msg.zidcardimg
              this.post.fidcardimg = res.msg.fidcardimg
              this.post.sidcardimg = res.msg.sidcardimg
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
    color: #999;
  }
  .content{
    width: 100%;
    min-height: 100vh;
    height: 100%;
    background: #e2e7eb;
    .head{
      width: 100%;
      height: 110px/$ppr;
      position: relative;
      text-align: center;
      line-height: 110px/$ppr;
      font-size: 36px/$ppr;
      color: #fff;
      background: #0d1637;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 100;
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
      width: 100%;
      height: auto;
      padding-top: 110px/$ppr;
      padding-bottom: 34px/$ppr;
      background: #e2e7eb;
      .box1{
        width: 100%;
        height: 104px/$ppr;
        background: #fff;
        margin-bottom: 10px/$ppr;
        p{
          width: 20%;
          height: 104px/$ppr;
          line-height: 104px/$ppr;
          font-size: 28px/$ppr;
          color: #000;
          text-align: right;
          float: left;
          margin-right: 8%;
        }
        li{
          float: left;
          line-height: 104px/$ppr;
          font-size: 28px/$ppr;
          color: #000;
        }
        input{
          display: block;
          float: left;

          width: 68%;
          height: 104px/$ppr;
        }
      }
      .box2{
        width: 100%;
        height: 365px/$ppr;
        padding-top: 19px/$ppr;
        background: #fff;
        margin-bottom: 10px/$ppr;
        position: relative;
        img{
          display: block;
          width: 516px/$ppr;
          height: 326px/$ppr;
          position: absolute;
          top: 0;
          right: 0;
          bottom: 0;
          left: 0;
          margin: auto;
          z-index: 10;
        }
        input{
          display: block;
          width: 516px/$ppr;
          height: 326px/$ppr;
          position: absolute;
          top: 0;
          right: 0;
          bottom: 0;
          left: 0;
          margin: auto;
          z-index: 20;
          opacity: 0;
        }
      }
      .send{
        width: 694px/$ppr;
        height: 105px/$ppr;
        background-color: #008aff;
        border-radius: 10px/$ppr;
        margin: 0 auto;
        margin-top: 20px/$ppr;
        text-align: center;
        line-height: 105px/$ppr;
        font-size: 28px/$ppr;
        color: #fff;
      }
    }
  }
</style>
