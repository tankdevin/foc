<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/img/icon_back.png" alt @click="back" />
      收货地址
    </div>
    <Bscroll>
      <div class="main" v-for="(item,i) in list " :key="i">
        <div class="main_top">
          <p>{{item.name}}</p>
          <p>{{item.phone}}</p>
        </div>
        <div class="main_boo">
          <p>{{item.sheng}}{{item.shi}}{{item.qu}}{{item.addr}}</p>
        </div>
        <div class="ship_main">
          <div @click="check(item.id)">
            <div class="ship_one" v-if="item.default == 1">
              <img src="../../../static/img/dui.png" alt />
              <p>设为默认</p>
            </div>
            <div class="ship_one" v-else>
              <img src="../../../static/img/yuan.png" alt />
              <p>设为默认</p>
            </div>
          </div>
          <div class="ship_two" @click="edit(item.id)">
            <img src="../../../static/img/edit1.png" alt />
            <p>编辑</p>
          </div>
          <div class="ship_two" @click="del(item.id)">
            <img src="../../../static/img/del.png" alt />
            <p>删除</p>
          </div>
        </div>
      </div>

      <div class="mask" v-if="ismr"></div>
      <div v-if="ismr" class="tankuang">
        <div>确认要删除地址吗？</div>
        <div>
          <p @click="cancle">取消</p>
          <p @click="sure">确认</p>
        </div>
      </div>
    </Bscroll>
    <div class="buy_boo" @click="add">
      <div class="buy">添加收货地址</div>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
export default {
  data() {
    return {
      list: [],
      boon: false,
      pageNo: 1,
      ismr: false,
      id: ""
    };
  },
  created() {
    this.addresslist();
  },
  methods: {
    edit(id) {
      this.$router.push("editadress?id=" + id);
    },
    del(id) {
      this.ismr = true;
      this.id = id;
    },
    cancle() {
      this.ismr = false;
    },
    sure() {
      this.ismr = false;
      this.$post("/user/deldelivery", {
        id: this.id
      }).then(res => {
        this.$msg(res.msg);
        if (res.code == 1) {
          this.addresslist();
        }
      });
    },
    check(id) {
      console.log(id);
      this.$post("/user/moren", {
        id: id
      }).then(res => {
        this.$msg(res.msg);
        if (res.code == 1) {
          this.addresslist();
        }
      });
    },
    back() {
      this.$router.back(-1);
    },
    add() {
      this.$router.push("/newaddress");
    },
    addresslist() {
      this.$post("/apiv1/order/delivery", {
        pageNo: this.pageNo
      }).then(res => {
        console.log(res);
        if (res.code == 1) {
          this.list = res.data;
        }
      });
    },
    vuetouch: function(s, e) {
      this.pageNo++;
      this.addresslist();
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
.mask {
  width: 100%;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1000;
  background: #000;
  opacity: 0.2;
}

.tankuang {
  width: 50%;
  height: 200px / $ppr;
  background-color: #fff;
  position: fixed;
  bottom: 600px / $ppr;
  left: 200px / $ppr;
  z-index: 9000;
  font-family: PingFang-SC-Regular;
  font-size: 25px / $ppr;
  font-weight: bold;
  font-stretch: normal;
  line-height: 40px / $ppr;
  letter-spacing: -1px / $ppr;
  color: #000000;
  text-align: center;
  border-radius: 20px / $ppr;

  div:first-child {
    padding-top: 45px / $ppr;
    padding-bottom: 45px / $ppr;
    border-bottom: 1px / $ppr solid #7f7f7f;
  }

  div:last-child {
    display: flex;
    justify-content: space-around;
    align-items: center;

    p:first-child {
      width: 15%;
      border-right: 1px / $ppr solid #7f7f7f;
      line-height: 70px / $ppr;
      text-align: center;
      padding-right: 90px / $ppr;
    }

    p:last-child {
      color: #0ba5fb;
      height: 70px / $ppr;
      line-height: 70px / $ppr;
      text-align: center;
    }
  }
}

.main {
  width: 100%;
  background-color: #ffffff;
  margin-bottom: 20px / $ppr;
  margin-top: 2.75rem;
  .main_top {
    width: 100%;
    display: flex;
    justify-content: space-between;
    margin: 0 auto;
    font-family: PingFang-SC-Regular;
    font-size: 30px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    line-height: 80px / $ppr;
    letter-spacing: 0px;
    color: #000000;
    height: 70px / $ppr;

    p:first-child {
      padding-left: 40px / $ppr;
    }

    p:last-child {
      padding-right: 40px / $ppr;
    }
  }

  .main_boo {
    font-family: PingFang-SC-Regular;
    font-size: 26px / $ppr;
    font-weight: bold;
    font-stretch: normal;
    line-height: 60px / $ppr;
    letter-spacing: 0px;
    color: #979797;
    padding-left: 40px / $ppr;
  }
}

.ship_main {
  width: 750px / $ppr;
  height: 85px / $ppr;
  background-color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 40px / $ppr;

  .ship_one {
    display: flex;
    justify-content: space-between;
    align-items: center;

    img {
      width: 39px / $ppr;
      height: 39px / $ppr;
      margin-right: 20px / $ppr;
    }

    p {
      font-family: PingFang-SC-Regular;
      font-size: 28px / $ppr;
      font-weight: bold;
      font-stretch: normal;
      line-height: 40px / $ppr;
      letter-spacing: -1px / $ppr;
      color: #7f7f7f;
      padding-right: 80px / $ppr;
    }
  }

  .ship_two {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-left: 50px / $ppr;

    img {
      width: 19px / $ppr;
      height: 19px / $ppr;
      padding-right: 20px / $ppr;
    }

    p {
      font-family: PingFang-SC-Regular;
      font-size: 28px / $ppr;
      font-weight: bold;
      font-stretch: normal;
      line-height: 40px / $ppr;
      letter-spacing: -1px / $ppr;
      color: #7f7f7f;
      padding-right: 80px / $ppr;
    }
  }
}

.buy_boo {
  width: 100%;
  height: 100px / $ppr;

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
</style>
