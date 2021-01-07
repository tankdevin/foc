<template>
  <div class="content" v-swipeup="{fn:vuetouch,name:'上滑'}">
    <Foot :status="1"></Foot>
    <div class="mask" v-if="user_boon" @click="close_win" @touchmove.prevent></div>
    <User class="wins" v-if="user_boon"></User>
    <div class="head">
      <div class="headbox">
        <img src="../../../static/image/index1.png" alt class="user" @click="showuser" />
        <img src="../../../static/image/index_logo.png" alt class="logo" />
        <!-- <p class="qiandao" @click="qiandao">新人签到</p> -->
        <img class="qiandao" @click="sign" src="../../../static/image/add.png" alt />
      </div>
      <div class="add" v-if="boon">
        <img class="san" src="../../../static/image/sanjiaoxing.png" alt />
        <div v-for="(i,index) in nav_list" @click="details(i.name)">
          <img :src="i.url" alt />
          {{i.name}}
        </div>
      </div>
    </div>

    <!--<img src="../../../static/image/index3.png" class="banner" alt="">-->
    <div class="banner">
      <swiper :options="swiperOption">
        <swiper-slide v-for="(i,ind) in banner" :key="ind">
          <img :src="i.img" list @click="announcement(i.id)" />
        </swiper-slide>
      </swiper>
    </div>
    <div class="zhanwei1"></div>
    <div class="kuang">
      <div class="k_txt" v-if="status == 0">可以收矿了，快点击这里收矿吧</div>
      <div class="k_txt" v-else>正在挖矿，还有{{hour || 0}}小时{{minute || '00'}}分{{second}}秒可以收矿</div>
      <div class="k_box" id="box1">
        <div class="k_btn" :style="{width: progress+'%'}"></div>
      </div>
      <img src="../../../static/image/kkkk.png" alt @click="shoukuang" v-if="status == 0" />
      <img src="../../../static/image/kuang.gif" alt @click="shoukuang" v-else />
    </div>

    <div class="btn_list">
      <div @click="to('/newlist')">
        <p>OPF余额</p>
        <p v-html="profile.account_money ? jiequ(profile.account_money) : 0"></p>
      </div>
      <div @click="to('/slwk')">
        <p>算力挖矿</p>
        <p v-html="profile.hashrate ? jiequ(profile.hashrate) : 0"></p>
      </div>
      <div @click="to('/jinri')">
        <p>今日收益</p>
        <p v-html="profile.today_bonus ? jiequ(profile.today_bonus) : 0"></p>
      </div>
      <div @click="to('/share')">
        <p>分享挖矿</p>
        <p v-html="profile.mining ? jiequ(profile.mining) : 0"></p>
      </div>
      <div @click="to('/integral')">
        <p>现金积分</p>
        <p v-html="profile.integral ? jiequ(profile.integral) : 0"></p>
      </div>
    </div>

    <div class="piao" @click="to('/zhuanpan')"></div>

    <div class="jilu">
      <div class="jilu_titile">
        <div>交易记录</div>
        <div>
          <img src="../../../static/image/index10.png" alt />
          <p @click="to('/pdrlist')">查看全部</p>
        </div>
      </div>
      <div class="list" v-for="i in list" @click="todetail(i.status,i.id)">
        <div :class="i.type == 1 ? 'mairu' : 'maichu'"></div>
        <div v-html="i.type == 1 ? '买入' : '卖出'"></div>
        <div>数量：{{i.num*1}}</div>
        <div v-if="i.status == 1">待匹配</div>
        <div v-if="i.status == 2">待打款</div>
        <div v-if="i.status == 3">已完成</div>
        <div v-if="i.status == 4">已打款</div>
        <div v-if="i.status == 5">已取消</div>
        <div v-if="i.status == 6">申诉中</div>
        <div v-if="i.status == 7">申诉完成</div>
      </div>
    </div>

    <!-- <div class="yindao" v-if="close_banner == 1">
      <swiper :options="swiperOption">
        <swiper-slide>
          <img src="../../../static/image/q1.png">
        </swiper-slide>
        <swiper-slide>
          <img src="../../../static/image/q2.png">
        </swiper-slide>
        <swiper-slide>
          <img src="../../../static/image/q3.png" v-swipeleft="{fn:vuetouch,name:'右滑'}">
        </swiper-slide>
      </swiper>
    </div>-->

    <div class="mask1" v-if="is_login" @touchmove.prevent></div>
    <div class="money" v-if="is_login">
      <div class="money_box">
        <img class="money_close" src="../../../static/image/icon_close.png" alt @click="close" />
        <img class="money_chai" src="../../../static/image/icon_chai.png" alt @click="text" />
      </div>
    </div>

    <div class="mask2" v-if="is_save" @touchmove.prevent></div>
    <div class="save" v-if="is_save">
      <div class="save_box">
        <p>{{money}}</p>
        <img class="save_close" src="../../../static/image/icon_close.png" alt @click="close_save" />
        <img class="save_chai" src="../../../static/image/save.png" alt @click="text_save" />
      </div>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
