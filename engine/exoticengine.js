$(document).ready(function() {
    $('div.exotic-input.radiobox, div.exotic-input-checkbox').click(function() {
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
        $(this).children('input[type="checkbox"], input[type="radio"]').trigger('change');
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
    $('div.numeric > .rem').click(function() {
		var t = $(this).parent();
		var xn = parseInt(t.children('input').val()) || 0;
		var xMax = parseInt(t.children('input').attr('max'));
		var xMin = parseInt(t.children('input').attr('min'));
		var n = Math.max(xMin, Math.min(xn - 1, xMax));
		t.children('input').val(n);
        t.children('span.value').text(toLocalisedNumbers(n));
        onExoticNumericChange(t.children('input'));
	});
	$('div.numeric > .add').click(function() {
		var t = $(this).parent();
		var xn = parseInt(t.children('input').val()) || 0;
		var xMax = parseInt(t.children('input').attr('max'));
		var xMin = parseInt(t.children('input').attr('min'));
		var n = Math.max(xMin, Math.min(xn + 1, xMax));
		t.children('input').val(n);
        t.children('span.value').text(toLocalisedNumbers(n));
        onExoticNumericChange(t.children('input'));
	});
});