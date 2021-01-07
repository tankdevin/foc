<template>
  <div class="content">
    <div class="head">
      <div class="headbox">
        算力挖矿
        <img src="../../../static/image/icon_back.png" alt="" class="back" @click="back">
        <img src="../../../static/image/icon_add.png" alt="" class="add" @click="add">
      </div>
    </div>

    <div class="zhanwei"></div>
    <div class="top">
      <div class="top1">
        <p>OPF</p>
        <p v-html="profile.account_money ? jiequ(profile.account_money) : 0"></p>
        <p>累计获得</p>
        <p v-html="info.personal_total_bonus ? jiequ(info.personal_total_bonus) : 0"></p>
        <p>算力挖矿</p>
        <p v-html="info.power_bonus ? jiequ(info.power_bonus) : 0"></p>
      </div>
      <div class="top2">
        <img src="../../../static/image/lou.png" alt="">
        <p v-html="info.total_bonus ?  '全网算力：'+ jiequ(info.total_bonus) :  '全网算力：' + 0"></p>
      </div>
    </div>

    <div class="box">
      <div>
        <p>分享算力</p>
        <p v-html="info.share_bonus ? jiequ(info.share_bonus) : 0"></p>
      </div>
      <div>
        <p>分享算力累计</p>
        <p v-html="info.total_share_bonus ? jiequ(info.total_share_bonus) : 0"></p>
      </div>
    </div>

    <div class="title">
      <p></p>
      <p>数据列表</p>
      <p @click="to()">查看更多</p>
    </div>

    <div class="main">
      <div class="list" style="background: #0d1637;">
        <p>转入时间</p>
        <p>获得</p>
        <p>算力</p>
        <p>状态</p>
      </div>
      <div v-for="(i,index) in list" class="list1" @touchmove="touched(index,i.status)" v-swipeleft="{fn:vuetouch,name:'左滑'}"  v-swiperight="{fn:vuetouch,name:'右滑'}">
        <div  class="list1"  @click="details(i.id)"  >
          <p>{{i.create_at}}</p>
          <p>{{i.bonus_total*1}}</p>
          <p>{{i.real_price*1}}</p>
          <p v-if="i.status == 1">进行中</p>
          <p v-if="i.status == 2">已取消</p>
          <p v-if="i.status == 3">已完成</p>
        </div>
        <div class="quxiao" v-if="touch_index1 == index" @click="cencel(i)">取消</div>
      </div>
    </div>

    <div class="mask" v-if="mask" @touchmove.prevent @click="closewin"></div>
    <div class="win" v-if="mask">
      <div class="win_box">
        <p>转入数量</p>
        <input type="text" v-model="num">
      </div>
      <div class="win_box">
        <p>交易密码</p>
        <input type="password" v-model="password">
      </div>
      <div class="win_send" @click="zhuanru">确认转入</div>
    </div>

    <div class="mask" v-if="pass_boon"></div>
    <div class="pass_win" v-if="pass_boon">
      <div class="pass_title">输入交易密码</div>
      <input type="password" v-model="paypassword" placeholder="请输入交易密码">
      <div class="btns">
        <p @click="closepass">取消</p>
        <p @click="sendpass">确定</p>
      </div>
    </div>

  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              list:[],
              profile:{},
              page: 1,
              info:{},
              mask: false,
              num: '',
              password: '',
              touch_index: null,//滑动列表对应下标
              touch_index1: null,//出现删除按钮使用的控制变量
              paypassword: '',
              pass_boon: false,
              mask_jack:{},
              status: null,//数据状态
            }
        },
        components: {},
        methods: {
          to(){
            this.$router.push('/sllist')
          },
          jiequs(str1,str2){//截取数据
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
          details(id){
            this.$router.push('/sb_list?id=' + id)
          },
          getlist(){
            let that = this;
            that.$post('/apiv1/index/getTransOrderRecord  ',{
              page: that.page,
              max_page_num: 4
            }).then(res=>{
              if(res.code == 1){
                if(res.data.length){
                  that.list = that.list.concat(res.data)

                }else{
                  if(that.page > 1){
                    that.$msg('暂无更多数据')
                  }
                }
              }
            })
          },
          vuetouch:function(s,e){//滑动处理
            let that = this;
            if(s.name == '上滑'){
              that.page++
              that.getlist()
            }else if(s.name == '左滑'){
              if(that.status == 1){
                that.touch_index1 = that.touch_index
              }
            }else{
              that.touch_index1 = null;
              that.status = null;
            }

          },
          add(){
            this.mask = true
          },
          closewin(){//关闭蒙层弹窗
            this.mask = false;
          },
          zhuanru(){//转入
            let that = this;
            if(that.num == ''){
              that.$msg('请输入交易数量')
            }else if(that.password == ''){
              that.$msg('请输入交易密码')
            }else{
              that.$post('/apiv1/index/transAction',{
                type:1,
                num: that.num,
                paypassword: that.password
              }).then(res=>{
                that.$msg(res.msg)
                if(res.code == 1){
                  that.mask = false;
                  that.list = [];
                  that.page = 1;
                  that.getlist()
                }
              })
            }
          },
          touched(i,status){
            this.touch_index = i;
            this.status = status;
          },
          cencel(i){
              //this.mask = true;
              this.mask_jack = i;
              this.pass_boon = true;
              this.touch_index1 = null;
          },
          closepass(){
            this.paypassword = '';
            this.pass_boon = false;
          },
          sendpass(){
            let that = this;
            that.$post('/apiv1/index/transAction',{
              type:2,
              num: that.mask_jack.real_price*1,
              paypassword: that.paypassword,
              id: that.mask_jack.id
            }).then(res=>{
              that.$msg(res.msg)
              if(res.code == 1){
                that.mask_jack = {};
                that.list = [];
                that.page = 1;
                that.getlist()
                that.pass_boon = false;
              }
            })
          }
        },
        created() {
          this.getlist()
          this.$post('/apiv1/index/profile').then(res=>{
            if(res.code == 1){
              this.profile = res.msg
            }
          })
          this.$post('/apiv1/index/getPowerDetail').then(res=>{
            if(res.code == 1){
              this.info = res.msg;
            }
          })
        },
        destroyed() {

        }
    }