import foot from "../bass/foot";
import user from "../user/user";
import "swiper/dist/css/swiper.css"; ////这里注意具体看使用的版本是否需要引入样式，以及具体位置。
import { swiper, swiperSlide } from "vue-awesome-swiper";
import { setTimeout } from "timers";
export default {
  data() {
    return {
      boon: false,
      banner: [],
      user_boon: false,
      swiperOption: {
        //swiper3
        autoplay: 3000,
        speed: 300
        // loop: true
      },
      list: [],
      profile: {},
      progress: 100,
      timer: null,
      sy_time: null, //剩余时间
      hour: "",
      minute: "",
      second: "",
      daojishi: "",
      remain_time: "",
      status: 1,
      order_page: 1,
      close_banner: "",
      nav_list: [
        { name: "签到", url: require("../../../static/image/qiandao.png") },
        { name: "二维码", url: require("../../../static/image/erweima.png") }
      ],
      is_login: false,
      is_save: false,
      money: ""
    };
  },
  components: {
    Foot: foot,
    User: user,
    swiper,
    swiperSlide
  },
  methods: {
    close_save() {
      this.is_save = false;
      this.is_login = false;
      this.$router.push("/");
    },
    close() {
      this.is_login = false;
      this.is_save = false;
    },
    text_save() {
      let that = this;
      that.$post("/apiv1/index/receiveIntegralTurnPdr").then(res => {
        console.log(res);

        if (res.code == 0) {
          that.$msg(res.msg);
          that.is_save = false;
          setTimeout(res => {
            localStorage.removeItem("is_login");
          }, 1000);
        } else if (res.code == 1) {
          that.$msg(res.msg);
          that.is_save = false;
          setTimeout(res => {
            localStorage.removeItem("is_login");
          }, 1000);
        }
      });
    },
    //拆
    text() {
      let that = this;
      that.is_login = false;
      that.$post("/apiv1/index/getTodayReceiveIntegral").then(res => {
        console.log(res);
        that.$msg(res.msg);
        if (res.code == 0) {
          that.is_save = false;
          this.is_login = false;
          setTimeout(res => {
            localStorage.removeItem("is_login");
          }, 1000);
        } else if (res.code == 1) {
          that.is_save = true;
          that.money = res.data.money;
          console.log(that.money);
          setTimeout(res => {
            localStorage.removeItem("is_login");
          }, 1000);
        }
      });
    },
    announcement(id) {
      if (id == 62) {
        this.$router.push("/noticedetail?id=" + id);
      }
    },
    details(e) {
      this.boon = false;
      if (e == "签到") {
        this.qiandao();
      } else {
        this.$router.push("/payment");
      }
    },
    sign() {
      this.boon = !this.boon;
    },
    backtime(begintime) {
      let startTime = new Date(begintime); // 开始时间
      let endTime = new Date(); // 结束时间
      let usedTime = endTime - startTime; // 相差的毫秒数
      let days = Math.floor(usedTime / (24 * 3600 * 1000)); // 计算出天数
      let leavel = usedTime % (24 * 3600 * 1000); // 计算天数后剩余的时间
      let hours = Math.floor(leavel / (3600 * 1000)); // 计算剩余的小时数
      let times = days > 0 ? 24 + hours : hours;
      return times;
    },
    formatSeconds(value) {
      let secondTime = parseInt(value); // 秒
      let minuteTime = 0; // 分
      let hourTime = 0; // 小时
      let that = this;
      if (secondTime > 60) {
        //如果秒数大于60，将秒数转换成整数
        minuteTime = parseInt(secondTime / 60);
        secondTime = parseInt(secondTime % 60);
        if (minuteTime > 60) {
          hourTime = parseInt(minuteTime / 60);
          minuteTime = parseInt(minuteTime % 60);
        }
      } else {
        minuteTime = Math.floor(secondTime / 60);
      }
      that.second = parseInt(secondTime);
      that.minute = parseInt(minuteTime);
      that.hour = parseInt(hourTime);
      if (that.second == 0 && that.minute == 0 && that.hour == 0) {
        that.kuangji();
      }
    },
    shoukuang() {
      let that = this;
      that.$post("/apiv1/index/shareDig").then(res => {
        that.$msg(res.msg);
        if (res.code == 1) {
          that.kuangji();
        }
      });
    },
    close_win() {
      this.user_boon = false;
    },
    showuser() {
      this.user_boon = true;
    },
    to(url) {
      this.$router.push(url);
    },
    qiandao() {
      let that = this;
      that.$post("/apiv1/index/getCheckIn").then(res => {
        that.$msg(res.msg);
        if (res.code == 1) {
          that.$post("/apiv1/index/profile").then(res => {
            console.log(res);
            if (res.code == 1) {
              that.profile = res.msg;
            }
          });
        }
      });
    },
    kuangji() {
      let that = this;
      that.$post("/apiv1/index/getFLagDigMine").then(res => {
        that.status = res.code;
        if (res.code == 1) {
          that.$post("/apiv1/index/profile").then(res => {
            console.log(res);
            if (res.code == 1) {
              that.profile = res.msg;
            }
          });
          that.k_info = res.msg.recent_bonus_log;
          let create_time = that.$times(that.k_info.create_time);
          that.sy_time = that.backtime(create_time);
          that.remain_time = res.msg.remain_time;
          that.progress = (this.sy_time / 24) * 100;
          that.timer = setInterval(() => {
            if (that.remain_time > 0) {
              that.remain_time--;
              that.formatSeconds(that.remain_time);
            } else {
              clearInterval(that.time);
              // that.kuangji()
            }
          }, 1000);
        } else {
          that.progress = 100;
        }
      });
    },
    order() {
      this.$post("/apiv1/index/getBuyOutOrder", {
        page: this.order_page
      }).then(res => {
        if (res.code == 1) {
          if (res.msg.length) {
            this.list = this.list.concat(res.msg);
          } else {
            if (this.order_page > 1) {
              this.$msg("暂无更多数据");
            }
          }
        }
      });
    },
    vuetouch: function(s, e) {
      this.order_page++;
    },
    todetail(status, id) {
      if (status == 1) {
        this.$router.push("/pipei?id=" + id);
      } else if (status == 2) {
        this.$router.push("/jiaoyizhong?id=" + id);
      } else if (status == 3) {
        this.$router.push("/yiwancheng?id=" + id);
      } else if (status == 4) {
        this.$router.push("/yidakuan?id=" + id);
      } else if (status == 6 || status == 7) {
        this.$router.push("/shensuzhong?id=" + id);
      }
    },
    vuetouch: function(s, e) {
      //滑动处理
      let that = this;
      that.close_banner = 0;
      localStorage.setItem("close_banner", 0);
      that.$post("/apiv1/index/profile").then(res => {
        if (res.code == 1) {
          that.profile = res.msg;
        }
      });
    }
  },
  mounted() {
    if (localStorage.getItem("close_banner")) {
      this.close_banner = localStorage.getItem("close_banner");
    } else {
      this.close_banner = 1;
    }
  },
  created() {
    let that = this;
    that.$post("/apiv1/index/banner").then(res => {
      if (res.code == 1) {
        that.banner = res.msg;
        for (let i in this.banner) {
          that.banner[i].img = baseNetworkUrl + that.banner[i].img;
        }
      }
    });
    that.$post("/apiv1/index/profile").then(res => {
      if (res.code == 1) {
        that.profile = res.msg;
      }
    });
    that.kuangji();
    that.order();
    that.$post("/apiv1/index/getTodayReceiveIntegral").then(res => {
      console.log(res);
      if (res.code == 1) {
        that.is_login = true;
      }
    });
  },
  destroyed() {
    clearInterval(this.timer);
  }
};
</script>

