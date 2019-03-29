(function () {
    var A = $('.pre_select'), B = $('em', A), C = $('i', A), D = $('.pre_selbox'), E = $('em', D), R = 60,
        H = $('.pre_select_num');
    var pnum = 22, snum = 5, id = [], sel = [], sn = 0;
    for (i = 0; i < pnum; i++) {
        id[i] = (i + 1);
    }
    $('strong', H).html(snum);
    B.each(function (i) {
        $(this).data('id', id[i]).data('sa', Math.random() > .5 ? 0 : 1).rotate({
            angle: -R,
            animateTo: (R / 10) * i - R
        });
    });

    function _collapse() {
        B.each(function (i) {
            $(this).delay((22 - i) * 20).fadeOut()
        })
        B.rotate({
            animateTo: -R,
            callback: function () {
                if (!A.data('die')) A.animate({'height': 0}), A.data('die', 1)
            }
        });
        H.fadeOut();
    }

    C.click(function () {
        if (sn >= snum) return false;
        var _p = $(this).parent();
        sel.push([_p.data('id'), _p.data('sa')]);
        B.removeClass('over');
        $(this).parent().addClass('over').hide();
        $('i', E.eq(sn)).css({'display': 'block'}).fadeIn().rotate({
            animateTo: 5 - Math.random() * 10
        })
        sn++;
        $('strong', H).html(snum - sn)
        if (sn == snum) {
            _collapse();
            end(sel);
        }
    })

    function end(sid) {
        var id = '';
        for (i = 0; i < sid.length; i++) {
            id += (id ? '_' : '') + sid[i].join('_')
        }
        ;var b = $('.btn_div a').eq(1);
        b.attr('href', b.attr('_href')).removeClass('die').attr('die', 'false').attr('href', b.attr('href').replace('_0', '_' + id));
        var c = $('.b-text div');
        c.eq(0).hide();
        c.eq(1).show()
    }

    function randomsort(a, b) {
        return Math.random() > .5 ? -1 : 1;
    }

    pid = id.sort(randomsort);
})()
