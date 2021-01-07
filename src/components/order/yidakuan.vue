<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
    </div>
    <div class="txt1">{{info.order_info.interval_info}}订单已打款</div>
    <div class="txt2">交易倒计时</div>
    <div class="txt2">{{hour || '00' }}时{{minute || '00'}}分{{second || '00'}}秒</div>

    <div class="main">
      <div class="box1">
        <p>订单金额</p>
        <p style="color: #0495ff;">{{info.order_info.real_price*1}}</p>
      </div>
      <div class="box1">
        <p>交易单价</p>
        <p>{{info.order_info.real_price/info.order_info.num}}</p>
      </div>
      <div class="box1">
        <p>交易数量</p>
        <p>{{info.order_info.num}}</p>
      </div>
      <div class="box1">
        <p>订单编号</p>
        <p>{{info.order_info.order_no}}</p>
      </div>
      <div class="box1">
        <p>创建时间</p>
        <p>{{info.order_info.create_at}}</p>
      </div>
      <div class="box2">
        <p>联系电话</p>
        <p><a :href="'tel:' + info.order_info.phone">联系</a></p>
        <p>{{info.order_info.phone}}</p>
      </div>
      <div class="box3" v-if="info.order_info.type == 1">
        <p>付款方式</p>
        <p>
          <img src="../../../static/image/zhifu1.png" alt="" v-if="info.payment_wexin" @click="show_win(1)">
          <img src="../../../static/image/zhifu2.png" alt="" v-if="info.payment_zfb" @click="show_win(2)">
          <img src="../../../static/image/zhifu3.png" alt="" v-if="info.payment_bank" @click="show_win(3)">
        </p>
      </div>
      <div class="box4" v-if="info.order_info.type == 1 && info.order_info.status != 4">
        <p>付款凭证</p>
        <p>
          <span v-if="!imgs">上传凭证</span>
          <input type="file" @change="upload">
          <img  :src="imgs" alt="" v-if="imgs">
        </p>
      </div>
      <div class="box5" v-if="info.order_info.dakuan_image != null">
        <p>查看凭证</p>
        <img :src="info.order_info.dakuan_image" alt="" @click="see_img">
      </div>
    </div>

    <div class="send" v-if="info.order_info.type == 2" @click="shoukuan">确认收款</div>
    <div class="cancel" v-if="is_show" @click="shensu">申诉</div>
    <div class="cancel" v-if="info.order_info.type == 2 && info.order_info.dakuan_image != null" @click="shensu">申诉</div>

    <div class="mask" v-if="wx_boon" @click="close_win"></div>
    <div class="wx" v-if="wx_boon">
      <p>{{info.payment_wexin.payment}}</p>
      <img :src="info.payment_wexin.paymentimg" alt="">
    </div>

    <div class="mask" v-if="al_boon" @click="close_win"></div>
    <div class="wx" v-if="al_boon">
      <p>{{info.payment_zfb.payment}}</p>
      <img :src="info.payment_zfb.paymentimg" alt="">
    </div>

    <div class="mask" v-if="shows" @touchmove.prevent></div>
    <div class="win" v-if="shows">
      <div class="win_box">
        <img class="win_close" src="../../../static/image/win_close.png" alt="" @click="close_win">
        <div class="win_title">银行卡</div>
        <div class="win_box">
          <p>开户银行</p>
          <p>{{info.payment_bank.bankname}}</p>
        </div>
        <div class="win_box">
          <p>真实姓名</p>
          <p>{{info.payment_bank.truename}}</p>
        </div>
        <div class="win_box">
          <p>电话</p>
          <p>{{info.order_info.phone}}</p>
        </div>
        <div class="win_box">
          <p>银行卡号</p>
          <p>{{info.payment_bank.bankcard}}</p>
        </div>
        <div class="win_box" style="border: none!important;">
          <p>开户地址</p>
          <p>{{info.payment_bank.banksite}}</p>
        </div>
      </div>
    </div>

    <div class="imgmask" v-if="imgs_boon" @click="close_imgs">
      <img :src="info.order_info.dakuan_image" alt="">
    </div>

  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        id:'',
        info:{
          order_info:{
            real_price:''
          }
        },
        imgs:'',//买方上传的截图
        shows: false,//展示收款信息
        wx_boon: false,//微信展示
        al_boon: false,//支付宝信息
        second:'',
        minute:'',
        hour:'',
        timer: null,
        is_show: false,
        imgs_boon: false
      }
    },
    components: {},
    methods: {
      see_img(){
        this.imgs_boon = true;
      },
      close_imgs(){
        this.imgs_boon = false;
      },
      formatSeconds(value) {
        let secondTime = parseInt(value);// 秒
        let minuteTime = 0;// 分
        let hourTime = 0;// 小时
        let that = this;
        if(secondTime > 60) {//如果秒数大于60，将秒数转换成整数
          minuteTime = parseInt(secondTime / 60);
          secondTime = parseInt(secondTime % 60);
          if(minuteTime > 60) {
            hourTime = parseInt(minuteTime / 60);
            minuteTime = parseInt(minuteTime % 60);
          }
        }else{
          minuteTime = Math.floor(secondTime / 60);
        }
        that.second =  parseInt(secondTime);
        that.minute = parseInt(minuteTime);
        that.hour = parseInt(hourTime);
        if(that.second == 0 && that.minute == 0 && that.hour == 0){
          clearInterval(that.timer)
          that.getdetail()
        }
      },
      back(){
        this.$router.go(-1)
      },
      getdetail(){
        let that = this;
        that.$post('/apiv1/index/getC2cOrderWaitDetail',{orderId: that.id}).then(res=>{
          if(res.code == 1){
            that.info = res.data;
            if(res.data.countdown <= 0){
              this.is_show = true
            }else{
              this.is_show = false
            }
            if(that.info.countdown > 0){
              that.timer = setInterval(()=>{
                that.info.countdown--
                that.formatSeconds(that.info.countdown)
              },1000)
            }
          }
        })
      },
      upload(e){
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
                that.imgs = baseNetworkUrl + '/' + res.msg.image_url;
                layer.closeAll()
              }
            })
          };
        })(e.target.files[0]);
        reader.readAsDataURL(e.target.files[0]);
      },
      close_win(){
        this.shows = false;
        this.wx_boon = false;
        this.al_boon = false;
      },
      show_win(e){
        if(e == 1){//微信
          this.wx_boon = true;
        }else if(e == 2){//支付宝
          this.al_boon = true
        }else{//银行卡
          this.shows = true;
        }
      },
      shensu(){
        this.$router.replace('/shensu?id=' + this.id)
      },
      shoukuan(){
        let that = this;
        that.$post('/apiv1/index/confrimShouKuan',{orderid: that.id}).then(res=>{
          that.$msg(res.msg)
          if(res.code == 1){
            setTimeout(()=>{
              that.$router.replace('/yiwancheng?id=' + that.id)
            },1000)
          }
        })
      }
    },
    created() {
      let that = this;
      that.id = that.$route.query.id;
      that.getdetail()
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
  .mask{
    width: 100%;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 600;
    background: #000;
    opacity: 0.8;
  }
  .wx{
    width: 500px/$ppr;
    height: 400px/$ppr;
    background: #131f49;
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    margin: auto;
    z-index: 700;
    p{
      width: 100%;
      height: 60px/$ppr;
      line-height: 60px/$ppr;
      text-align: center;
      color: #fff;
    }
    img{
      width: 300px/$ppr;
      height: 300px/$ppr;
      display: block;
      margin: 0 auto;
      margin-top: 20px/$ppr;
    }
  }
  .win{
    width: 610px/$ppr;
    height: 893px/$ppr;
    background: #131f49;
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    margin: auto;
    z-index: 700;
    .win_box{
      width: 610px/$ppr;
      height: 893px/$ppr;
      position: relative;
      .win_close{
        width: 37px/$ppr;
        height: 37px/$ppr;
        position: absolute;
        top: 17px/$ppr;
        right: 19px/$ppr;
      }
      .win_title{
        width: 100%;
        height: 100px/$ppr;
        line-height: 100px/$ppr;
        text-align: center;
        font-size: 32px/$ppr;
        color: #fff;
      }
      .win_box{
        width: 525px/$ppr;
        height: 146px/$ppr;
        border-bottom: 1px dashed #fff;
        margin: 0 auto;
        p:nth-child(1){
          width: 100%;
          height: 70px/$ppr;
          line-height: 70px/$ppr;
          color: #9099bb;
          font-size: 24px/$ppr;
        }
        p:nth-child(2){
          color: #fff;
          font-size: 32px/$ppr;
        }
      }
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
      height: 60px/$ppr;
      padding-top: 150px/$ppr;
      text-align: center;
      font-size: 36px/$ppr;
      color: #8084a6;
    }
    .txt2{
      width: 100%;
      height: 40px/$ppr;
      padding-top: 20px/$ppr;
      text-align: center;
      font-size: 36px/$ppr;
      color: #fff;
    }
    .main{
      width: 683px/$ppr;
      /*height: 782px/$ppr;*/
      background-color: #151e47;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      margin-top: 50px/$ppr;
      padding-bottom: 30px/$ppr;
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
      .box3{
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
          img{
            display: inline-block;
            width: 50px/$ppr;
            height: 50px/$ppr;
            margin: 20px/$ppr 0 0 60px/$ppr;
          }
        }
      }
      .box4{
        width: 100%;
        height: 140px/$ppr;
        line-height: 140px/$ppr;
        font-size: 26px/$ppr;
        color: #fff;
        p:nth-child(1){
          float: left;
          margin-left: 40px/$ppr;
        }
        p:nth-child(2){
          float: right;
          margin-right: 40px/$ppr;
          width: 170px/$ppr;
          height: 47px/$ppr;
          background-color: #00c69e;
          border-radius: 10px/$ppr;
          margin-top: 32px/$ppr;
          position: relative;
          font-size: 26px/$ppr;
          color: #fff;
          line-height: 47px/$ppr;
          text-align: center;
          input{
            width: 170px/$ppr;
            height: 47px/$ppr;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 20;
            opacity: 0;
          }
          img{
            width: 170px/$ppr;
            height: 47px/$ppr;
          }
        }

      }
      .box5{
        width: 100%;
        height: 200px/$ppr;
        line-height: 140px/$ppr;
        font-size: 26px/$ppr;
        color: #fff;
        p:nth-child(1){
          float: left;
          margin-left: 40px/$ppr;
        }
        img{
          width: 200px/$ppr;
          height: 200px/$ppr;
          display: block;
          float: right;
          margin-right: 30px/$ppr;
        }
      }
    }
    .send{
      width: 675px/$ppr;
      height: 95px/$ppr;
      background-color: #5d97df;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      margin-top: 30px/$ppr;
      text-align: center;
      line-height: 95px/$ppr;
      font-size: 30px/$ppr;
      color: #fff;
    }
    .cancel{
      width: 675px/$ppr;
      height: 90px/$ppr;
      border: solid 2px/$ppr #0495ff;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      margin-top: 30px/$ppr;
      margin-bottom: 60px/$ppr;
      text-align: center;
      line-height: 95px/$ppr;
      font-size: 30px/$ppr;
      color: #fff;
    }
  }
</style>
