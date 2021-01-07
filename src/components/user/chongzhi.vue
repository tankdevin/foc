<template>
  <div class="content" v-swipeup="{fn:vuetouch,name:'上滑'}">
    <div class="chongzhi_head">
      <img src="../../../static/image/icon_back1.png" alt @click="back" />
      <span>在线充值</span>
      <span class="record">
        <router-link to="/record">充值记录</router-link>
      </span>
    </div>

    <div class="main">
      <ul class="nav_title">
        <li v-for="(item,index) in nav" :key="index" @click="fn(index)">
          <img v-if="active == index" :src="item.url1" alt />
          <img v-else :src="item.url2" alt />
          <span>{{ item.name }}</span>
        </li>
      </ul>
      <ul class="nav_wrap">
        <li v-for="(it,ind) in nav_wrap" v-if="i == ind" :key="ind" @click="fnn(ind)">
          <p class="title">
            <span class="trange"></span>
            <span>{{it.title}}</span>
          </p>
          <p>
            <span></span>
            <span>姓名：{{it.name}}</span>
          </p>
          <p>
            <span></span>
            <span>账号：{{payment}}</span>
          </p>
          <span :class="it.class"></span>
        </li>
      </ul>
      <div class="box3">
        <p>请上传打款截图</p>
        <img :src="info.paymentimg" alt v-if="info.paymentimg" />
        <input type="file" @change="upload" />
      </div>

      <div class="box2">
        <p>充值金额:</p>
        <input type="text" placeholder="请输入充值金额" v-model="info.code" />
      </div>
      <div class="send" @click="send">确定</div>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
export default {
  data() {
    return {
      list: [],
      page: 1,
      active: 0,
      i: 0,
      payment: "",
      nav: [
        {
          url1: require("../../../static/image/cz_icon1.png"),
          url2: require("../../../static/image/cz_icon2.png"),
          name: "支付宝"
        },
        {
          url1: require("../../../static/image/cz_icon1.png"),
          url2: require("../../../static/image/cz_icon2.png"),
          name: "微信"
        },
        {
          url1: require("../../../static/image/cz_icon1.png"),
          url2: require("../../../static/image/cz_icon2.png"),
          name: "银行卡"
        }
      ],
      nav_wrap: [
        {
          name: "称",
          num: "123456",
          title: "支付宝信息",
          class: "posion1"
        },
        {
          name: "刘",
          num: "123456",
          title: "微信信息",
          class: "posion2"
        },
        {
          name: "路",
          num: "123456",
          title: "银行卡信息",
          class: "posion3"
        }
      ],
      info: {
        paymentimg: require("../../../static/image/upload1.png"),
        code: "",
        type: 1
      },
      nums: 60,
      timer: null
    };
  },
  components: {},
  methods: {
    jiequs(str1, str2) {
      let str = str1 + str2;
      if (String(str).indexOf(".") > -1) {
        var temp = Number(str);
        temp = Math.floor(temp * 1000) / 1000;
        temp = temp.toFixed(4);
        return temp;
      } else {
        return str;
      }
    },
    back() {
      this.$router.go(-1);
    },
    vuetouch: function(s, e) {
      this.page++;
      this.getlist();
    },
    fn(index) {
      this.active = index;
      this.i = index;
    },
    upload(e) {
      let image = e.target.value;
      let reader = new FileReader();
      let that = this;
      reader.onload = (function(file) {
        return function(e) {
          let bs64 = this.result;
          that
            .$post("/apiv1/common/fileImage", {
              image: bs64
            })
            .then(res => {
              if (res.code == 1) {
                that.info.paymentimg = baseNetworkUrl + res.msg.image_url;
              }
            });
        };
      })(e.target.files[0]);
      reader.readAsDataURL(e.target.files[0]);
    },
    send() {
      let that = this;
      if (
        that.info.paymentimg == require("../../../static/image/upload1.png")
      ) {
        that.$msg("请上传收款码");
      } else if (that.info.code == "") {
        that.$msg("请输入充值金额");
      } else {
        console.log(that.info);
        that.$post("/apiv1/index/payadd", that.info).then(res => {
          localStorage.removeItem("register_send_code_time");
          that.$msg(res.msg);
          if (res.code == 1) {
            this.$router.push("/record");
          }
        });
      }
    }
  },
  created() {
    this.$post("/apiv1/index/paymentlistad", { type: 1 }).then(res => {
      if (res.code == 1) {
        console.log(res);
        if (res.data) {
          this.payment = res.data.payment;
          this.info.paymentimg = res.data.paymentimg;
          if (this.info.payment == "") {
            if (this.active == 0) {
              this.$router.push("/wechat");
            } else if (this.active == 1) {
              this.$router.push("/alipay");
            } else {
              this.$router.push("/bankcard");
            }
          }
        }
      }
    });
  },
  destroyed() {}
};
</script>

