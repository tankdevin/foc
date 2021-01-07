<template>
    <div class="content">

    </div>
</template>

<script type="text/ecmascript-6">
    export default {
        data() {
            return {
                titlemsg: '哈哈哈',
                val: ''
            }
        },
        components: {},
        created() {
            /*layer.open({
              content: '移动版和PC版不能同时存在同一页面'
              ,btn: '我知道了'
            });
            layer.open({type: 2});

            layer.tips('哈哈哈')

            //跳转方式
            this.$router.push('/from');//保留历史
            this.$router.replace('/from');//不保留历史
            this.$router.go(-1);//返回上一页

            //get请求
            this.$http.get('/static/list.json').then(res=>{
              console.log(res)
            }).catch(err=>{//catch异常处理
              console.log(err)
            });

            //改变store中的值，也可以 this.$store.state.infos.userName='哈哈哈'  直接赋值
            this.$store.commit( 'show', this.msg );
            console.log(this.$store.state.infos)

            post请求
            this.$http.post('/static/list.json',{
              //参数
            }).then(res=>{
              console.log(res)
            }).catch(err=>{
              console.log(err)
            });*/

        },
        methods: {
            go() {
                this.$router.push('/from')
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "../../assets/scss/mixin";

    .content {
      width: 100%;
      min-height: 100vh;
      height: 100%;
        background: #40AFFE;

        .head {
            width: 100%;
            height: 96px/$ppr;
            line-height: 96px/$ppr;
            text-align: center;
            color: #000;
            font-size: 30px/$ppr;
            background: orange;
        }
    }

</style>
