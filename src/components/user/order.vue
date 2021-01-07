<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/img/icon_back.png" alt @click="back" />
      提交订单
    </div>
    <div class="top" @click="adress">
      <div class="top_img1">
        <img src="../../../static/img/weizhi.png" alt />
      </div>
      <div class="top_address">
        <div class="name">
          <p>{{address.area}}</p>
          <p>{{address.phone}}</p>
        </div>
        <div class="name_boo">
          <p>{{address.province_id}} {{address.city_id}} {{address.district_id}} {{address.addr}}</p>
        </div>
      </div>
    </div>

    <div class="add-address top" @click="adress" v-show= 'bol'>
      <div class="top_img1">
        <img src="../../../static/img/weizhi.png" alt />
      </div>
      <p class>添加收货地址</p>
    </div>
    <div class="bottom_bar">
      <img src="../../../static/img/addres_bar.jpg" />
    </div>
    <div class="main">
      <div class="main_top">
        <img src="../../../static/img/gouwu.png" alt />
        <span>{{goods.groupName}}</span>
      </div>

      <div class="main_goods">
        <div class="goods_left">
          <img v-lazy="goods.goods_logo" alt />
        </div>
        <div class="goods_right">
          <div>{{goods.goods_title}}</div>
          <div class="right_price">
            <div class="price1">
              ￥{{selling_price + deduction_price}}
              <span>￥{{goods.market_price*1}}</span>
            </div>
            <div class="num">X{{num}}</div>
          </div>
        </div>
      </div>
      <div class="main_price">
        <p>商品金额</p>
        <p>￥{{selling_price + deduction_price}}</p>
      </div>
      <div class="main_boo" v-if="goods.cate_id == 1">
        <p>赠送批发券</p>
        <p>￥{{goods.pfMoney*1}}</p>
      </div>
    </div>

    <div class="boo1" v-if="goods.cate_id != 2 && goods.cate_id != 9">
      <img src="../../../static/img/quan.png" alt />
      <p>团购券支付（可用团购券￥{{profile.buyMoney*1}}）</p>
      <img src="../../../static/img/dui.png" alt />
    </div>

    <div class="boo1" v-if="goods.cate_id == 9">
      <img src="../../../static/img/quan.png" alt />
      <p>{{jiequ(deduction_price)}} 批发券 + {{jiequ(selling_price)}} 团购券</p>
      <img src="../../../static/img/dui.png" alt />
    </div>

    <div class="boo2" v-if="goods.cate_id == 2">
      <div class="money-left">
        <img src="../../../static/img/zhifu.png" alt />
        <div class="money-group">
          <p>{{shop_pf_money*1}} 现金 + {{shop_tg_money*1}} OPF</p>
          <p>可用批发券：{{profile.pfj*1}}</p>
        </div>
      </div>
      <img src="../../../static/img/dui.png" @click="changePayType(1)" v-if="payType==1" alt />
      <img src="../../../static/img/yuan.png" @click="changePayType(1)" v-else alt />
    </div>

    <div class="order">
      <div>
        应付：
        <span>￥{{selling_price*num}} 团购券</span>
      </div>
      <div class="order1" @click="submit" v-if="subFlag">提交订单</div>
      <div class="order1" v-else>正在提交...</div>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
