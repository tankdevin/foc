<template>
  <div class="content" v-swipeup="{fn:vuetouch,name:'上滑'}">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      算力收益
    </div>

    <div class="main">
      <div class="list" v-for="i in list">
        <div class="list1">
            <p>收益名称</p>
            <p>{{i.content}}</p>
        </div>
        <div class="list2">
          计算日期：{{$times(i.create_time)}}
        </div>
      </div>
    </div>
  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        list:[],
        page: 1,
        id: ''
      }
    },
    components: {},
    methods: {
      jiequs(str1,str2){
        let str = str1 + str2;
        if (String(str).indexOf(".") > -1) {
          var temp = Number(str);
          temp = Math.floor(temp * 1000) / 1000;
          temp = temp.toFixed(4);
          return temp
        } else {
          return str
        }
      },
      back(){
        this.$router.go(-1)
      },
      getlist(){
        let that = this;
        that.$post('/apiv1/index/getHashDayBonus',{
          page: that.page,
          orderId: that.id
        }).then(res=>{
          if(res.code == 1){
            if(res.msg.length){
              that.list = that.list.concat(res.msg)
            }else{
              if(that.page > 1){
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
      this.id = this.$route.query.id;
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
      padding-top: 120px/$ppr;
      .list{
        width: 100%;
        height: 155px/$ppr;
        margin-bottom: 15px/$ppr;
        background: #313f79;
        border-radius: 10px/$ppr;
        .list1{
          width: 100%;
          height: 108px/$ppr;
          border-bottom: 2px/$ppr solid #4758a1;
            p:nth-child(1){
              width: 100%;
              height: 55px/$ppr;
              line-height: 60px/$ppr;
              font-size: 24px/$ppr;
              color: #92aaff;
              margin-left: 3%;
            }
            p:nth-child(2){
              width: 100%;
              font-size: 30px/$ppr;
              color: #fff;
              margin-left: 3%;
            }
        }
        .list2{
          padding-left: 22px/$ppr;
          line-height: 44px/$ppr;
          font-size: 18px/$ppr;
          color: #b2c1ff;
        }
      }
    }
  }
</style>
