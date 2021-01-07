import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

//store数据，用来存放用户信息，页面状态等内容
const store = new Vuex.Store({
    mutations: {
        show(state, msg) {
            console.log(state, msg);
            state.infos.userName = msg;
        }
    }
});

export default store
