<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      我的分享
      <!-- <p @click="codedetail">二维码</p> -->
    </div>
    <div class="zhanwei"></div>
    <div class="top">
      <div>
        <p>团队粉丝</p>
        <p>{{msg.fensi}}</p>
      </div>
      <div>
        <p>直接分享粉丝量</p>
        <p>{{msg.zfensi}}</p>
      </div>
      <div>
        <p>直推正式</p>
        <p>{{msg.zhitui}}</p>
      </div>
      <div>
        <p>直推实名</p>
        <p>{{msg.shi}}</p>
      </div>
    </div>

    <div class="code">
      <div>
        <img :src="info.qrcodeUrl" alt="">
      </div>
      <div>
        邀请链接：
        <p>{{info.linkUrl}}</p>
      </div>
      <div @click="copy">复制</div>
    </div>

    <div class="hehe" @click="seemore">查看更多</div>
    <div class="main">
      <div class="list" v-if="list.length > 0">
        <p>手机号</p>
        <p>等级</p>
        <p>实名</p>
        <p>日期</p>
      </div>
      <div class="list1" v-for="i in list" v-if="list.length > 0">
        <p>{{i.phone}}</p>
        <p>{{i.title}}</p>
        <p v-html="i.is_identity == 1 ? '已实名' : '未实名'">
        <p>{{i.create_at}}</p>
      </div>
      <div v-if="list.length == 0" class="none">暂无数据</div>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              list:[],
              info:'',
              msg:{}
            }
        },
        components: {},
        methods: {
          back(){
            this.$router.go(-1)
          },
          copy(){
            const input = document.createElement('input')
            document.body.appendChild(input)
            input.setAttribute('value',this.info.linkUrl)
            input.select()
            if (document.execCommand('copy')) {
              document.execCommand('copy')
            }
            document.body.removeChild(input)
            this.$msg('复制成功')
          },
          getlist(){
            let that = this;
            that.$post('/apiv1/index/detailed',{max_page_num: 3}).then(res=>{
              if(res.code == 1){
                this.list = res.data
              }
            })
          },
          // codedetail(){
          //   this.$router.push('/codes')
          // },
          seemore(){
            this.$router.push('/sharelist')
          }
        },
        created() {
          let that = this;
          that.$post('/apiv1/index/parameter').then(res=>{
            if(res.code == 1){
              that.msg = res.data;
            }
          })
          that.$post('/apiv1/index/qrcode?time=' + new Date().getTime()).then(res=>{
            if(res.code == 1){
              that.info = res.msg
            }
          })
          that.getlist()
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
      p{
        height: 110px/$ppr;
        line-height: 110px/$ppr;
        font-size: 30px/$ppr;
        color: #fff;
        position: absolute;
        top: 0;
        right: 45px/$ppr;
      }
    }
    .zhanwei{
      width: 100%;
      height: 130px/$ppr;
    }
    .top{
      width: 675px/$ppr;
      height: 155px/$ppr;
      background-color: #1b285a;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      div{
        width: 25%;
        height: 155px/$ppr;
        float: left;
        p:nth-child(1){
          width: 100%;
          height: 90px/$ppr;
          font-size: 20px/$ppr;
          color: #7981a5;
          line-height: 90px/$ppr;
          text-align: center;
        }
        p:nth-child(2){
          width: 100%;
          font-size: 30px/$ppr;
          color: #fff;
          text-align: center;
        }
      }
    }
    .code{
      width: 648px/$ppr;
      height: 615px/$ppr;
      padding-top: 65px/$ppr;
      margin: 0 auto;
      div:nth-child(1){
        width: 324px/$ppr;
        height: 324px/$ppr;
        margin: 0 auto;
        border-radius: 10px/$ppr;
        background: #fff;
        position: relative;
        img{
          width: 310px/$ppr;
          height: 310px/$ppr;
          display: block;
          position: absolute;
          top: 0;
          right: 0;
          bottom: 0;
          left: 0;
          margin: auto;
        }
      }
      div:nth-child(2){
        width: 100%;
        /*height: 105px/$ppr;*/
        line-height: 60px/$ppr;
        text-align: center;
        font-size: 30px/$ppr;
        color: #b2c1ff;
        padding-top: 20px/$ppr;
        p{
          display: inline;
          color: #fff;
          line-height: 60px/$ppr;
        }
      }
      div:nth-child(3){
        width: 350px/$ppr;
        height: 80px/$ppr;
        background-color: #008aff;
        border-radius: 10px/$ppr;
        margin: 0 auto;
        font-size: 30px/$ppr;
        color: #fff;
        text-align: center;
        line-height: 80px/$ppr;
        margin-top: 10px/$ppr;
      }
    }
    .hehe{
      width: 675px/$ppr;
      margin: 0 auto;
      text-align: right;
      color: #fff;
      font-size: 24px/$ppr;
      height: 60px/$ppr;
      line-height: 60px/$ppr;
    }
    .main{
      width: 675px/$ppr;
      margin: 0 auto;
      padding-bottom: 20px/$ppr;
      .none{
        width: 100%;
        padding-top: 100px/$ppr;
        text-align: center;
        color: #fff;
        font-size: 28px/$ppr;
      }
      .list1{
        width: 100%;
        height: 80px/$ppr;
        background: #141f4c;
        font-size: 24px/$ppr;
        color: #fff;
        display: flex;
        p{
          height: 80px/$ppr;
          display:flex;
          flex: 1;
          align-items:center;/*垂直居中*/
          justify-content: center;/*水平居中*/
        }
        div{
          width:80px/$ppr;
          height: 80px/$ppr;
          background: #ff1e1e;
          text-align: center;
          line-height: 80px/$ppr;
          color: #fff;
          font-size: 24px/$ppr;
          animation-duration: 0.1s;
          animation-name: into;
        }
      }
      .list{
        width: 100%;
        height: 80px/$ppr;
        background: #141f4c;
        font-size: 30px/$ppr;
        color: #fff;
        display: flex;
        p{
          height: 80px/$ppr;
          display:flex;
          flex: 1;
          align-items:center;/*垂直居中*/
          justify-content: center;/*水平居中*/
        }
        div{
          width:80px/$ppr;
          height: 80px/$ppr;
          background: #ff1e1e;
          text-align: center;
          line-height: 80px/$ppr;
          color: #fff;
          font-size: 24px/$ppr;
          animation-duration: 0.1s;
          animation-name: into;
        }
      }
    }
  }
</style>
