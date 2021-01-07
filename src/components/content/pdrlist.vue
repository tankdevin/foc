<template>
  <div class="content" v-swipeup="{fn:vuetouch,name:'上滑'}">
    <div class="head">
      <img src="../../../static/image/icon_back.png" alt="" @click="back">
      交易记录
    </div>
    <div class="nav">
      <div @click="checktab(1)">
        <p :class="tabs == 1 ? 'check' : 'nocheck'">交易记录</p>
      </div>
      <div @click="checktab(2)">
        <p :class="tabs == 2 ? 'check' : 'nocheck'">历史记录</p>
      </div>
    </div>

    <div class="main">
      <div class="left" v-if="tabs == 1">
        <div class="list" v-for="i in orderlist" @click="todetail(i.status,i.id)">
          <div :class="i.type == 1 ? 'mairu' : 'maichu'"></div>
          <div v-html="i.type == 1 ? '买入' : '卖出'"></div>
          <div>数量：{{i.num*1}}</div>
          <div v-if="i.status == 1">待匹配</div>
          <div v-if="i.status == 2">待打款</div>
          <div v-if="i.status == 4">已打款</div>
          <div v-if="i.status == 6">申诉中</div>
          <div v-if="i.status == 7">申诉完成</div>
        </div>
      </div>

      <div class="right" v-if="tabs == 2">
        <div class="lists" v-for="i in historylist" @click="see(i.id,i.status)">
          <div class="list1">
            <div>
              <p>交易类型</p>
              <p style="color: #ff2828" v-html="i.type == 1 ? '买入' : '卖出'"></p>
            </div>
            <div>
              <p>交易数量</p>
              <p>{{i.num}}</p>
            </div>
            <div>
              <p>订单金额</p>
              <p style="color: #46ffde;">{{i.real_price*1}}</p>
            </div>
          </div>
          <div class="list2">
            <p>计算日期：{{i.create_at}}</p>
            <p v-if="i.status == 3">已完成</p>
            <p v-if="i.status == 5">已取消</p>
            <p v-if="i.status == 7">申诉完成</p>
            <p v-if="i.status == 10">订单超时</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
              tabs: 1,
              order_page: 1,
              orderlist:[],
              history_page: 1,
              historylist:[],
            }
        },
        components: {},
        methods: {
          back(){
            this.$router.go(-1)
          },
          checktab(e) {
            this.tabs = e
          },
          order(){
            this.$post('/apiv1/index/getBuyOutOrder',{
              page: this.order_page
            }).then(res=>{
                if(res.code == 1){
                  if(res.msg.length){
                    this.orderlist = this.orderlist.concat(res.msg)
                  }else{
                    if(this.order_page > 1){
                      this.$msg('暂无更多数据')
                    }
                  }
                }
            })
          },
          history(){
            this.$post('/apiv1/index/getHistoryOrder',{
              page: this.history_page
            }).then(res=>{
              if(res.code == 1){
                if(res.msg.length){
                  this.historylist = this.historylist.concat(res.msg)
                }else{
                  if(this.history_page > 1){
                    this.$msg('暂无更多数据')
                  }
                }
              }
            })
          },
          vuetouch:function(s,e){
            if(this.tabs == 1){
              this.order_page++
              this.order()
            }else if(this.tabs == 2){
              this.history_page++
              this.history()
            }
          },
          todetail(status,id){
            if(status == 1){
              this.$router.push('/pipei?id=' + id)
            }else if(status == 2 || status == 4){
              this.$router.push('/jiaoyizhong?id=' + id)
            }else if(status == 3){
              this.$router.push('/yiwancheng?id=' + id)
            }else if(status == 4){
              this.$router.push('/yidakuan?id=' + id)
            }else if(status == 6 || status == 7){
              this.$router.push('/shensuzhong?id=' + id)
            }
          },
          see(id,status){
            if(status == 3){
              this.$router.push('/yiwancheng?id=' + id)
            }else if( status == 7){
              this.$router.push('/shensuzhong?id=' + id)
            }
          }
        },
        created() {
          this.order()
          this.history()
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
    .nav{
      width: 100%;
      height: 76px/$ppr;
      position: fixed;
      top: 110px/$ppr;
      left: 0;
      div{
        width: 50%;
        height: 76px/$ppr;
        float: left;
        text-align: center;
        p{
          display: inline-block;
        }
        .check{
          font-size: 28px/$ppr;
          color: #a0b4ff;
          line-height: 76px/$ppr;
          border-bottom: 1px solid #a0b4ff;
        }
        .nocheck{
          font-size: 28px/$ppr;
          color: #fff;
          line-height: 76px/$ppr;
          border: none;
        }
      }
    }
    .main{
      width: 100%;
      height: auto;
      padding-top: 220px/$ppr;
      .list{
        width: 676px/$ppr;
        height: 57px/$ppr;
        margin: 0 auto;
        border-radius: 10px/$ppr;
        margin-bottom: 13px/$ppr;
        background: #5d97df;
        .mairu{
          background-color: #5ce6ac;
        }
        .maichu{
          background-color: red;
        }
        div{
          display: inline-block;
        }
        div:nth-child(1){
          width: 16px/$ppr;
          height: 16px/$ppr;
          border-radius: 50%;
          margin: 21px/$ppr 22px/$ppr 0 22px/$ppr;
        }
        div:nth-child(2){
          width: 200px/$ppr;
          height: 57px/$ppr;
          line-height: 57px/$ppr;
          font-size: 24px/$ppr;
          color: #fff;
        }
        div:nth-child(3){
          width: 240px/$ppr;
          height: 57px/$ppr;
          line-height: 57px/$ppr;
          font-size: 24px/$ppr;
          color: #fff;
        }
        div:nth-child(4){
          width: 128px/$ppr;
          height: 39px/$ppr;
          background-color: #0957b8;
          border-radius: 10px/$ppr;
          /*margin-top: 6px/$ppr;*/
          font-size: 24px/$ppr;
          color: #fff;
          text-align: center;
          line-height: 39px/$ppr;
        }
      }
      .lists{
        width: 675px/$ppr;
        height: 155px/$ppr;
        margin: 0 auto;
        margin-bottom: 15px/$ppr;
        background: #313f79;
        border-radius: 10px/$ppr;
        .list1{
          width: 100%;
          height: 108px/$ppr;
          border-bottom: 2px/$ppr solid #4758a1;
          div{
            width: 33.3%;
            height: 108px/$ppr;
            float: left;
            p:nth-child(1){
              width: 100%;
              height: 55px/$ppr;
              line-height: 60px/$ppr;
              text-align: center;
              font-size: 24px/$ppr;
              color: #92aaff;
            }
            p:nth-child(2){
              width: 100%;
              text-align: center;
              font-size: 30px/$ppr;
              color: #fff;
            }
          }
        }
        .list2{
          p:nth-child(1){
            padding-left: 22px/$ppr;
            line-height: 44px/$ppr;
            font-size: 18px/$ppr;
            color: #b2c1ff;
            float: left;
          }
          p:nth-child(2){
            padding-right:60px/$ppr;
            line-height: 44px/$ppr;
            font-size: 18px/$ppr;
            color: #b2c1ff;
            float: right;
          }
        }
      }
    }
  }
</style>
