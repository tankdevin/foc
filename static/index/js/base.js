$(function() {
    function table(aa, bb, cc) {
        $(aa).click(function() {
            var index = $(this).index();
            $(this).addClass(cc).siblings().removeClass(cc);
            $(bb).eq(index).show().siblings(bb).hide();
        })
    }

    table('.agent-top  li', '.agent-one', 'ace')

    //数字累加
    // var options = {
    //     useEasing: true,
    //     useGrouping: true,
    //     separator: ',',
    //     decimal: '.',
    //     prefix: '',
    //     suffix: ''
    // };
    // var demo = new CountUp("count-number", 0, 15, 0, 2.5, options);
    // var demo2 = new CountUp("count-number2", 0, 78, 0, 2.5, options);
    // var demo3 = new CountUp("count-number3", 0, 127, 0, 2.5, options);
    // var demo4 = new CountUp("count-number4", 0, 12000, 0, 2.5, options);
    // var demo5 = new CountUp("count-number5", 0, 2000, 0, 2.5, options);
    // var demo6 = new CountUp("count-number6", 0, 10, 0, 2.5, options);
    // $(window).scroll(function() {
    //     if ($(window).scrollTop() >= 200) {
    //         demo.start();
    //         demo2.start();
    //         demo3.start();
    //         demo4.start();
    //         demo5.start();
    //         demo6.start();
    //     }
    // })


    //封装切换class
    // $.fn.toggle2classes = function(class1, class2) {
    //     if (!class1 || !class2)
    //         return this;

    //     return this.each(function() {
    //         var $elm = $(this);

    //         if ($elm.hasClass(class1) || $elm.hasClass(class2))
    //             $elm.toggleClass(class1 + ' ' + class2);

    //         else
    //             $elm.addClass(class1);
    //     });
    // };
    // $('.pce').click(function() {
    //     $('.b1,.b2').toggle2classes('color1', 'color2');
    // })

    //不封装切换
    // $('.pce').click(function() {

    //     $(".b1, .b2").toggleClass("color1 color2");

    // });

    //图片上传
    function upload(a, b) {
        var file1 = document.querySelector(b);
        obj.addEventListener('click', fn, false);

        function fn() {
            file1.click();
        }
        file1.addEventListener('change', dianji, false);

        function dianji() {
            var files = this.files[0];
            var ofiles = new FileReader();
            ofiles.readAsDataURL(files);
            ofiles.onload = function() {
                //console.log(this.result)
                obj.innerHTML = "<img src = '" + this.result + "'/>";
            }

        }
    }

    //弹出层
    $('.add').click(function() {
        //点击事件弹出层显示，
        $('.tan').fadeToggle();
        //弹出层出现
        $('.alert').fadeToggle();
        $('html,body').addClass('ovfHiden');
        //使网页不可滚动

    });
    $('#close').click(function() {
        //弹出层消失点击事件
        $('.tan').fadeToggle();
        //弹出层消失
        $('.alert').fadeToggle();
        $('html,body').removeClass('ovfHiden');
        //使网页不可滚动
    });

    //checkbox
    $('a.checkbox').click(function() {
        $(this).toggleClass('checked');
    });
    //radio
    $('.radio').click(function() {
        $(this).addClass('checked').siblings('.radio').removeClass('checked');
    });

    //switch
    $("body").on("mousedown", ".switch", function() {
        $(this).toggleClass("on");
        $(this).toggleClass("off");
    });

})