export default {
  data() {
    return {
      selling_price: 0,
      deduction_price: 0,
      shop_pf_money: "",
      shop_tg_money: "",
      subFlag: true,
      id: 0,
      num: 0,
      bol: false,
      // goods: {
      //   cate_id: 1,
      //   groupName: 2,
      //   goods_logo: require("../../../static/image/index7.png"),
      //   goods_title: 1,
      //   market_price:1,
      //   pfMoney:1
      // },
      // address: {
      //   name: 1,
      //   phone: 111,
      //   province_id: 1,
      //   city_id: 1,
      //   district_id: 1,
      //   addr: 1
      // },
      goods: "",
      address: "",
      profile: {},
      payType: 1
    };
  },
  created() {
    this.id = this.$route.query.id;
    this.num = this.$route.query.num;
    this.$post("/apiv1/order/address").then(res => {
      console.log(res);
      if (res.code == 0) {
        // this.tabs = 0;
      } else {
        this.address = res.data;
      }
    });
    this.$post("/apiv1/order/goodsdetailed", {
      goods_id: this.id,
      num: this.num
    }).then(res => {
      console.log(res);
      this.goods = res.data;
      this.selling_price = Number(res.data.market_price);
      this.deduction_price = Number(res.data.selling_price);
      this.shop_pf_money = res.data.market_price;
      this.shop_tg_money = res.data.selling_price;
      console.log(this.goods);
    });
    // this.$post("/user/profile").then(res => {
    //   this.profile = res.data;
    // });
  },
  methods: {
    back() {
      this.$router.back();
    },
    adress() {
      this.$router.push("/shipaddress");
    },
    submit() {
      this.subFlag = false;
      this.$post("/apiv1/order/addorder", {
        addr_id: this.address.id,
        goods_id: this.id,
        num: this.num
      }).then(res => {
        console.log(res);
        this.$msg(res.msg);
        if (res.code == 1) {
          this.$router.push("/dingdan");
        } else {
          this.subFlag = true;
        }
      });
      // if (this.goods.cate_id != 2) {
      //   if (this.goods.cate_id == 3) {
      //     this.$post("/apiv1/order/addorder", {
      //       addr_id: this.address.id,
      //       goods_id: this.id,
      //       num: this.num,
      //     }).then(res => {
      //       this.$msg(res.msg);
      //       if (res.code == 1) {
      //         this.$router.push("/buysuccess");
      //       } else {
      //         this.subFlag = true;
      //       }
      //     });
      //   } else if (this.goods.cate_id == 8) {
      //     // 秒杀专区
      //     this.$post("/order/seckillGoodsBuy", {
      //       addr_id: this.address.id,
      //       goods_id: this.id,
      //       num: this.num,
      //       paypassword: ""
      //     }).then(res => {
      //       this.$msg(res.msg);
      //       if (res.code == 1) {
      //         this.$router.push("/buysuccess");
      //       } else {
      //         this.subFlag = true;
      //       }
      //     });
      //   } else if (this.goods.cate_id == 9) {
      //     // 精品专区
      //     this.$post("/order/jingBuyOrder", {
      //       addr_id: this.address.id,
      //       goods_id: this.id,
      //       num: this.num,
      //       paypassword: ""
      //     }).then(res => {
      //       this.$msg(res.msg);
      //       if (res.code == 1) {
      //         this.$router.push("/buysuccess");
      //       } else {
      //         this.subFlag = true;
      //       }
      //     });
      //   } else {
      //     this.$post("/order/addorder", {
      //       addr_id: this.address.id,
      //       goods_id: this.id,
      //       num: this.num,
      //       paypassword: ""
      //     }).then(res => {
      //       if (res.code == 1) {
      //         this.$router.push("/buysuccess?type=1&money=" + res.data.pfMoney);
      //       } else {
      //         this.$msg(res.msg);
      //         this.subFlag = true;
      //       }
      //     });
      //   }
      // } else {
      //   this.$post("/order/pf_goods", {
      //     addr_id: this.address.id,
      //     goodsid: this.id,
      //     num: this.num,
      //     type: this.payType - 1
      //   }).then(res => {
      //     if (res.code == 1) {
      //       this.$router.push("/buysuccess?type=2");
      //     } else {
      //       this.$msg(res.msg);
      //       this.subFlag = true;
      //     }
      //   });
      // }
    },
    changePayType(type) {
      this.payType = type;
    }
  }
};
</script>

<style lang="scss" scoped>
@import "../../assets/scss/mixin";
.content {
  width: 100%;
  min-height: 100vh;
  height: 100%;
  background: #0d1637;
  .head {
    width: 100%;
    height: 110px / $ppr;
    position: relative;
    text-align: center;
    line-height: 110px / $ppr;
    font-size: 36px / $ppr;
    color: #fff;
    background: #0d1637;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    img {
      width: 17px / $ppr;
      height: 29px / $ppr;
      display: block;
      position: absolute;
      top: 40px / $ppr;
      left: 44px / $ppr;
    }
  }
}
.top {
  width: 100%;
  padding-bottom: 1rem;
  background: #1b285a;
  color: #fff;
  margin-top: 2.75rem;
  display: flex;
  justify-content: space-around;

  .top_img1 {
    width: 20%;
    margin-top: 50px / $ppr;

    img {
      width: 37px / $ppr;
      height: 35px / $ppr;
      margin: 0 auto;
    }
  }

  .top_address {
    margin-top: 30px / $ppr;
    width: 80%;
    display: flex;
    flex-direction: column;

    .name {
      display: flex;
      justify-content: space-between;
      font-family: PingFang-SC-Regular;
      font-size: 25px / $ppr;
      font-weight: bold;
      font-stretch: normal;
      line-height: 40px / $ppr;
      letter-spacing: 0px;

      p:last-child {
        padding-right: 60px / $ppr;
      }
    }

    .name_boo {
      font-family: PingFang-SC-Regular;
      font-size: 24px / $ppr;
      font-weight: bold;
      font-stretch: normal;
      line-height: 40px / $ppr;
      letter-spacing: 1px / $ppr;
      color: #979797;
      padding-top: 10px / $ppr;
    }
  }
}

.add-address {
  align-items: center;
  justify-content: center;

  .top_img1 {
    margin: 0;
  }
}

