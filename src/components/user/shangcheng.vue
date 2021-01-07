<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt @click="back" />
      商城
    </div>
    <main ref="my_grou">
      <van-pull-refresh
        v-model="isLoading"
        @refresh="onRefresh"
        :loosing-text="loosText"
        :head-height="100"
        van-clearfix
      >
        <van-list
          v-model="loading"
          :finished="finished"
          :finished-text="finishedText"
          @load="onLoad"
        >
          <div class="rem_boo">
            <div class="bulk_box" @click="details(item.id)" v-for="(item,k) in goods" :key="k">
              <img :src="item.goods_logo" alt />
              <p class="text1">{{item.goods_title}}</p>
              <p class="text2">￥{{jiequ(item.selling_price)}}</p>
            </div>
          </div>
        </van-list>
      </van-pull-refresh>
    </main>
  </div>
</template>

<script type="text/ecmascript-6">
export default {
  data() {
    return {
      my_state: 0,
      finishedText: "", //加载后有无数据显示的字
      num: 1, //页数
      loosText: "松开刷新", //下拉刷新时显示的文字
      pullText: "", //下拉过程文案
      isLoading: false, //下拉刷新开关
      loading: false, //上拉加载中
      finished: false, //上拉加载结束
      // goods: [
      //     {goods_logo:require('../../../static/img/block.png'),goods_title:11,selling_price:100},
      //     {goods_logo:require('../../../static/img/block.png'),goods_title:11,selling_price:100},
      //     {goods_logo:require('../../../static/img/block.png'),goods_title:11,selling_price:100},
      //     {goods_logo:require('../../../static/img/block.png'),goods_title:11,selling_price:100},
      //     {goods_logo:require('../../../static/img/block.png'),goods_title:11,selling_price:100},
      //     {goods_logo:require('../../../static/img/block.png'),goods_title:11,selling_price:100},
      //     {goods_logo:require('../../../static/img/block.png'),goods_title:11,selling_price:100},
      //     {goods_logo:require('../../../static/img/block.png'),goods_title:11,selling_price:100},
      //     ],
      goods:[],
      shopid: ""
    };
  },
  activated() {
    this.$refs.my_grou.scrollTop = this.my_state;
  },
  methods: {
    details(id) {
      this.my_state = this.$refs.my_grou.scrollTop;
      this.$router.push("/details?id=" + id);
    },
    back() {
      this.$router.go(-1);
    },
    onLoad() {
      //上拉加载
      setTimeout(() => {
        this.getuser_more();
      }, 500);
    },
    onRefresh() {
      //下拉刷新
      // location.reload()
      this.my_state = 0;
      this.goods = [];
      this.num = 1;
      this.getGoods();
      this.onLoad();
      this.isLoading = false;
    },
    getuser_more() {
      //上拉加载更多
      this.num++;
      // this.$post("", {
      //   pageNo: this.num
      // }).then(res => {
      //   console.log(res);
      //   if (res.code == 1) {
      //     this.goods = this.goods.concat(res.data);
      //     if (res.data.length < 1) {
      //       this.finished = true;
      //       this.finishedText = "没有更多了";
      //     }
      //     if (this.goods.length < 1) {
      //       this.finishedText = "暂无数据";
      //     }
      //   }
      //   this.loading = false;
      // });
    },
    getGoods() {
      this.$post("/apiv1/order/tuangroupList", {
        pageNo: 1
      }).then(res => {
        console.log(res.data);
        this.goods = res.data;
        if (this.goods.length < 1) {
          this.finishedText = "暂无数据";
        }
      });
    }
  },
  created() {
    this.getGoods();
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
main {
  height: calc(100vh - 2.75rem);
  overflow-y: scroll;
  -webkit-overflow-scrolling: touch;
  margin-top: 2.75rem;
}
.rem_boo {
  width: 100%;
  padding: 25px / $ppr;
  box-sizing: border-box;
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;

  .bulk_box {
    width: 48%;
    margin-bottom: 35px / $ppr;
    background-color: #ffffff;
    border-radius: 10px / $ppr;
    border: solid 1px #ebebeb;
    overflow: hidden;

    img {
      width: 100%;
      height: 335px / $ppr;
      align-self: center;
    }

    .text1 {
      margin-left: 20px / $ppr;
      margin-top: 6px / $ppr;
      margin-bottom: 10px / $ppr;
      font-family: PingFang-SC-Medium;
      font-size: 26px / $ppr;
      color: #000000;
    }

    .text2 {
      margin-left: 20px / $ppr;
      font-family: PingFang-SC-Heavy;
      font-size: 26px / $ppr;
      color: #f24f18;
      margin-bottom: 20px / $ppr;
    }
  }
}

.details {
  width: 100%;
  min-height: 100vh;
  height: 100vh;
  background: #f8fbff;
  position: relative;
  overflow: hidden;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 10000;

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
    height: calc(100vh - 5.25rem);
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
    background: #fff;
    position: relative;

    .first {
      width: 650px / $ppr;
      font-family: PingFang-SC-Regular;
      font-size: 28px / $ppr;
      font-weight: bold;
      font-stretch: normal;
      line-height: 40px / $ppr;
      letter-spacing: 0px;
      color: #000000;
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
    background: #fff;
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
    background: #fff;

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
    background-color: #fff;
    position: absolute;
    bottom: 0;
    left: 0;
    z-index: 10001;

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
}
</style>