<style lang="scss" scoped>
@import "../../assets/scss/mixin";
.content {
  width: 100%;
  min-height: 100vh;
  height: 100%;
  background: #0d1637;
  .chongzhi_head {
    width: 100%;
    padding: 0 30px / $ppr 0 40px / $ppr;
    box-sizing: border-box;
    height: 110px / $ppr;
    text-align: center;
    line-height: 110px / $ppr;
    font-size: 36px / $ppr;
    color: #fff;
    background: #0d1637;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #b2c1ff;
    img {
      width: 17px / $ppr;
      height: 29px / $ppr;
      display: block;
    }
    .record {
      a {
        color: #b2c1ff;
        font-size: 24px / $ppr;
      }
    }
  }
  .main {
    width: 675px / $ppr;
    margin: 0 auto;
    padding-top: 120px / $ppr;
    .nav_title {
      color: #fff;
      display: flex;
      justify-content: center;
      font-size: 30px / $ppr;
      li {
        display: flex;
        align-items: center;
        img {
          width: 23px / $ppr;
          height: 23px / $ppr;
          margin-right: 15px / $ppr;
        }
      }
      li:nth-child(3),
      li:nth-child(2) {
        margin-left: 50px / $ppr;
      }
    }
    .nav_wrap {
      width: 70%;
      margin: 0 auto;
      color: #fff;
      li {
        width: 100%;
        padding: 25px / $ppr;
        box-sizing: border-box;
        background: #008aff;
        border-radius: 15px / $ppr;
        margin-top: 60px / $ppr;
        position: relative;
      }
      p {
        display: flex;
        align-items: center;
        font-size: 24px / $ppr;
        line-height: 40px / $ppr;
        span:nth-child(1) {
          width: 15px / $ppr;
          height: 15px / $ppr;
          display: block;
          margin-right: 10px / $ppr;
        }
      }
      .title {
        letter-spacing: 2px / $ppr;
        margin-bottom: 29px / $ppr;
        .trange {
          background: #fff;
          border-radius: 50%;
        }
      }
      .posion1 {
        position: absolute;
        display: block;
        top: -5%;
        left: 70px / $ppr;
        border: 15px / $ppr solid;
        border-color: transparent transparent #008aff #008aff;
        transform: rotate(-225deg);
      }
      .posion2 {
        position: absolute;
        display: block;
        top: -5%;
        left: 230px / $ppr;
        border: 15px / $ppr solid;
        border-color: transparent transparent #008aff #008aff;
        transform: rotate(-225deg);
      }
      .posion3 {
        position: absolute;
        display: block;
        top: -5%;
        left: 400px / $ppr;
        border: 15px / $ppr solid;
        border-color: transparent transparent #008aff #008aff;
        transform: rotate(-225deg);
      }
    }
    .box2 {
      width: 100%;
      height: 74px / $ppr;
      margin-bottom: 36px / $ppr;
      position: relative;
      margin-top: 30px / $ppr;
      p {
        width: 160px / $ppr;
        height: 74px / $ppr;
        line-height: 74px / $ppr;
        text-align: right;
        padding-right: 20px / $ppr;
        float: left;
        font-size: 30px / $ppr;
        color: #fff;
      }
      input {
        width: 420px / $ppr;
        height: 74px / $ppr;
        padding-left: 20px / $ppr;
        background: #1b285a;
        border: none;
        outline: none;
        display: block;
        float: left;
        border-radius: 10px / $ppr;
        color: #fff;
      }
      ::-webkit-input-placeholder {
        /* Chrome/Opera/Safari */
        color: #6978b0;
      }
      span {
        position: absolute;
        top: 14px / $ppr;
        right: 12px / $ppr;
        width: 136px / $ppr;
        height: 46px / $ppr;
        background-color: #008aff;
        border-radius: 10px / $ppr;
        color: #fff;
        text-align: center;
        line-height: 46px / $ppr;
        font-size: 16px / $ppr;
      }
    }
    .box3 {
      width: 100%;
      height: 374px / $ppr;
      margin: 60px / $ppr auto;
      position: relative;
      p {
        width: 100%;
        height: 50px / $ppr;
        text-align: center;
        font-size: 30px / $ppr;
        color: #b2c1ff;
      }
      img {
        width: 324px / $ppr;
        height: 324px / $ppr;
        display: block;
        margin: 0 auto;
      }
      input {
        width: 324px / $ppr;
        height: 324px / $ppr;
        position: absolute;
        top: 50px / $ppr;
        left: 50%;
        margin-left: -162px / $ppr;
        z-index: 100;
        opacity: 0;
      }
    }
    .send {
      width: 100%;
      height: 90px / $ppr;
      background-color: #008aff;
      border-radius: 10px / $ppr;
      text-align: center;
      line-height: 90px / $ppr;
      font-size: 30px / $ppr;
      color: #fff;
      margin-top: 68px / $ppr;
    }
  }
}
</style>
