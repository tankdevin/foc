<template>
  <div class="content" v-swipeup="{fn:vuetouch,name:'上滑'}">
    <Foot class="foot" :status="3"></Foot>
      <div class="banner">
        <swiper :options="swiperOption">
          <swiper-slide v-for="(i,ind) in banner" :key="ind"><img :src="i.img"></swiper-slide>
        </swiper>
      </div>

      <div class="nav">
        <div @click="check(1)">
          <p :class="tabs == 1 ? 'check' : 'no'">快讯</p>
        </div>
        <div @click="check(2)">
          <p :class="tabs == 2 ? 'check' : 'no'">公告</p>
        </div>
      </div>

      <div class="main mescroll">
          <div class="list" v-for="i in list" @click="to(i.id)">
            <div>
              <img :src="i.img" alt="">
            </div>
            <div>
              <p>{{i.title}}</p>
              <p v-html="i.content"></p>
              <p>{{$times(i.addtime)}}</p>
            </div>
          </div>

      </div>

  </div>
</template>

<script type="text/ecmascript-6">
  import foot from '../bass/foot'
  import 'swiper/dist/css/swiper.css'////这里注意具体看使用的版本是否需要引入样式，以及具体位置。
  import { swiper, swiperSlide } from 'vue-awesome-swiper'
  export default {
    data() {
      return {
        tabs: 1,
        banner:[],
        swiperOption: {//swiper3
          autoplay: 3000,
          speed: 300,
          loop: true
        },
        list:[],
        page: 1,
        scroll: null
      }
    },
    components: {
      Foot: foot,
      swiper,
      swiperSlide,
    },
    methods: {
      check(e){
        if(this.tabs != e){
          this.list = []
        }
        this.tabs = e;
        this.page = 1;
        if(e == 1){
          this.getlist('news')
        }else{
          this.getlist('noticle')
        }
      },
      getlist(type){
        //快讯 news  公告 noticle
        this.$post('/apiv1/index/notice',{type: type,page: this.page}).then(res=>{
          if(res.code == 1){
            this.list = this.list.concat(res.msg);
            // if(this.tabs == 2){
            //   for(let i in this.list){
            //     this.list[i].img = baseNetworkUrl + this.list[i].img
            //   }
            // }
          }
        })
      },
      vuetouch:function(s,e){
        this.page++
        if(this.tabs == 1){
          this.getlist('news')
        }else{
          this.getlist('noticle')
        }
      },
      to(id){
        this.$router.push('/noticedetail?id=' + id)
      }
    },
    created() {
      let that = this;
      that.$post('/apiv1/index/business').then(res=>{
        if(res.code == 1){
          that.banner = res.msg;
          for(let i in that.banner){
            that.banner[i].img = baseNetworkUrl + that.banner[i].img;
          }
        }
      })
      that.getlist('news')
    },
    destroyed() {

    }
  }
</script>

<style lang="scss" scoped>
  @import "../../assets/scss/mixin";
  .swiper-container{
    width: 690px/$ppr;
    height: 385px/$ppr;
    img{
      width: 690px/$ppr;
      height: 385px/$ppr;
    }
  }
  .content{
    width: 100%;
    min-height: 100vh;
    height: 100%;
    background: #0d1637;
    .banner{
      width: 690px/$ppr;
      height: 385px/$ppr;
      margin: 0 auto;
      padding: 65px/$ppr 0 20px/$ppr 0;
      img{
        width: 690px/$ppr;
        height: 385px/$ppr;
      }
    }
    .nav{
      width: 690px/$ppr;
      height: 60px/$ppr;
      margin: 0 auto;
      div{
        width: 60px/$ppr;
        height: 60px/$ppr;
        margin: 0 10px/$ppr;
        display: inline-block;
        p{
          width: 60px/$ppr;
          height: 60px/$ppr;
          text-align: center;
          font-size: 25px/$ppr;
        }
        .check{
          color: #fff;
          border-bottom: 2px solid #4a5ea6;
        }
        .no{
          color: #cfd9ff;
        }
      }
    }
    .main{
      width: 690px/$ppr;
      height: auto;
      margin: 0 auto;
      padding-bottom: 120px/$ppr;
      .list{
        width: 100%;
        height: 209px/$ppr;
        border-bottom: 1px solid #0e2371;
        div:nth-child(1){
          width: 340px/$ppr;
          height: 187px/$ppr;
          padding-top: 22px/$ppr;
          float: left;
          img{
            width: 300px/$ppr;
            height: 162px/$ppr;
            margin-left: 10px/$ppr;
          }
        }
        div:nth-child(2){
          float: left;
          width: 350px/$ppr;
          height: 209px/$ppr;
          p:nth-child(1){
            width: 330px/$ppr;
            height: 40px/$ppr;
            padding-top: 37px/$ppr;
            font-size: 24px/$ppr;
            color: #fff;
            overflow: hidden;
            text-overflow:ellipsis;
            white-space: nowrap;
          }
          p:nth-child(2){
            width: 330px/$ppr;
            height: 72px/$ppr;
            line-height: 24px/$ppr;
            font-size: 14px/$ppr;
            color: #576aae;
            overflow: hidden;
          }
          p:nth-child(3){
            padding-right: 30px/$ppr;
            padding-top: 17px/$ppr;
            text-align: right;
            font-size: 14px/$ppr;
            color: #fff;
          }
        }
      }
    }
  }
</style>
