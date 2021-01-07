<template>
  <div class="content" v-swipeup="{fn:vuetouch,name:'上滑'}">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      我的分享
    </div>
    <div class="main">
      <div class="list" v-if="list.length > 0">
        <p>手机号</p>
        <p>等级</p>
        <p>实名</p>
        <p>日期</p>
      </div>
      <div class="list1" v-for="i in list" v-if="list.length > 0">
        <p>{{i.phone}}</p>
        <p>{{i.title}}</p>
        <p v-html="i.is_identity == 1 ? '已实名' : '未实名'">
        <p>{{i.create_at}}</p>
      </div>
      <div v-if="list.length == 0" class="none">暂无数据</div>
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
          back(){
            this.$router.go(-1)
          },
          getlist(){
            let that = this;
            that.$post('/apiv1/index/detailed',{max_page_num: 20,page: that.page}).then(res=>{
              if(res.code == 1){
                if(res.data.length){
                  that.list = that.list.concat(res.data)
                }else{
                  if(this.page > 1){
                    that.$msg('暂无更多数据')
                  }
                }
              }
            })
          },
          vuetouch:function(s,e){
              this.page++
              this.getlist()
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
      position: relative;
      text-align: center;
      line-height: 110px/$ppr;
      font-size: 36px/$ppr;
      color: #fff;
      background: #0d1637;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 100;
      img{
        width: 17px/$ppr;
        height: 29px/$ppr;
        display: block;
        position: absolute;
        top: 40px/$ppr;
        left: 44px/$ppr;
      }
    }
    .main{
      width: 675px/$ppr;
      margin: 0 auto;
      padding-bottom: 20px/$ppr;
      padding-top: 110px/$ppr;
      .none{
        width: 100%;
        padding-top: 100px/$ppr;
        text-align: center;
        color: #fff;
        font-size: 28px/$ppr;
      }
      .list1{
        width: 100%;
        height: 80px/$ppr;
        background: #141f4c;
        font-size: 24px/$ppr;
        color: #fff;
        display: flex;
        p{
          height: 80px/$ppr;
          display:flex;
          flex: 1;
          align-items:center;/*垂直居中*/
          justify-content: center;/*水平居中*/
        }
        div{
          width:80px/$ppr;
          height: 80px/$ppr;
          background: #ff1e1e;
          text-align: center;
          line-height: 80px/$ppr;
          color: #fff;
          font-size: 24px/$ppr;
          animation-duration: 0.1s;
          animation-name: into;
        }
      }
      .list{
        width: 100%;
        height: 80px/$ppr;
        background: #141f4c;
        font-size: 30px/$ppr;
        color: #fff;
        display: flex;
        p{
          height: 80px/$ppr;
          display:flex;
          flex: 1;
          align-items:center;/*垂直居中*/
          justify-content: center;/*水平居中*/
        }
        div{
          width:80px/$ppr;
          height: 80px/$ppr;
          background: #ff1e1e;
          text-align: center;
          line-height: 80px/$ppr;
          color: #fff;
          font-size: 24px/$ppr;
          animation-duration: 0.1s;
          animation-name: into;
        }
      }
    }
  }
</style>
