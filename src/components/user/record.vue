<template>
  <div class="content" v-swipeup="{fn:vuetouch,name:'上滑'}">
    <div class="head">
      <img src="../../../static/image/icon_back1.png" alt @click="back" />
      充值记录
    </div>
    <van-list v-model="loading" :finished="finished" finished-text="没有更多数据了" @load="onLoad">
      <div class="main">
        <!-- <p class="title">2019年10月17日</p> -->
        <div class="all">
          <div class="all_conte">
            <div>
              <p>充值笔数</p>
              <p>3</p>
            </div>
            <div>
              <p>合计</p>
              <p>￥600.00</p>
            </div>
          </div>
        </div>
        <ul class="list">
          <li v-for="(item,index) in nav " :key="index">
            <p v-if='item.type ==1' >支付宝</p>
            <p v-if='item.type ==2' >微信</p>
            <p v-if='item.type ==3' >银行卡</p>
            <p class="wrap">
              <span class="time">{{ item.time }}</span>
              <span>￥{{item.money}}</span>
            </p>
          </li>
        </ul>
      </div>
    </van-list>
  </div>
</template>

<script type="text/ecmascript-6">
export default {
  data() {
    return {
      list: [],
      page: 1,
      active: 0,
      loading: false,
      finished: false,
      countsum: -1, //我的数据
      nav: ""
    };
  },
  components: {},
  methods: {
    onLoad() {
      // 异步更新数据
      setTimeout(() => {
        // 加载状态结束
        this.loading = false;
        // 数据全部加载完成
        if (this.nav.length >= (this.nav.length-1)) {
          this.finished = true;
        }
      }, 500);

      //我的
      this.$post("/apiv1/order/paylist").then(res => {
        if (res.code == 1) {
          console.log(res.data);
          this.nav = res.data;
        }
      });
    },
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
    }
  },
  created() {},
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
  .head {
    width: 100%;
    height: 110px / $ppr;
    position: relative;
    text-align: center;
    line-height: 110px / $ppr;
    font-size: 36px / $ppr;
    color: #b2c1ff;
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
  .main {
    width: 675px / $ppr;
    margin: 0 auto;
    margin-top: 110px / $ppr;
    background: #3e4abf;
    border-radius: 15px / $ppr;
    box-sizing: border-box;
    .title {
      padding: 20px / $ppr 0;
      font-size: 24px / $ppr;
      color: #fff;
      padding-left: 30px / $ppr;
      box-sizing: border-box;
      background: #198bff;
      border-top-left-radius: 15px / $ppr;
      border-top-right-radius: 15px / $ppr;
    }
    .all {
      width: 100%;
      padding: 25px / $ppr 15px / $ppr 0 15px / $ppr;
      box-sizing: border-box;
      line-height: 50px / $ppr;
      .all_conte {
        padding: 0 15px / $ppr 15px / $ppr 15px / $ppr;
        display: flex;
        justify-content: space-between;
        border-bottom: 1px / $ppr solid #fff;
      }
      div {
        font-size: 30px / $ppr;
        color: #ffff;
      }
    }
    .list {
      width: 100%;
      padding: 0 15px / $ppr;
      box-sizing: border-box;
      font-size: 30px / $ppr;
      color: #fff;
      li {
        padding: 40px / $ppr 15px / $ppr;
        box-sizing: border-box;
        border-bottom: 1px / $ppr dotted #fff;
        .time {
          font-size: 20px / $ppr;
        }
        .wrap {
          display: flex;
          justify-content: space-between;
          margin-top: 14px / $ppr;
          align-items: flex-end;
        }
      }
      li:last-child {
        border-bottom-style: none;
      }
    }
  }
}
</style>