<style lang="scss" scoped>
@import "../../assets/scss/mixin";
.mask1 {
  width: 100%;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 10000;
  background: #000;
  opacity: 0.8;
}
.money {
  width: 556px / $ppr;
  height: 747px / $ppr;
  z-index: 10001;
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  background: url("../../../static/image/money_bg.png") no-repeat;
  background-size: 100% 100%;
  .money_box {
    width: 100%;
    height: 100%;
    position: relative;
    .money_close {
      width: 57px / $ppr;
      height: 57px / $ppr;
      position: absolute;
      top: 0;
      right: 0;
    }
    .money_chai {
      width: 68px / $ppr;
      height: 72px / $ppr;
      position: absolute;
      bottom: 128px / $ppr;
      left: 50%;
      margin-left: -34px / $ppr;
    }
  }
  .monery_box {
    width: 750px / $ppr;
    height: 1334px / $ppr;
  }
}
.save {
  width: 556px / $ppr;
  height: 747px / $ppr;
  z-index: 10001;
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  background: url("../../../static/image/save_bg.png") no-repeat;
  background-size: 100% 100%;
  p {
    width: 229px / $ppr;
    height: 32px / $ppr;
    position: absolute;
    top: 400px / $ppr;
    right: 350px / $ppr;
    font-family: PingFang-SC-Heavy;
    font-size: 42px / $ppr;
    font-weight: normal;
    font-stretch: normal;
    line-height: 35px / $ppr;
    letter-spacing: 0px;
    color: #ffe16b;
  }
  .save_box {
    width: 100%;
    height: 100%;
    position: relative;
    .save_close {
      width: 57px / $ppr;
      height: 57px / $ppr;
      position: absolute;
      top: 0;
      right: 200px / $ppr;
    }
    .save_chai {
      width: 191px / $ppr;
      height: 63px / $ppr;
      position: absolute;
      bottom: 630px / $ppr;
      left: 50%;
      margin-left: -175px / $ppr;
    }
  }
  .save_box {
    width: 750px / $ppr;
    height: 1334px / $ppr;
  }
}
.yindao {
  width: 100%;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 20000;
  .swiper-container {
    width: 100%;
    height: 100vh;
    img {
      width: 100%;
      height: 100vh;
    }
  }

  width: 675px / $ppr;
  height: 269px / $ppr;
  img {
    width: 675px / $ppr;
    height: 269px / $ppr;
  }
}
.mask {
  width: 100%;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 10000;
  background: #000;
  opacity: 0.8;
}
.wins {
  width: 500px / $ppr;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 10001;
}
.content {
  width: 100%;
  min-height: 100vh;
  height: 100%;
  /*padding-bottom: 120px/$ppr;*/
  background: #10143a;
  .head {
    width: 100%;
    height: 120px / $ppr;
    position: fixed;
    top: 0;
    left: 0;
    background: #10143a;
    z-index: 500;
    .add {
      width: 181px / $ppr;
      height: 180px / $ppr;
      background-color: #58c2ff;
      border-radius: 10px / $ppr;
      border: solid 4px / $ppr #ffffff;
      position: relative;
      top: -10px / $ppr;
      left: 540px / $ppr;
      .san {
        width: 70px / $ppr;
        height: 70px / $ppr;
        border-radius: 5px / $ppr;
        position: relative;
        top: -20px / $ppr;
        left: 100px / $ppr;
      }
      div:last-child {
        border-bottom: none;
      }
      div {
        position: relative;
        top: -65px / $ppr;
        left: 0px / $ppr;
        width: 181px / $ppr;
        height: 78px / $ppr;
        display: flex;
        justify-content: space-around;
        margin: 0 auto;
        font-family: PingFang-SC-Medium;
        font-size: 24px / $ppr;
        line-height: 78px / $ppr;
        letter-spacing: 0px;
        color: #ffffff;
        border-bottom: 1px / $ppr solid #32acf2;
        img {
          width: 26px / $ppr;
          height: 26px / $ppr;
          margin-top: 30px / $ppr;
        }
      }
    }
    .headbox {
      width: 100%;
      height: 120px / $ppr;
      position: relative;
      .user {
        width: 47px / $ppr;
        height: 47px / $ppr;
        display: block;
        position: absolute;
        top: 36px / $ppr;
        left: 37px / $ppr;
      }
      .logo {
        width: 180px / $ppr;
        height: 31px / $ppr;
        display: block;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        margin: auto;
      }
      .qiandao {
        width: 45px / $ppr;
        height: 45px / $ppr;
        position: absolute;
        top: 40px / $ppr;
        right: 40px / $ppr;
        font-size: 26px / $ppr;
        color: #fff;
      }
    }
  }

  .banner {
    display: block;
    border-radius: 10px / $ppr;
    width: 675px / $ppr;
    height: 269px / $ppr;
    margin: 0 auto;
    margin-bottom: 11px / $ppr;
    margin-top: 120px / $ppr;
    img {
      width: 675px / $ppr;
      height: 269px / $ppr;
    }
  }

  .zhanwei1 {
    width: 100%;
    height: 20px / $ppr;
  }
  .kuang {
    width: 675px / $ppr;
    height: 101px / $ppr;
    background: url("../../../static/image/index4.png") no-repeat;
    background-size: 100% 100%;
    position: relative;
    margin: 0 auto;
    img {
      display: block;
      position: absolute;
      width: 217px / $ppr;
      height: 137px / $ppr;
      top: -33px / $ppr;
      right: -20px / $ppr;
    }
    .k_txt {
      height: 60px / $ppr;
      line-height: 60px / $ppr;
      padding-top: 10px / $ppr;
      padding-left: 20px / $ppr;
      font-size: 20px / $ppr;
      color: #fff;
    }
    .k_box {
      width: 489px / $ppr;
      height: 18px / $ppr;
      background-color: #ffffff;
      border-radius: 9px / $ppr;
      margin-left: 20px / $ppr;
      position: relative;
      .k_btn {
        width: 0;
        height: 18px / $ppr;
        background-color: #83d2ff;
        border-radius: 9px / $ppr;
        position: absolute;
        left: 0;
        top: 0;
      }
    }
  }
  .btn_list {
    width: 692px / $ppr;
    height: 434px / $ppr;
    margin: 0 auto;
    margin-top: 31px / $ppr;
    div {
      width: 329px / $ppr;
      height: 194px / $ppr;
      display: block;
      float: left;
      margin: 0 8.5px / $ppr 36px / $ppr 8.5px / $ppr;
      color: #fff;
      p:nth-child(1) {
        height: 40px / $ppr;
        padding: 111px / $ppr 0 0 20px / $ppr;
        font-size: 24px / $ppr;
      }
      p:nth-child(2) {
        padding-left: 20px / $ppr;
        font-size: 30px / $ppr;
      }
    }
    div:nth-child(1) {
      background: url("../../../static/image/index5.png") no-repeat;
      background-size: 100% 100%;
    }
    div:nth-child(2) {
      background: url("../../../static/image/index6.png") no-repeat;
      background-size: 100% 100%;
    }
    div:nth-child(3) {
      width: 215px / $ppr;
      height: 194px / $ppr;
      background: url("../../../static/image/index7.png") no-repeat;
      background-size: 100% 100%;
    }
    div:nth-child(4) {
      width: 215px / $ppr;
      height: 194px / $ppr;
      background: url("../../../static/image/index8.png") no-repeat;
      background-size: 100% 100%;
    }
    div:nth-child(5) {
      width: 200px / $ppr;
      height: 194px / $ppr;
      background: url("../../../static/image/index11.png") no-repeat;
      background-size: 100% 100%;
    }
  }
  .piao {
    width: 675px / $ppr;
    height: 138px / $ppr;
    margin: 0 auto;
    margin-top: 20px / $ppr;
    background: url("../../../static/image/zhuanpan.png") no-repeat;
    background-size: 100% 100%;
  }
  .jilu {
    width: 676px / $ppr;
    height: auto;
    margin: 0 auto;
    .jilu_titile {
      width: 100%;
      height: 69px / $ppr;
      font-size: 22px / $ppr;
      color: #fff;
      line-height: 69px / $ppr;
      div:nth-child(1) {
        float: left;
        padding-left: 10px / $ppr;
      }
      div:nth-child(2) {
        float: right;
        padding-right: 10px / $ppr;
        img {
          width: 18px / $ppr;
          height: 16px / $ppr;
          display: inline-block;
          margin-top: 26px / $ppr;
          margin-right: 10px / $ppr;
        }
        p {
          display: inline-block;
        }
      }
    }
    .list {
      width: 676px / $ppr;
      height: 57px / $ppr;
      margin: 0 auto;
      border-radius: 10px / $ppr;
      margin-bottom: 13px / $ppr;
      background: #5d97df;
      .mairu {
        background-color: #5ce6ac;
      }
      .maichu {
        background-color: red;
      }
      div {
        display: block;
        float: left;
      }
      div:nth-child(1) {
        width: 16px / $ppr;
        height: 16px / $ppr;
        border-radius: 50%;
        margin: 21px / $ppr 22px / $ppr 0 22px / $ppr;
      }
      div:nth-child(2) {
        width: 200px / $ppr;
        height: 57px / $ppr;
        line-height: 57px / $ppr;
        font-size: 24px / $ppr;
        color: #fff;
      }
      div:nth-child(3) {
        width: 260px / $ppr;
        height: 57px / $ppr;
        line-height: 57px / $ppr;
        font-size: 24px / $ppr;
        color: #fff;
      }
      div:nth-child(4) {
        width: 128px / $ppr;
        height: 39px / $ppr;
        background-color: #0957b8;
        border-radius: 10px / $ppr;
        margin-top: 8px / $ppr;
        font-size: 24px / $ppr;
        color: #fff;
        text-align: center;
        line-height: 39px / $ppr;
      }
    }
  }
}
</style>