.main {
  width: 100%;
  background: #1b285a;
  color: #fff;
  margin-top: 20px / $ppr;

  .main_top {
    height: 90px / $ppr;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #e6e6e6;

    img {
      width: 30px / $ppr;
      height: 30px / $ppr;
      margin-left: 20px / $ppr;
      margin-right: 15px / $ppr;
    }

    span {
      font-family: PingFang-SC-Regular;
      font-size: 26px / $ppr;
      font-weight: bold;
      font-stretch: normal;
      letter-spacing: 0px;
    }
  }

  .main_goods {
    height: 246px / $ppr;
    display: flex;
    justify-content: space-around;
    border-bottom: 1px / $ppr solid #e6e6e6;

    .goods_left {
      width: 30%;

      img {
        width: 200px / $ppr;
        height: 200px / $ppr;
        margin: 30px / $ppr 0 0 20px / $ppr;
      }
    }

    .goods_right {
      width: 497px / $ppr;
      height: 65px / $ppr;
      font-family: PingFang-SC-Regular;
      font-size: 26px / $ppr;
      font-weight: bold;
      font-stretch: normal;
      line-height: 40px / $ppr;
      letter-spacing: 0px;
      margin-top: 47px / $ppr;

      .right_price {
        display: flex;
        justify-content: space-between;
        margin-top: 40px / $ppr;

        .price1 {
          font-family: PingFang-SC-Regular;
          font-size: 28px / $ppr;
          font-weight: bold;
          letter-spacing: 0px;
          color: #f54d0e;

          span {
            font-family: PingFang-SC-Regular;
            font-size: 24px / $ppr;
            font-weight: normal;
            letter-spacing: 0px;
            color: #979797;
            text-decoration: line-through;
          }
        }

        .num {
          padding-right: 100px / $ppr;
        }
      }
    }
  }

  .main_price {
    height: 108px / $ppr;
    display: flex;
    justify-content: space-between;
    font-family: PingFang-SC-Medium;
    font-size: 28px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    line-height: 108px / $ppr;
    letter-spacing: -1px / $ppr;
    border-bottom: 1px / $ppr solid #e6e6e6;

    p:first-child {
      padding-left: 30px / $ppr;
    }

    p:last-child {
      color: #ff4f1f;
      padding-right: 30px / $ppr;
    }
  }

  .main_boo {
    height: 108px / $ppr;
    display: flex;
    justify-content: space-between;
    font-family: PingFang-SC-Medium;
    font-size: 28px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    line-height: 108px / $ppr;
    letter-spacing: -1px / $ppr;

    p:first-child {
      padding-left: 30px / $ppr;
    }

    p:last-child {
      color: #2a2a2a;
      padding-right: 30px / $ppr;
    }
  }
}

.boo1 {
  height: 110px / $ppr;
  display: flex;
  justify-content: space-between;
  margin-top: 30px / $ppr;
  background: #1b285a;
  color: #fff;

  img {
    width: 38px / $ppr;
    height: 28px / $ppr;
    margin-top: 37px / $ppr;
    margin-left: 20px / $ppr;
  }

  img:last-child {
    margin-right: 20px / $ppr;
    width: 38px / $ppr;
    height: 38px / $ppr;
  }

  p {
    font-family: PingFang-SC-Medium;
    font-size: 28px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    line-height: 108px / $ppr;
    letter-spacing: -1px / $ppr;
    padding-right: 50px / $ppr;
  }
}

.boo2 {
  height: 129px / $ppr;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 30px / $ppr;
  background: #fff;

  .money-left {
    align-items: center;
    display: flex;
  }

  img {
    width: 38px / $ppr;
    height: 42px / $ppr;
    margin-left: 35px / $ppr;
    margin-right: 30px / $ppr;
  }

  img:last-child {
    margin-right: 20px / $ppr;
    width: 38px / $ppr;
    height: 38px / $ppr;
  }

  p {
    font-family: PingFang-SC-Medium;
    font-size: 28px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    letter-spacing: -1px / $ppr;
    color: #505050;
    padding-right: 50px / $ppr;
  }

  p:last-child {
    font-family: PingFang-SC-Medium;
    font-size: 24px / $ppr;
    font-weight: bold;
    color: #aeaeae;
  }
}

.order {
  width: 100%;
  height: 97px / $ppr;
  position: fixed;
  bottom: 0;
  left: 0;
  display: flex;
  justify-content: space-between;
  background: #1b285a;

  div {
    margin-left: 30px / $ppr;
    font-family: PingFang-SC-Medium;
    font-size: 28px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    line-height: 97px / $ppr;
    letter-spacing: -1px / $ppr;
    color: #000000;

    span {
      color: #f54d0e;
    }
  }

  .order1 {
    float: right;
    width: 211px / $ppr;
    height: 97px / $ppr;
    background: linear-gradient(-90deg, #4eccfe 0%, #1a7dfc 100%);
    font-family: PingFang-SC-Medium;
    font-size: 28px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    line-height: 97px / $ppr;
    letter-spacing: -1px / $ppr;
    color: #aeaeae;
    text-align: center;
  }
}
</style>
