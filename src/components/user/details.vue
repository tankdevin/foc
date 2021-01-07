<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt @click="back" />
      订单详情
    </div>
    <Bscroll>
      <van-swipe :autoplay="3500" indicator-color="white" class="swiper-container">
        <van-swipe-item v-for="(item,i) in image" :key="i">
          <img v-lazy="item" alt />
        </van-swipe-item>
      </van-swipe>
      <div class="main">
        <p class="first" v-html="detail.goods_desc"></p>
        <p v-html="detail.goods_countent"></p>
        <p class="last">
          ￥{{jiequ(detail.selling_price)}}
          <span>￥{{jiequ(detail.market_price)}}</span>
        </p>
        <a class="kuchun">库存{{detail.ku}}件</a>
      </div>
      <div class="goods">商品详情</div>
      <div class="goods_main" v-html="detail.goods_content">商品详情</div>
    </Bscroll>
    <div class="buy_boo">
      <div class="buy" @click="hh">立即购买</div>
    </div>
    <div class="under" v-if="boon">
      <div class="top">
        <img v-lazy="detail.goods_logo" class="top_left" alt />
        <div class="top_middle">
          <div>
            ￥{{jiequ(detail.selling_price)}}
            <span>￥{{jiequ(detail.market_price)}}</span>
          </div>
          <div>库存{{detail.ku}}件</div>
        </div>
      </div>
      <div class="di">
        <div class="di_top">
          <div>
            <div>选择数量</div>
          </div>
          <div class="sum_cont">
            <label class="minute" @click="btn_minute(num)">-</label>
            <input class="input" v-model="num" />
            <label class="add" @click="btn_add(num)">+</label>
          </div>
        </div>
        <div class="di_under" @click="order">立即购买</div>
      </div>
    </div>
    <div class="mask" v-if="boon" @click="mask"></div>
  </div>
</template>

