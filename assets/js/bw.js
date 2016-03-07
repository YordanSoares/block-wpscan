$(function () {
    $('.tab li').click(function () {
        var index = $('.tab li').index(this);
        $('.bw li').css('display', 'none');
        $('.bw li').eq(index).css('display', 'block');
        $('.bw li').removeClass('select');
        $(this).addClass('select')
    });
});