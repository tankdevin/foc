<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      转账记录
    </div>
    <div class="main" >
        
        <!-- <div class="bishu">
            <div class="heji">
                <p>收款笔记</p>
                <p>合计</p>
            </div>
            <div class="qianshu">
                <p>1</p>
                <p>￥200.00</p>
            </div>
        </div> -->
        <div class="jilu">
            <div class="heji">
                <p class="name">张三</p>
            </div>
            <div class="qianshu">
                <p>1</p>
                <p>￥200.00</p>
            </div>
        </div>
        <div></div>
        <div></div>
    </div>

  </div>
</template>

<script type="text/ecmascript-6">
  export default {
    data() {
      return {
        content:''
      }
    },
    components: {},
    methods: {
      back(){
        this.$router.go(-1)
      }
    },
    created() {
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
    .main{
        width: 682px/$ppr;
	    height: 725px/$ppr;
        background-color: #3e4abf;
        border-radius: 10px/$ppr;
        position: fixed;
        top: 150px/$ppr;
        left: 38px/$ppr;
        .time{
            width: 682px/$ppr;
            height: 68px/$ppr;
            background-color: #198bff;
            border-radius: 10px/$ppr 10px/$ppr 0px 0px;
            .time_text{
                width: 170px/$ppr;
                height: 23px/$ppr;
                font-family: PingFang-SC-Regular;
                font-size: 24px/$ppr;
                font-weight: normal;
                font-stretch: normal;
                line-height: 42px/$ppr;
                letter-spacing: 1px/$ppr;
                color: #ffffff;
                padding:15px/$ppr 21px/$ppr 0 31px/$ppr;
            }
            
        }
        // .bishu{
        //     width: 648px/$ppr;
        //     height: 90px/$ppr;
        //     border-radius: 1px/$ppr;
        //     border-bottom:1px/$ppr solid #fff;
        //     margin:0 auto;
        //     margin-top:30px/$ppr;
        //     .heji{
        //         display:flex;
        //         justify-content: space-between;
        //         font-family: PingFang-SC-Regular;
        //         font-size: 18px/$ppr;
        //         font-weight: normal;
        //         letter-spacing: 1px/$ppr;
        //         color: #ffffff;
        //     }
        //     .qianshu{
        //         display:flex;
        //         justify-content: space-between; 
        //         font-family: PingFang-SC-Regular;
        //         font-size: 18px/$ppr;
        //         font-weight: normal;
        //         letter-spacing: 1px/$ppr;
        //         color: #ffffff;
        //     }
        // }
        .jilu{
            width: 648px/$ppr;
            height: 90px/$ppr;
            border-radius: 1px/$ppr;
            border-bottom:1px/$ppr dashed #fff;
            margin:0 auto;
            margin-top:30px/$ppr;
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