<script type="text/ecmascript-6">
export default {
  data() {
    return {
      boon: false,
      detail: ""
      ,
      image: [],
      num: 1
    };
  },
  mounted() {
    let id = this.$route.query.id;
    this.$post("/apiv1/order/goodsdetailed", {
      goods_id: id
    }).then(res => {
      this.detail = res.data;
      this.image = res.data.goods_image;
    });
  },
  methods: {
    //添加
    btn_add(num) {
      if (num < 9999) {
        this.num++;
      }
    },
    //减去
    btn_minute(num) {
      if (num > 1) {
        this.num--;
      }
    },
    back() {
      this.$router.go(-1);
    },
    mask() {
      this.boon = false;
    },
    hh() {
      this.boon = true;
    },
    img() {
      this.boon = false;
    },
    order() {
      let id = this.$route.query.id;
      this.$router.push("/order?num=" + this.num + "&id=" + id);
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
.kuchun {
  height: 45px / $ppr;
  line-height: 45px / $ppr;
  padding: 0 10px / $ppr;
  border: 1px solid #ee4c0c;
  border-radius: 1.75rem;
  font-size: 26px / $ppr;
  color: #ee4c0c;
  font-weight: bold;
  position: absolute;
  right: 45px / $ppr;
  bottom: 35px / $ppr;
}

.wrap {
  height: calc(100vh - 2.75rem);
  overflow: hidden;
}

.swiper-container {
  width: 100%;
  height: 757px / $ppr;

  img {
    width: 100%;
    height: 757px / $ppr;
  }
}

.main {
  width: 100%;
  padding-top: 0.5rem;
  position: relative;

  .first {
    width: 650px / $ppr;
    font-family: PingFang-SC-Regular;
    font-size: 28px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    line-height: 40px / $ppr;
    letter-spacing: 0px;
    color: #fff;
    margin: 0 auto;
  }

  .last {
    box-sizing: border-box;
    padding-left: 45px / $ppr;
    margin-top: 35px / $ppr;
    font-family: PingFang-SC-Heavy;
    font-size: 28px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    line-height: 40px / $ppr;
    letter-spacing: 0px;
    color: #ee4c0c;
    padding-bottom: 35px / $ppr;

    span {
      font-size: 24px / $ppr;
      text-decoration: line-through;
      color: #9c9c9c;
    }
  }
}

.goods img {
  width: 100%;
  height: 100%;
}

.goods {
  width: 100%;
  font-family: PingFang-SC-Regular;
  font-size: 24px / $ppr;
  font-weight: bold;
  font-stretch: normal;
  line-height: 80px / $ppr;
  letter-spacing: 0px;
  color: #7d7d7d;
  text-align: center;
  margin-top: 20px / $ppr;

  p img,
  img {
    width: 100%;
  }
}

.goods_main {
  font-family: PingFang-SC-Regular;
  font-size: 24px / $ppr;
  font-weight: bold;
}

.img {
  img {
    width: 100%;
  }
}

.buy_boo {
  width: 100%;
  height: 100px / $ppr;

  position: fixed;
  bottom: 0;
  left: 0px / $ppr;

  .buy {
    width: 605px / $ppr;
    height: 70px / $ppr;
    background-image: linear-gradient(-90deg, #4eccfe 0%, #1a7dfc 100%),
      linear-gradient(#ffffff, #ffffff);
    background-blend-mode: normal, normal;
    font-size: 30px / $ppr;
    border-radius: 35px / $ppr;
    color: #fff;
    line-height: 70px / $ppr;
    margin: 15px / $ppr auto 15px / $ppr;
    text-align: center;
  }
}

.mask {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
  background: rgba(0, 0, 0, 0.5);
}

.under {
  width: 100%;
  height: 490px / $ppr;
  background-color: #1b285a;
  position: absolute;
  bottom: 0;
  left: 0;
  z-index: 10001;
  color: #fff;
  input{
    background-color: #1b285a;
  }

  .top {
    height: 50%;
    position: relative;

    .top_left {
      width: 200px / $ppr;
      height: 200px / $ppr;
      position: absolute;
      left: 0.8rem;
      top: -1rem;
      border-radius: 10%;
    }

    .top_middle {
      position: absolute;
      left: 6.5rem;
      top: 0.6rem;
      font-size: 25px / $ppr;
      height: 100px / $ppr;
      line-height: 70px / $ppr;
      color: #f75b22;
      font-weight: 900;

      div:first-child {
        font-size: 30px / $ppr;

        span {
          text-decoration: line-through;
          font-weight: normal;
          color: #999;
          margin-left: 20px / $ppr;
        }
      }

      div:last-child {
        border: 1px solid salmon;
        border-radius: 70px / $ppr;
        height: 40px / $ppr;
        text-align: center;
        line-height: 40px / $ppr;
        font-weight: 600;
      }
    }

    .top_under {
      height: 70px / $ppr;
      padding-top: 30px / $ppr;
      padding-right: 30px / $ppr;

      img {
        border: 1px solid red;
      }
    }
  }

  .di {
    height: 50%;

    .di_top {
      width: 100%;
      padding-left: 0.8rem;
      padding-top: 0.6rem;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      font-size: 25px / $ppr;
      position: relative;

      div:last-child {
        font-family: PingFang-SC-Regular;
        font-size: 25px / $ppr;
        font-weight: bold;
        font-stretch: normal;
        line-height: 40px / $ppr;
        color: #eba81c;
      }

      /* 总数量*/
      .sum_cont {
        position: absolute;
        right: 0.8rem;
        bottom: 0;
        display: flex;
        justify-content: space-around;
        /*减号*/

        .minute {
          width: 50px / $ppr;
          text-align: center;
          height: 50px / $ppr;
          line-height: 50px / $ppr;
          left: 10px / $ppr;
          font-size: 18px / $ppr;
          border: 1px / $ppr solid #dd524d;
        }

        /*加号*/

        .add {
          width: 50px / $ppr;
          border: 1px / $ppr solid #dd524d;
          height: 50px / $ppr;
          line-height: 50px / $ppr;
          text-align: center;
          font-size: 18px / $ppr;
        }

        /*文本框*/

        .input {
          width: 90px / $ppr;
          height: 50px / $ppr;
          display: inline-block;
          line-height: 50px / $ppr;
          text-align: center;
        }
      }
    }

    .di_under {
      width: 605px / $ppr;
      height: 70px / $ppr;
      background-image: linear-gradient(-90deg, #4eccfe 0%, #1a7dfc 100%),
        linear-gradient(#ffffff, #ffffff);
      background-blend-mode: normal, normal;
      font-size: 30px / $ppr;
      border-radius: 35px / $ppr;
      color: #fff;
      line-height: 70px / $ppr;
      text-align: center;
      position: absolute;
      bottom: 1rem;
      left: 50%;
      transform: translateX(-50%);
    }
  }
}
.demo{
    width: 100%;
    height: 100%;
}
</style>
