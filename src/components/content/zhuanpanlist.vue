<template>
  <div class="content" v-swipeup="{fn:vuetouch,name:'上滑'}">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      抽奖记录
    </div>
    <div class="zhanwei"></div>
    <div class="list" style="border-bottom: 1px solid #0d1637;">
      <p>获奖时间</p>
      <p>奖品</p>
    </div>
    <div class="list" v-for="i in list">
      <p>{{$times(i.create_time)}}</p>
      <p>获得{{i.money*1}}OPF</p>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              list:[],
              page: 1
            }
        },
        components: {},
        methods: {
          getlist(){
            let that = this;
            that.$post('/apiv1/index/getLetterRecord',{page: that.page,max_page_num: 20,}).then(res=>{
              if(res.msg.length){
                that.list = that.list.concat(res.msg)
              }else{
                if(that.page > 1){
                  that.$msg('暂无更多数据')
                }
              }
            })
          },
          back(){
            this.$router.go(-1)
          },
          vuetouch:function(s,e){//滑动处理
            let that = this;
            that.page++
            that.getlist()
          },
        },
        created() {
          this.getlist()
        },
        destroyed() {

        }
    }
</script>

<style lang="scss" scoped>
  @import "../../assets/scss/mixin";
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
      text-align: center;
      line-height: 110px/$ppr;
      font-size: 36px/$ppr;
      color: #fff;
      background: #0d1637;
      z-index: 100;
      img{
        width: 17px/$ppr;
        height: 29px/$ppr;
        display: block;
        position: fixed;
        top: 40px/$ppr;
        left: 44px/$ppr;
      }
      p{
        position: fixed;
        top: 0;
        right: 20px/$ppr;
        height: 110px/$ppr;
        font-size: 28px/$ppr;
      }
    }
    .zhanwei{
      width: 100%;
      height: 120px/$ppr;
    }
    .list{
      width: 660px/$ppr;
      height: 80px/$ppr;
      font-size: 24px/$ppr;
      color: #fff;
      margin: 0 auto;
      line-height: 80px/$ppr;
      background: #1b285a;
      p{
        width: 50%;
        float: left;
        text-align: center;
      }
    }
  }
</style>
