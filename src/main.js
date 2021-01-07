// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import Vuex from 'vuex'
import store from './vuex/store'
import axios from 'axios'
import layer from '../static/js/layer'
import VueAwesomeSwiper from 'vue-awesome-swiper'
import Es6Promise from 'es6-promise'
import lazyload from "vue-lazyload" //图片懒加载
import Vant from 'vant';
import 'vant/lib/index.css';
import Bscroll from '@/components/bass/Bscroll'
Vue.component('Bscroll', Bscroll)
Es6Promise.polyfill()
// const baseNetworkUrl = 'http://pdrn.cn';
// const baseNetworkUrl = 'http://pdr.fonhua.com';
// const baseNetworkUrl = 'https://pdrvip.com';
// const baseNetworkUrl = 'http://103.42.30.64';
const baseNetworkUrl = "http://www.368001.com/index.php";
// const baseNetworkUrl  = "http://192.168.199.163:8893"
Vue.use(Vant)
Vue.use(lazyload, {
	loading: '',
})

document.addEventListener("deviceready", onDeviceReady, false);



//热更新
function onDeviceReady() {
  console.log('deviceready')
  const downloadProgress = (progress) => {
    console.log(`Downloaded ${progress.receivedBytes} of ${progress.totalBytes}`);
  }

  post('/apiv1/common/hot_update').then(res => {
    if (res.data.hot_update == 1) {
      codePush.sync(null, {
        //安装模式
        //ON_NEXT_RESUME 下次恢复到前台时
        //ON_NEXT_RESTART 下一次重启时
        //IMMEDIATE 马上更新
        installMode: InstallMode.IMMEDIATE,
        //对话框
        updateDialog: {
          //是否显示更新描述
          appendReleaseDescription: true,
          //更新描述的前缀。 默认为"Description"
          descriptionPrefix: "更新内容：",
          //强制更新按钮文字，默认为continue
          mandatoryContinueButtonLabel: "立即更新",
          //强制更新时的信息. 默认为"An update is available that must be installed."
          mandatoryUpdateMessage: "必须更新后才能使用",
          //非强制更新时，按钮文字,默认为"ignore"
          optionalIgnoreButtonLabel: '稍后',
          //非强制更新时，确认按钮文字. 默认为"Install"
          optionalInstallButtonLabel: '后台更新',
          //非强制更新时，检查到更新的消息文本
          optionalUpdateMessage: '有新版本了，是否更新？',
          //Alert窗口的标题
          title: '更新提示'
        },
      }, downloadProgress);
    }
  })
}

