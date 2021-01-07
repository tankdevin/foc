<template>
    <div id="app">
        <transition class="content">
            <router-view></router-view>
        </transition>
    </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
                transitionName: '',
              phone: 13253871445
            }
        },
        beforeRouteUpdate(to, from, next) {
            let isBack = this.$router.isBack;
            if (isBack) {
                this.transitionName = 'slide-right'
            } else {
                this.transitionName = 'slide-left'
            }
            this.$router.isBack = false;
        },
        components:{

        },
        mounted() {

        },
        created() {

        },
        methods: {

        },
    }
</script>

<style lang="scss">
    @import "assets/scss/bass";
    @import "assets/scss/mixin";

    #app {
        font-family: 'Avenir', Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        width: 100%;
        margin: 0 auto;
      overflow-x: hidden;
    }

    .slide-left-enter, .slide-right-leave-active {
        opacity: 0;
        -webkit-transform: translate(50px, 0);
        transform: translate(50px, 0);
    }

    .slide-left-leave-active, .slide-right-enter {
        opacity: 0;
        -webkit-transform: translate(-50px, 0);
        transform: translate(-50px, 0);
    }


</style>
