<template>
  <div class="content">
    <div class="head">
      <div class="headbox">
        <div>
          <img src="../../../static/image/user1.png" alt="">
        </div>
        <div>
          <p>{{info.nickname}}</p>
          <p>{{info.title}}</p>
          <p v-if="info.credit == 5">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
          </p>
          <p v-if="info.credit == 4">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
          </p>
          <p v-if="info.credit == 3">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
          </p>
          <p v-if="info.credit == 2">
            <img src="../../../static/image/xing.png" alt="">
            <img src="../../../static/image/xing.png" alt="">
          </p>
          <p v-if="info.credit == 1">
            <img src="../../../static/image/xing.png" alt="">
          </p>
        </div>
      </div>
    </div>

    <div class="main">
      <div class="list" v-for="i in nav" @click="to(i.route,i.name)">
        <div><img :src="i.url" :class="i.class" alt=""></div>
        <div>{{i.name}}</div>
        <div><img src="../../../static/image/icon14.png" alt=""></div>
      </div>
    </div>

    <div class="close" @click="close">安全退出</div>

  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              nav:[
                {url:require('../../../static/image/icon1.png'),name:'社区升级',class:'img1',route:'/shengji'},
                {url:require('../../../static/image/icon2.png'),name:'我的分享',class:'img2',route:'/share'},
                {url:require('../../../static/image/icon3.png'),name:'商城',class:'img3',route:'/shangcheng'},
                {url:require('../../../static/image/icon4.png'),name:'收入记录',class:'img4',route:'/shouyi'},
                {url:require('../../../static/image/icon11.png'),name:'分享来源',class:'img11',route:'/zhinan'},
                {url:require('../../../static/image/icon5.png'),name:'实名认证',class:'img5',route:'/shiming'},
                {url:require('../../../static/image/icon6.png'),name:'收款管理',class:'img6',route:'/shoukuan'},
                {url:require('../../../static/image/icon7_.png'),name:'在线充值',class:'img7',route:'/chongzhi'},
                {url:require('../../../static/image/icon7_.png'),name:'我的订单',class:'img7',route:'/dingdan'},
                {url:require('../../../static/image/icon8.png'),name:'登陆密码',class:'img8',route:'/loginpass'},
                {url:require('../../../static/image/icon9.png'),name:'交易密码',class:'img9',route:'/paypass'},
                {url:require('../../../static/image/icon10.png'),name:'常见问题',class:'img10',route:'/wenti'},
                {url:require('../../../static/image/icon12.png'),name:'联系客服',class:'img12',route:'/kefu'},
                {url:require('../../../static/image/icon13.png'),name:'关于我们',class:'img13',route:'/about'},
                {url:require('../../../static/image/icon15.png'),name:'收款码',class:'img15',route:'/payment'},
              ],
              info:{},
            }
        },
        components: {},
        methods: {
          to(url,name){
            let that = this;
            if(name == '实名认证'){
              that.$post('/apiv1/index/getProfileFlag').then(res=>{
                if(res.code == 0){
                  that.$router.push(url)
                }else{
                  layer.open({
                    content: res.msg,
                    btn: '确定',
                    yes:function () {
                      layer.closeAll()
                      if(res.code == 3){
                        that.$router.push(url)
                      }
                    }
                  })
                }
              })
            } else{
              this.$router.push(url)
            }
          },
          close(){
            localStorage.clear()
            this.$router.replace('/login')
          }
        },
        created() {
          this.$post('/apiv1/index/profile').then(res=>{
            if(res.code == 1){
              this.info = res.msg
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
    width: 500px/$ppr;
    height: 100vh;
    overflow-y: auto;
    background: #10143a;
    .head{
      width: 100%;
      height: 240px/$ppr;
      .headbox{
        width: 100%;
        height: 240px/$ppr;
        margin: 0 auto;
        div:nth-child(1){
          width: 114px/$ppr;
          height: 114px/$ppr;
          float: left;
          margin: 40px/$ppr 54px/$ppr 0 50px/$ppr;
          img{
            width: 114px/$ppr;
            height: 114px/$ppr;
          }
        }
        div:nth-child(2){
          float: left;
          height: 240px/$ppr;
          p:nth-child(1){
            height: 102px/$ppr;
            line-height: 130px/$ppr;
            font-size: 30px/$ppr;
            color: #fff;
          }
          p:nth-child(2){
            width: 200px/$ppr;
            height: 48px/$ppr;
            background-color: #fc2154;
            border-radius: 10px/$ppr;
            text-align: center;
            line-height: 48px/$ppr;
            font-size: 24px/$ppr;
            color: #fff;
          }
          p:nth-child(3){
            padding-top: 10px/$ppr;
            img{
              width: 29px/$ppr;
              height: 28px/$ppr;
              display: inline-block;
              margin-right: 15px/$ppr;
            }
          }
        }
      }
    }
    .main{
      width: 100%;
      margin: 0 auto;
      .list:nth-child(1){
        border-top: 1px solid #141948;
      }
      .list{
        width: 100%;
        height: 98px/$ppr;
        border-bottom: 1px solid #141948;
        div{
          float: left;
        }
        div:nth-child(1){
          width: 126px/$ppr;
          height: 98px/$ppr;
          position: relative;
          img{
            display: block;
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            margin: auto;
          }
          .img1{
            width: 42px/$ppr;
            height: 48px/$ppr;
          }
          .img2{
            width: 47px/$ppr;
            height: 47px/$ppr;
          }
          .img3{
            width: 55px/$ppr;
            height: 41px/$ppr;
          }
          .img4{
            width: 39px/$ppr;
            height: 47px/$ppr;
          }
          .img5{
            width: 41px/$ppr;
            height: 50px/$ppr;
          }
          .img6{
            width: 39px/$ppr;
            height: 43px/$ppr;
          }
          .img7{
            width: 42px/$ppr;
            height: 39px/$ppr;
          }
          .img8{
            width: 38px/$ppr;
            height: 44px/$ppr;
          }
          .img9{
            width: 34px/$ppr;
            height: 42px/$ppr;
          }
          .img10{
            width: 40px/$ppr;
            height: 40px/$ppr;
          }
          .img11{
            width: 38px/$ppr;
            height: 33px/$ppr;
          }
          .img12{
            width: 43px/$ppr;
            height: 40px/$ppr;
          }
          .img13{
            width: 40px/$ppr;
            height: 40px/$ppr;
          }
          .img15{
            width: 40px/$ppr;
            height: 40px/$ppr;
          }
        }
        div:nth-child(2){
          width: 300px/$ppr;
          height: 98px/$ppr;
          line-height: 98px/$ppr;
          font-size: 30px/$ppr;
          color: #fff;
        }
        div:nth-child(3){
          width: 11px/$ppr;
          height: 19px/$ppr;
          margin-top: 39px/$ppr;
          img{
            width: 11px/$ppr;
            height: 19px/$ppr;
          }
        }
      }
    }
    .close{
      width: 90%;
      height: 90px/$ppr;
      background-color: #ff3859;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      margin-top: 70px/$ppr;
      margin-bottom: 70px/$ppr;
      text-align: center;
      line-height: 90px/$ppr;
      font-size: 30px/$ppr;
      color: #fff;
    }
  }
</style>
