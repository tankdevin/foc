<template>
  <div class="content">
    <div class="top">
      <div @click="back">
        <img src="../../../static/image/left.png" alt="">
      </div>
      <h1>订单申诉</h1>
    </div>

    <div class="main">
      <div class="box1">
        <div class="box1_title">
          <img src="../../../static/image/xx1.png" alt="">
          <p>投诉内容</p>
        </div>
        <div class="box1_main">
          <textarea name="" id="" cols="30" rows="10" placeholder="请输入不少于10个字的描述" v-model="content" @input="oin"></textarea>
          <p>{{content.length}}/200</p>
        </div>
      </div>
      <div class="box2">
        <div class="box2_title">
          <img src="../../../static/image/xx2.png" alt="">
          <p>投诉截图<span>(选填，最多3张)</span></p>
        </div>
        <div class="box2_main">
          <div v-if="imgs.length > 0" v-for="i in imgs">
            <img :src="i" alt="">
          </div>
          <div v-if="imgs.length < 3">
            <input type="file" @change="upload">
            <img src="../../../static/image/xx3.png" alt="">
          </div>
        </div>
      </div>
    </div>

    <div class="send" @click="send">提交反馈</div>

  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        id:'',
        content:'',
        imgs:[]
      }
    },
    components: {},
    methods: {
      back() {
        this.$router.go(-1)
      },
      send(){
        this.$post('/apiv1/index/complaint', {
          orderId: this.id,
          content: this.content,
          imagePath: this.imgs
        }).then(res => {
          this.$msg(res.msg)
          if (res.code == 1) {
            this.$router.replace('/')
          }
        })
      },
      oin(){
        if(this.content.length >= 200){
          this.content.length = 200;
        }
      },
      upload(e){
        let that = this;
        let image = e.target.value;
        let reader = new FileReader();
        reader.onload = (function(file) {
          return function(e) {
            let bs64 = this.result;
            that.$post('/apiv1/common/fileImage', {
              image: bs64,
            }).then(res => {
              if (res.code == 1) {
                // that.$msg(res.msg)
                that.imgs.push(baseNetworkUrl + res.msg.image_url)
              }
            })
          };
        })(e.target.files[0]);
        reader.readAsDataURL(e.target.files[0]);
      }
    },
    created() {
      this.id = this.$route.query.id;
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
    background: #10143a;
    .main{
      width: 680px/$ppr;
      height: auto;
      padding-top: 120px/$ppr;
      margin: 0 auto;
      .box1{
        width: 680px/$ppr;
        height: 363px/$ppr;
        background-color: #ffffff;
        border-radius: 10px/$ppr;
        margin: 0 auto;
        box-shadow:4px/$ppr 4px/$ppr 4px/$ppr #C2C2C2, 2px 2px 5px #999;
        .box1_title{
          width: 628px/$ppr;
          height: 88px/$ppr;
          margin: 0 auto;
          img{
            width: 31px/$ppr;
            height: 31px/$ppr;
            display: block;
            float: left;
            margin: 26px/$ppr 16px/$ppr 0 0;
          }
          p{
            float: left;
            line-height: 88px/$ppr;
            font-size: 26px/$ppr;

          }
        }
        .box1_main{
          width: 628px/$ppr;
          height: 254px/$ppr;
          background-color: #ffffff;
          border-radius: 10px/$ppr;
          border: solid 1px #d0d0d0;
          margin: 0 auto;
          textarea{
            width: 588px/$ppr;
            height: 180px/$ppr;
            padding: 20px/$ppr 20px/$ppr 0 20px/$ppr;
            border: none;
            outline: none;
            border-radius: 20px/$ppr;
          }
          p{
            width: 588px/$ppr;
            text-align: right;
            font-size: 28px/$ppr;
          }
        }
      }
      .box2{
        width: 680px/$ppr;
        background-color: #ffffff;
        border-radius: 10px/$ppr;
        margin: 0 auto;
        box-shadow:4px/$ppr 4px/$ppr 4px/$ppr #C2C2C2, 2px 2px 5px #999;
        margin-top: 32px/$ppr;
        .box2_title{
          width: 628px/$ppr;
          height: 88px/$ppr;
          margin: 0 auto;
          img{
            width: 31px/$ppr;
            height: 31px/$ppr;
            display: block;
            float: left;
            margin: 26px/$ppr 16px/$ppr 0 0;
          }
          p{
            float: left;
            line-height: 88px/$ppr;
            font-size: 26px/$ppr;

          }
        }
        .box2_main{
          width: 628px/$ppr;
          height: 185px/$ppr;
          margin: 0 auto;
          div{
            width: 154px/$ppr;
            height: 154px/$ppr;
            border-radius: 10px/$ppr;
            display: inline-block;
            margin-right: 20px/$ppr;
            position: relative;
            input{
              width: 154px/$ppr;
              height: 154px/$ppr;
              display: block;
              position: absolute;
              top: 0;
              left: 0;
              opacity: 0;
            }
            img{
              width: 154px/$ppr;
              height: 154px/$ppr;
            }
          }
        }
      }
    }
    .send{
      width: 680px/$ppr;
      height: 84px/$ppr;
      background-color: #008aff;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      line-height: 84px/$ppr;
      text-align: center;
      font-size: 26px/$ppr;
      color: #fff;
      margin-top: 80px/$ppr;
    }
    .top {
      width: 100%;
      height: 85px/$ppr;
      line-height: 85px/$ppr;
      position: fixed;
      left: 0;
      top: 0;
      z-index: 1000;

      > div {
        width: 20%;
        height: 85px/$ppr;
        position: absolute;
        top: 0;
        left: 0;
      }

      h1 {
        font-size: 36px/$ppr;
        color: #ffffff;
        text-align: center;
        font-weight: 600;
      }

      img {
        width: 15px/$ppr;
        height: 26px/$ppr;
        position: absolute;
        left: 20px/$ppr;
        top: 25px/$ppr;
      }
    }
  }

</style>
