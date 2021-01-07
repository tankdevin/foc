<template>
	<div class="wrap" ref="wrap">
	   	<div class="wrapper">
	   		<slot></slot>   
	  	 </div>
	</div>
</template>

<script>
import Bscroll from 'better-scroll';
export default{
	name : 'Bscroll',
	mounted(){
		setTimeout(()=>{
			new Bscroll(this.$refs.wrap,{
				click:true,
				useTransition: false,
				deceleration: 0.002,
			})
  		},10)
	}
}
</script>	
<style>
	.wrap{
		width: 100%;
	}
	.wrapper{
		padding: 0 0 3rem;
	}
</style>

