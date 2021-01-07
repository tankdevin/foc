<template>
  <div class="foot">
    <div @click="to('/')">
      <p>
        <img src="../../../static/image/foot1.png" alt="" v-if="status == 1" class="img1">
        <img src="../../../static/image/foot2.png" alt="" v-else class="img1">
      </p>
      <p :class="status == 1 ? 'foot_check' : 'foot_no'">首页</p>
    </div>
    <div @click="to('/transaction')">
      <p>
        <img src="../../../static/image/foot3.png" alt="" v-if="status == 2" class="img2">
        <img src="../../../static/image/foot4.png" alt="" v-else class="img2">
      </p>
      <p :class="status == 2 ? 'foot_check' : 'foot_no'">交易</p>
    </div>
    <div @click="to('/notice')">
      <p>
        <img src="../../../static/image/foot5.png" alt="" v-if="status == 3" class="img3">
        <img src="../../../static/image/foot6.png" alt="" v-else class="img3">
      </p>
      <p :class="status == 3 ? 'foot_check' : 'foot_no'">消息</p>
    </div>
    <div @click="to('/wallet')">
      <p>
        <img src="../../../static/image/foot7.png" alt="" v-if="status == 4" class="img4">
        <img src="../../../static/image/foot8.png" alt="" v-else class="img4">
      </p>
      <p :class="status == 4 ? 'foot_check' : 'foot_no'">钱包</p>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {}
        },
        props:['status'],
        components: {},
        methods: {
          to(url){
            this.$router.replace(url)
          }
        },
        created() {

        },
        destroyed() {

        }
    }
</script>

<style lang="scss" scoped>
  @import "../../assets/scss/mixin";
  .foot{
    width: 100%;
    height: 120px/$ppr;
    position: fixed;
    left: 0;
    bottom: 0;
    background: #161d48;
    z-index: 500;
    div{
      width: 25%;
      height: 120px/$ppr;
      float: left;
      p:nth-child(1){
        width: 100%;
        height: 75px/$ppr;
        display: block;
        position: relative;
        img{
          display: block;
          position: absolute;
          top: 0;
          right: 0;
          bottom: 0;
          left: 0;
          margin: auto;
        }
        .img1{
          width: 39px/$ppr;
          height: 35px/$ppr;
        }
        .img2{
          width: 33px/$ppr;
          height: 35px/$ppr;
        }
        .img3{
          width: 41px/$ppr;
          height: 35px/$ppr;
        }
        .img4{
          width: 39px/$ppr;
          height: 39px/$ppr;
        }
      }
      p:nth-child(2){
        width: 100%;
        text-align: center;
        font-size: 28px/$ppr;
      }
      .foot_check{
        color: #21e2ff;
      }
      .foot_no{
        color: #fff;
      }
    }
  }
</style>
