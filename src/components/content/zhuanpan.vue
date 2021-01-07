<template>
  <div class="content">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      幸运大转盘
      <p @click="tolist">抽奖记录</p>
    </div>
    <div class="zhanwei"></div>
    <div class="zhuanbox">
      <div class="zhuanpan" id="zhuanpan"></div>
      <img class="begin" src="../../../static/image/zhuanpan2.png" alt="" @click="begin" id="begin">
    </div>

    <div class="txt">
      游戏规则：每次抽奖需消耗1OPF，根据运气会获得1OPF，2OPF，5OPF，10OPF或谢谢参与，每日限抽奖10次，祝你好运！
    </div>
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
        num: 1,
        offOn: true,//抽奖控制
        timer: null,
        list:[],
        jiangli:'',
        du: 0,
      }
    },
    components: {},
    methods: {
      tihuan(str){
        if(str){
          let a = str[0];
          return a + '****'
        }
      },
      back() {
        this.$router.go(-1)
      },
      getlist(){
        let that = this;
        that.list = [];
        that.$post('/apiv1/index/getLetterRecord',{page: 1,max_page_num: 3}).then(res=>{
          if(res.code == 1){
            this.list = res.msg;
          }
        })
      },
      tolist(){
        this.$router.push('/zhuanpanlist')
      },
      begin(){
          let that = this;
          let oPointer = document.getElementById("begin");//指针
          let oTurntable = document.getElementById("zhuanpan");//圆盘
          let rdm = Math.random() * 5;//随机那个位置
          let cat = 72;////总共5个扇形区域，每个区域72度
          let du = 3600 + parseInt(rdm*cat);
          this.du += du;
          if(that.offOn){
            layer.open({
              content:'每次抽奖需要消耗1OPF，确定继续？',
              btn: ['确定', '取消'],
              yes: function(index){
                layer.closeAll();
                that.offOn = false;
                oTurntable.style.transitionDuration = '4s'
                oTurntable.style.transitionProperty = 'all';
                oTurntable.style.transform = "rotate(" + that.du + "deg)";
                that.timer = setInterval(()=>{
                  that.$post('/apiv1/index/goLettery').then(res=>{
                    clearInterval(that.timer)
                    if(res.code == 1){
                      that.offOn = true;
                      layer.open({
                        content: res.data == 0 ? '谢谢参与' : '恭喜您获得' + res.data + 'OPF',
                        btn: '确定'
                      });
                      that.getlist()
                    }else{
                      that.$msg(res.msg)
                    }
                  })
                },4000)
              }
            })
          }

      }
    },
    created() {
      this.getlist()
    },
    destroyed() {
      clearInterval(this.timer)
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
      height: 160px/$ppr;
    }
    .zhuanbox{
      width: 627px/$ppr;
      height: 627px/$ppr;
      margin: 0 auto;
      position: relative;
      padding-bottom: 50px/$ppr;
      div{
        width: 627px/$ppr;
        height: 627px/$ppr;
        background: url('../../../static/image/zhuanpan1.png') no-repeat;
        background-size: 100% 100%;
      }
      img{
        width: 161px/$ppr;
        height: 210px/$ppr;
        display: block;
        position: absolute;
        top: 185px/$ppr;
        left: 50%;
        margin-left: -80.5px/$ppr;
      }
    }
    .txt{
      width: 660px/$ppr;
      padding-bottom: 30px/$ppr;
      color: #fff;
      margin: 0 auto;
      font-size: 28px/$ppr;
      line-height: 40px/$ppr;
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
