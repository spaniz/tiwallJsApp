$(document).ready(function() {
    $('div.exotic-input').click(function() {
        var c = $(this).attr('check');
      
        if (c == 'true') {
            $(this).children('input[type="checkbox"], input[type="radio"]').prop('checked', false);
            $(this).attr('check', 'false');
        }
        else {     
            $(this).parents('div.radiogroup').find('div.radiobox[check="true"]').click();
            $(this).children('input[type="checkbox"], input[type="radio"]').prop('checked', true);
            $(this).attr('check', 'true');
        }
    });
    $(document).mousemove(function(e) {
        document.documentElement.style.setProperty('--overX', (e.pageX > (document.documentElement.clientWidth / 2)) ? 1 : 0)
        document.documentElement.style.setProperty('--overY', (e.pageY > (document.documentElement.clientHeight / 2)) ? 1 : 0)
        document.documentElement.style.setProperty('--mouseX', e.pageX + "px");
        document.documentElement.style.setProperty('--mouseY', e.pageY + "px");
    });
    $('*').mouseover(function() {
        if (!$(this).attr('tooltip'))
            return;
        $('#tooltip').css('display', 'block').html($(this).attr('tooltip'));
    });
    $('*').mouseout(function() {
        $('#tooltip').css('display', 'none');
    });
});

let user = { 
    name: "meme", 
    prem: true, 
    prc: function() 
    { 
        return users.filter(function(i) { 
            return i[0] === 'm' && this.prem;
        });
    }
}