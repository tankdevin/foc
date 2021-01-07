<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
    </div>
    <div class="txt1">{{info.interval_info}}订单已完成</div>

    <div class="main">
      <div class="box1">
        <p>订单金额</p>
        <p style="color: #0495ff;">{{info.real_price*1}}</p>
      </div>
      <div class="box1">
        <p>交易单价</p>
        <p>{{info.real_price/info.num}}</p>
      </div>
      <div class="box1">
        <p>交易数量</p>
        <p>{{info.num*1}}</p>
      </div>
      <div class="box1">
        <p>订单编号</p>
        <p>{{info.order_no}}</p>
      </div>
      <div class="box1">
        <p>截止时间</p>
        <p>{{$times(info.end_time)}}</p>
      </div>
      <div class="box2">
        <p>联系电话</p>
        <p><a :href="'tel:' + info.phone">联系</a></p>
        <p>{{info.phone}}</p>
      </div>
      <div class="box4">
        <p>付款凭证</p>
        <p>
          <img :src="info.dakuan_image" alt="" @click="see_img">
        </p>
      </div>
    </div>

    <div class="imgmask" v-if="imgs_boon" @click="close_imgs">
      <img :src="info.dakuan_image" alt="">
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        id:'',
        info:{},
        imgs_boon: false
      }
    },
    components: {},
    methods: {
      back(){
        this.$router.go(-1)
      },
      see_img(){
        this.imgs_boon = true;
      },
      close_imgs(){
        this.imgs_boon = false;
      },
    },
    created() {
      let that = this;
      that.id = that.$route.query.id;
      that.$post('/apiv1/index/getC2cOrderWaitDetail',{orderId: that.id}).then(res=>{
        if(res.code == 1){
          that.info = res.data.order_info
          // console.log(res.data.order_info)
        }
      })
    },
    destroyed() {

    }
  }
</script>

<style lang="scss" scoped>
  @import "../../assets/scss/mixin";
  .imgmask{
    width: 100%;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 10000;
    background: #000;
    img{
      width: 100%;
      position: fixed;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      margin: auto;
      z-index: 10001;
    }
  }
  .content{
    width: 100%;
    min-height: 100vh;
    height: 100%;
    background: #0d1637;
    .head{
      width: 100%;
      height: 110px/$ppr;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 500;
      img{
        width: 17px/$ppr;
        height: 29px/$ppr;
        display: block;
        position: fixed;
        top: 40px/$ppr;
        left: 44px/$ppr;
      }
    }
    .txt1{
      width: 100%;
      height: 260px/$ppr;
      line-height: 260px/$ppr;
      padding-top: 110px/$ppr;
      text-align: center;
      font-size: 36px/$ppr;
      color: #8084a6;
    }
    .main{
      width: 683px/$ppr;
      height: 782px/$ppr;
      background-color: #151e47;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      margin-top: 50px/$ppr;
      .box1{
        width: 100%;
        height: 90px/$ppr;
        line-height: 90px/$ppr;
        font-size: 26px/$ppr;
        color: #fff;
        p:nth-child(1){
          float: left;
          margin-left: 40px/$ppr;
        }
        p:nth-child(2){
          float: right;
          margin-right: 40px/$ppr;
        }
      }
      .box2{
        width: 100%;
        height: 90px/$ppr;
        line-height: 90px/$ppr;
        font-size: 26px/$ppr;
        color: #fff;
        p:nth-child(1){
          float: left;
          margin-left: 40px/$ppr;
        }
        p:nth-child(2){
          float: right;
          margin-right: 40px/$ppr;
          width: 116px/$ppr;
          height: 47px/$ppr;
          background-color: #0495ff;
          border-radius: 10px/$ppr;
          margin-top: 20px/$ppr;
          text-align: center;
          line-height: 47px/$ppr;
        }
        p:nth-child(3){
          float: right;
          margin-right: 20px/$ppr;
        }
      }
      .box4{
        width: 100%;
        height: 220px/$ppr;
        line-height: 220px/$ppr;
        font-size: 26px/$ppr;
        color: #fff;
        p:nth-child(1){
          float: left;
          margin-left: 40px/$ppr;
        }
        p:nth-child(2){
          float: right;
          margin-right: 40px/$ppr;
          width: 220px/$ppr;
          height: 220px/$ppr;
          background-color: #fff;
          border-radius: 10px/$ppr;
          img{
            width: 220px/$ppr;
            height: 220px/$ppr;
          }
        }

      }
    }
  }
</style>
