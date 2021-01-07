<template>
  <div class="content" v-swipeup="{fn:vuetouch,name:'上滑'}">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      收款记录
    </div>
    
    <div class="main" v-if="list.length > 0">
        <div class="jilu" v-for="i in list">
            <div class="heji">
                <p class="name">{{i.nickname}}</p>
            </div>
            <div class="qianshu">
                <p>{{$times(i.create_time)}}</p>
                <p>￥{{jiequs(i.money)}}</p>
            </div>
        </div>
        
    </div>
    <div class="blank" v-if="list.length == 0">暂无记录</div>
  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        content:'',
        list:[],
        page: 1,
      }
    },
    components: {},
    methods: {
      jiequs(str){
        //let str = str1 + str2;
        if (String(str).indexOf(".") > -1) {
          var temp = Number(str);
          temp = Math.floor(temp * 1000) / 1000;
          temp = temp.toFixed(4);
          return temp
        } else {
          return str
        }
      },
      vuetouch:function(s,e){
        this.page++;
        this.payment();      
      },
      back(){
        this.$router.go(-1)
      },
      payment(){
        this.$post('/apiv1/index/transferList',{page : this.page}).then(res=>{
          if(res.code == 1 ){
            if(res.msg.length){
              this.list = this.list.concat(res.msg)
            }    
          }else{
              if(this.page > 1){
                this.$msg('暂无更多数据')
              }
            }
        })
      }
    },
    created() {
      this.payment();
      this.$post('/apiv1/index/notice',{type: 'about'}).then(res=>{
        if(res.code == 1){
          this.content = res.msg[0].content
        }
      })
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
      text-align: center;
      line-height: 110px/$ppr;
      font-size: 36px/$ppr;
      color: #fff;
      position: fixed;
      top: 0;
      left: 0;
      background: #0d1637;
      img{
        width: 17px/$ppr;
        height: 29px/$ppr;
        display: block;
        position: fixed;
        top: 40px/$ppr;
        left: 44px/$ppr;
      }
    } 
    .blank{
        width: 682px/$ppr;
        border-radius: 10px/$ppr;
        position: fixed;
        top: 150px/$ppr;
        left: 38px/$ppr;
        text-align: center;
        color:white;
        padding-top:50px/$ppr;
    }
    .main{
        width: 682px/$ppr;
	      height: 1000px/$ppr;
        background-color: #3e4abf;
        border-radius: 10px/$ppr;
        position: fixed;
        top: 150px/$ppr;
        left: 38px/$ppr;
        .jilu:last-child{
          border-bottom:none;
        }
        .jilu{
            width: 648px/$ppr;
            height: 90px/$ppr;
            margin:0 auto;
            margin-top:30px/$ppr;
            border-bottom: 1px solid #fff;
            .heji{
                width: 632px/$ppr;
                // height: 75px/$ppr;
                font-family: PingFang-SC-Regular;
                font-size: 30px/$ppr;
                font-weight: normal;
                font-stretch: normal;
                line-height: 49px/$ppr;
                letter-spacing: 1px/$ppr;
                color: #ffffff;
            }
            .qianshu{
                display:flex;
                justify-content: space-between; 
                font-family: PingFang-SC-Regular;
                font-size: 18px/$ppr;
                font-weight: normal;
                letter-spacing: 1px/$ppr;
                color: #ffffff;
            }
        }
    }
  }
</style>