</script>

<style lang="scss" scoped>
  @import "../../assets/scss/mixin";
  .mask{
    width: 100%;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 999;
    background: #000;
    opacity: 0.8;
  }
  .pass_win{
    width: 646px/$ppr;
    height: 300px/$ppr;
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    margin: auto;
    background: #fff;
    z-index: 1000;
    border-radius: 20px/$ppr;
    .pass_title{
      width: 100%;
      height: 100px/$ppr;
      text-align: center;
      line-height: 100px/$ppr;
      font-size: 32px/$ppr;
      color: #333;
    }
    input{
      width: 600px/$ppr;
      height: 100px/$ppr;
      text-align: center;
      margin: 0 auto;
    }
    .btns{
      width: 100%;
      height: 100px/$ppr;
      color: #333;
      font-size: 30px/$ppr;
      p{
        width: 50%;
        height: 100px/$ppr;
        text-align: center;
        line-height: 100px/$ppr;
        float: left;
        color: #009284;
      }
    }
  }
  .win{
    width: 100%;
    position: fixed;
    bottom: 0;
    left: 0;
    background: #1e2a5e;
    border-top-left-radius: 40px/$ppr;
    border-top-right-radius: 40px/$ppr;
    z-index: 1000;
    padding: 20px/$ppr 0;
    .win_box{
      width: 660px/$ppr;
      height: 80px/$ppr;
      margin: 0 auto;
      border-bottom: 1px solid #fff;
      color: #fff;
      clear: both;
      p{
        width: 120px/$ppr;
        height: 80px/$ppr;
        line-height: 80px/$ppr;
        float: left;
        font-size: 24px/$ppr;
      }
      input{
        display: block;
        float: left;
        width: 500px/$ppr;
        height: 80px/$ppr;
        border: none;
        outline: none;
        background: #1e2a5e;
        color: #fff;
      }
    }
    .win_send{
      width: 660px/$ppr;
      height: 80px/$ppr;
      background: #0293fc;
      text-align: center;
      line-height: 80px/$ppr;
      text-align: center;
      font-size: 28px/$ppr;
      color: #fff;
      clear: both;
      margin: 0 auto;
      border-radius: 20px/$ppr;
      margin-top: 20px/$ppr;
    }
  }
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
      background: #10143a;
      z-index: 500;
      .headbox{
        width: 100%;
        height: 110px/$ppr;
        position: relative;
        text-align: center;
        line-height: 120px/$ppr;
        font-size: 36px/$ppr;
        color: #fff;
        .back{
          width: 17px/$ppr;
          height: 29px/$ppr;
          display: block;
          position: absolute;
          top: 40px/$ppr;
          left: 44px/$ppr;
        }
        .add{
          width: 44px/$ppr;
          height: 44px/$ppr;
          display: block;
          position: absolute;
          top: 36px/$ppr;
          right: 40px/$ppr;
        }
      }
    }
    .zhanwei{
      width: 100%;
      height: 110px/$ppr;
    }
    .top{
      width: 675px/$ppr;
      height: 393px/$ppr;
      background-color: #141f4c;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      margin-bottom: 17px/$ppr;
      .top1{
        width: 270px/$ppr;
        height: 393px/$ppr;
        float: left;
        padding-left: 40px/$ppr;
        font-size: 30px/$ppr;
        p:nth-child(odd){
          height: 40px/$ppr;
          padding-top: 40px/$ppr;
          color: #7981a5;
        }
        p:nth-child(even){
          height: 30px/$ppr;
          color: #fff;
        }
      }
      .top2{
        float: left;
        img{
          width: 215px/$ppr;
          height: 283px/$ppr;
          margin-left: 20px/$ppr;
          /*margin-top: -20px/$ppr;*/
        }
        p{
          padding-top: 30px/$ppr;
          font-size: 16px/$ppr;
          color: #7981a5;
        }
      }
    }
    .box{
      width: 675px/$ppr;
      height: 155px/$ppr;
      background-color: #141f4c;
      border-radius: 10px/$ppr;
      margin: 0 auto;
      div{
        width: 50%;
        height: 155px/$ppr;
        float: left;
        p:nth-child(1){
          width: 100%;
          height: 90px/$ppr;
          text-align: center;
          line-height: 90px/$ppr;
          font-size: 30px/$ppr;
          color: #7981a5;
        }
        p:nth-child(2){
          width: 100%;
          text-align: center;
          font-size: 30px/$ppr;
          color: #fff;
        }
      }
    }
    .title{
      width: 675px/$ppr;
      height: 80px/$ppr;
      line-height: 80px/$ppr;
      font-size: 22px/$ppr;
      color: #fff;
      margin: 0 auto;
      p:nth-child(1){
        width: 14px/$ppr;
        height: 14px/$ppr;
        border-radius: 50%;
        background: #fff;
        float: left;
        margin: 34px/$ppr 20px/$ppr 0 9px/$ppr;
      }
      p:nth-child(2){
        float: left;
        line-height: 80px/$ppr;
      }
      p:nth-child(3){
        float: right;
        padding-right: 20px/$ppr;
      }
    }
    .main{
      width: 675px/$ppr;
      margin: 0 auto;
      padding-bottom: 20px/$ppr;
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
        
      }
      .quxiao{
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
  @keyframes into{
    0% {
      width:0px/$ppr;
    }
    100% {
      width:80px/$ppr;
    }
  }
</style>
