<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/img/icon_back.png" alt @click="back" />
      新增地址
    </div>
    <div class="new_main">
      <div class="new_one">
        <p>收货人</p>
        <input type="text" v-model="info.name" placeholder="请输入收货人姓名" />
      </div>
      <div class="new_one">
        <p>联系方式</p>
        <input type="text" v-model="info.phone" placeholder="请输入联系方式" />
      </div>
      <div class="new_one">
        <p>所在地址</p>
        <div class="address">
          <div class="select_box">
            <select id="sheng" @change="shengselect()">
              <option
                v-for="(item,i) in shenglist"
                :key="i"
                :value="item.id"
              >{{item.area_name}}</option>
            </select>
            <img src="../../../static/img/sjx.png" alt class="sanJiao" />
          </div>
          <div class="select_box">
            <select id="shi" @change="shiselect()">
              <option v-for="(item,i) in shilist" :key="i" :value="item.area_parent_id">{{item.area_name}}</option>
            </select>
            <img src="../../../static/img/sjx.png" alt class="sanJiao" />
          </div>
          <div class="select_box">
            <select id="qu" @change="quselect()">
              <option
                v-for="(item,i) in qulist"
                :key="i"
                :value="item.area_parent_id"
              >{{item.area_name}}</option>
            </select>
            <img src="../../../static/img/sjx.png" alt class="sanJiao" />
          </div>
        </div>
      </div>
    </div>

    <div class="details">
      <p>详细地址</p>
      <textarea placeholder="请输入详细地址" v-model="info.addr"></textarea>
    </div>

    <div class="save" @click="send">保存</div>
  </div>
</template>

<script type="text/ecmascript-6">
export default {
  data() {
    return {
      shenglist: [], //省列表
      shilist: [], //市列表
      qulist: [], //区列表
      shengid: "",
      shiid: "",
      quid: "",
      info: {
        name: "",
        phone: "",
        province: "", //省
        city: "", //市
        district: "", //区县
        addr: ""
      }
    };
  },
  created() {
    this.getsheng();
  },
  methods: {
    back() {
      this.$router.go(-1);
    },
    send() {
      let that = this;
      let provinces =
        that.info.province + "|" + that.info.city + "|" + that.info.district;
      let data = {
        name: that.info.name,
        phone: that.info.phone,
        provinces: provinces,
        addr: that.info.addr
        // default: that.ismr == 1 ? 1 : 0
      };
      if (that.info.name == "") {
        that.$msg("请输入收货人");
      } else if (that.info.phone == "") {
        that.$msg("请输入手机号");
      } else if (provinces == "") {
        that.$msg("请选择地区");
      } else if (that.info.addr == "") {
        that.$msg("请输入详细地址");
      } else {
        this.$post("/apiv1/order/adddelivery ", data).then(res => {
          console.log(data);
          if (res.code == 1) {
            that.$msg(res.msg);
            that.$router.replace("/shipaddress");
          } else {
            that.$msg(res.msg);
          }
        });
      }
    },
    shengselect() {
      //获取省份值
      let obj = document.getElementById("sheng"); //定位
      let index = obj.selectedIndex; // 选中索引
      let id = obj.options[index].value; // 省
      let value = obj.options[index].text; // 选中值
      this.info.province = id;
      this.getaddress(id);
    },
    shiselect() {
      //获取市值
      let obj = document.getElementById("shi"); //定位id
      let index = obj.selectedIndex; // 选中索引
      let id = obj.options[index].value; // 省id
      let value = obj.options[index].text; // 选中值
      this.info.city = id;
      this.getqu(id);
    },
    quselect() {
      //获取区名
      let obj = document.getElementById("qu"); //定位id
      let index = obj.selectedIndex; // 选中索引
      let id = obj.options[index].value; // 省id
      let value = obj.options[index].text; // 选中值
      this.info.district = id;
    },
    getsheng() {
      //获取省份列表
      let that = this;
      that.$post("/apiv1/order/getProvince").then(res => {
        if (res.code == 1) {
          console.log(res.data);
          that.shenglist = res.data;
          that.shengid = that.shenglist[0].id;
          that.info.province = that.shenglist[0].id;
          that.getaddress(that.shengid);
        }
      });
    },
    getaddress(id) {
      //获取市列表
      let that = this;
      that
        .$post("/apiv1/order/getCity", {
          province_id: id
        })
        .then(res => {
          console.log(res);
          if (res.code == 1) {
            that.shilist = res.data;
            that.shiid = that.shilist[0].id;
            that.info.city = that.shilist[0].id;
            that.getqu(that.shiid);
          }
        });
    },
    getqu(id) {
      //获取区列表
      let that = this;
      that
        .$post("/apiv1/order/getDistrict", {
          city_id: id
        })
        .then(res => {
          console.log(res);
          if (res.code == 1) {
            that.qulist = res.data;
            that.info.district = that.qulist[0].id;
          }
        });
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

* {
  -webkit-appearance: none;
  /*兼容苹果手机,去除苹果手机select 默认样式*/
}

.select_box {
  width: 150px / $ppr;
  height: 98px / $ppr;
  display: flex;
  align-items: center;
  background: #1b285a;
  overflow: hideen;
  margin: 0 6px / $ppr;
}
.sanJiao {
  width: 14px / $ppr;
  height: 14px / $ppr;
}
.new_main {
  width: 100%;
  height: 300px / $ppr;
  background: #1b285a;
  color: #fff;
  margin-top: 2.75rem;

  .new_one {
    height: 100px / $ppr;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px / $ppr solid #e4e4e4;
    padding: 0 20px / $ppr;

    input {
      border: none;
      outline: none;
      background: none;
      font-size: 30px / $ppr;
      width: 512px / $ppr;
    }

    p {
      font-family: PingFang-SC-Medium;
      font-size: 31px / $ppr;
      font-weight: normal;
      font-stretch: normal;
      line-height: 89px / $ppr;
      letter-spacing: -1px / $ppr;
    }

    .address {
      height: 98px / $ppr;
      display: flex;
      select {
        background: #1b285a;
        color: #fff;
        display: block;
        width: 128px / $ppr;
        height: 98px / $ppr;
        color: #999;
        font-size: 30px / $ppr;
        border: none;
        outline: none;
        float: left;
      }
    }
  }
}

.details {
  margin-top: 30px / $ppr;
  height: 250px / $ppr;
  background: #1b285a;
  color: #fff;
  font-family: PingFang-SC-Medium;
  font-size: 31px / $ppr;
  font-weight: normal;
  font-stretch: normal;
  line-height: 89px / $ppr;
  letter-spacing: -1px / $ppr;
  padding-left: 20px / $ppr;
  display: flex;
  justify-content: space-between;

  textarea {
    line-height: 50px / $ppr;
    width: 537px / $ppr;
    line-height: 80px / $ppr;
    float: left;
    font-size: 30px / $ppr;
    color: #999;
    background: #1b285a;
    border: none;
    outline: none;
  }
}

.save {
  width: 630px / $ppr;
  height: 87px / $ppr;
  background-image: linear-gradient(90deg, #197cfb 0%, #46c1fd 100%),
    linear-gradient(#000000, #000000);
  background-blend-mode: normal, normal;
  border-radius: 44px / $ppr;
  font-family: PingFang-SC-Medium;
  font-size: 31px / $ppr;
  font-weight: normal;
  font-stretch: normal;
  line-height: 89px / $ppr;
  letter-spacing: -1px / $ppr;
  color: #ffffff;
  text-align: center;
  margin: 100px / $ppr auto 0;
}
</style>