axios.interceptors.request.use(
  config => {
    // const token = getCookie('名称');注意使用的时候需要引入cookie方法，推荐js-cookie
    config.data = JSON.stringify(config.data);
    config.headers = {
      'Content-Type': 'application/json',
      'token': localStorage.getItem('token',) || ''
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

//http response 拦截器
axios.interceptors.response.use(
  response => {
    // if (localStorage.getItem('close_banner') && localStorage.getItem('close_banner') == 0) {
      if (response.data.code == 999) {
        localStorage.clear()
        layer.closeAll()
        router.replace({
          path: "/login",
          querry: {redirect: router.currentRoute.fullPath} //从哪个页面跳转
        })
      }
    // }
    return response;
  },
  error => {
    return Promise.reject(error)
  }
)

function get(url, params = {}) {
  return new Promise((resolve, reject) => {
    axios.get(baseNetworkUrl + url, {
      params: params
    }).then(response => {
      resolve(response.data);
    }).catch(err => {
      reject(err)
    })
  })
}


/**
 * 封装post请求
 * @param url
 * @param data
 * @returns {Promise}
 */

function jiequ(str) {
  if (String(str).indexOf(".") > -1) {
    var temp = Number(str);
    temp = Math.floor(temp * 1000) / 1000;
    temp = temp.toFixed(2);
    return temp
  } else {
    return str
  }
}


function post(url, data = {}) {
  return new Promise((resolve, reject) => {
    axios.post(baseNetworkUrl + url, data)
      .then(response => {
        resolve(response.data);
      }, err => {
        reject(err)
      })
  })
}

function times(timestamp) {
  let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear() + '-';
  let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  let D = (date.getDate() < 10 ? '0' + date.getDate() + ' ' : date.getDate() + ' ');
  let h = (date.getHours() < 10 ? '0' + date.getHours() + ':' : date.getHours() + ':');
  let m = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes());
  let s = date.getSeconds();
  return Y + M + D + h + m;
}

Vue.use(Vuex, axios, router, layer, VueAwesomeSwiper)

function msg(title, time = 2) {
  layer.open({
    content: title,
    skin: 'msg',
    time: time
  });
}


//全局请求用户数据
post('/apiv1/index/profile').then(res => {
  if (res.code == 1) {
    localStorage.setItem('profile', JSON.stringify(res.msg));
  }

})


Vue.config.productionTip = false;
Vue.prototype.$http = axios;
Vue.prototype.$get = get;
Vue.prototype.$post = post;
Vue.prototype.layer = layer;
Vue.prototype.$msg = msg;
Vue.prototype.$times = times;
Vue.prototype.jiequ = jiequ;


//触摸
function vueTouch(el, binding, type) { //触屏函数
  console.log(el)
  var _this = this;
  this.obj = el;
  this.binding = binding;
  this.touchType = type;
  this.vueTouches = {x: 0, y: 0}; //触屏坐标
  this.vueMoves = true;
  this.vueLeave = true;
  this.vueCallBack = typeof (binding.value) == "object" ? binding.value.fn : binding.value;
  this.obj.addEventListener("touchstart", function (e) {
    _this.start(e);
  }, false);
  this.obj.addEventListener("touchend", function (e) {
    _this.end(e);
  }, false);
  this.obj.addEventListener("touchmove", function (e) {
    _this.move(e);
  }, false);
};
vueTouch.prototype = {
  start: function (e) { //监听touchstart事件
    this.vueMoves = true;
    this.vueLeave = true;
    this.longTouch = true;
    this.vueTouches = {x: e.changedTouches[0].pageX, y: e.changedTouches[0].pageY};
    this.time = setTimeout(function () {
      if (this.vueLeave && this.vueMoves) {
        this.touchType == "longtap" && this.vueCallBack(this.binding.value, e);
        this.longTouch = false;
      }
      ;
    }.bind(this), 1000);
  },
  end: function (e) { //监听touchend事件
    var disX = e.changedTouches[0].pageX - this.vueTouches.x; //计算移动的位移差
    var disY = e.changedTouches[0].pageY - this.vueTouches.y;
    clearTimeout(this.time);
    if (Math.abs(disX) > 10 || Math.abs(disY) > 100) { //当横向位移大于10，纵向位移大于100，则判定为滑动事件
      this.touchType == "swipe" && this.vueCallBack(this.binding.value, e); //若为滑动事件则返回
      if (Math.abs(disX) > Math.abs(disY)) { //判断是横向滑动还是纵向滑动
        if (disX > 10) {
          this.touchType == "swiperight" && this.vueCallBack(this.binding.value, e); //右滑
        }
        ;
        if (disX < -10) {
          this.touchType == "swipeleft" && this.vueCallBack(this.binding.value, e); //左滑
        }
        ;
      } else {
        if (disY > 10) {
          this.touchType == "swipedown" && this.vueCallBack(this.binding.value, e); //下滑
        }
        ;
        if (disY < -10) {
          this.touchType == "swipeup" && this.vueCallBack(this.binding.value, e); //上滑
        }
        ;
      }
      ;
    } else {
      if (this.longTouch && this.vueMoves) {
        this.touchType == "tap" && this.vueCallBack(this.binding.value, e);
        this.vueLeave = false
      }
      ;
    }
    ;
  },
  move: function (e) { //监听touchmove事件
    this.vueMoves = false;
  }
};
Vue.directive("tap", { //点击事件
  bind: function (el, binding) {
    new vueTouch(el, binding, "tap");
  }
});
Vue.directive("swipe", { //滑动事件
  bind: function (el, binding) {
    new vueTouch(el, binding, "swipe");
  }
});
Vue.directive("swipeleft", { //左滑事件
  bind: function (el, binding) {
    new vueTouch(el, binding, "swipeleft");
  }
});
Vue.directive("swiperight", { //右滑事件
  bind: function (el, binding) {
    new vueTouch(el, binding, "swiperight");
  }
});
Vue.directive("swipedown", { //下滑事件
  bind: function (el, binding) {
    new vueTouch(el, binding, "swipedown");
  }
});
Vue.directive("swipeup", { //上滑事件
  bind: function (el, binding) {
    new vueTouch(el, binding, "swipeup");
  }
});
Vue.directive("longtap", { //长按事件
  bind: function (el, binding) {
    new vueTouch(el, binding, "longtap");
  }
});


/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  components: {App},
  template: '<App/>',
})
