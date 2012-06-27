(function() {
    
    var usrRegex = /^[a-zA-Z0-9]{3,}$/;
    var passwdRegex = /^[a-zA-Z0-9]{6,}$/;
    var $select, $begin, $end, $prev, $next, $refresh, $signout, $captcha, $id, $passwd, $err;
    
    function submit(value) {
        setSubVal(value);
        document.forms["form"].submit();
    }
    
    function setSubVal(value) {
        $('input#subval').val(value);
    }
    
    function submitSelVal() {
        submit('select');
    }
    
    function setError(val) {
//        if (val == null) {
//            $err.css('visibility', 'hidden');
//            $err.css('position', 'fixed');
//        }
//        else {
            $err.css('visibility', 'visible');
            $err.css('position', 'static');
            $err.html(val);
//        }
    }
    
    function validate() {
        if (!usrRegex.test($id.val())) {
            setError('Invalid username format!');
            return false;
        }
        if (!passwdRegex.test($passwd.val())) {
            setError('Invalid password format!');
            return false;
        }
        if ($captcha.val().length != 5) {
            setError('The text entered does not match the code shown!');
            return false;
        }
        return true;
    }
    
    function genSecuredPassword() {
        var passwd = $passwd.val();
        passwd = $().crypt({method:"md5",source:passwd});
        passwd = passwd + $('span#salt').html();
        passwd = $().crypt({method:"sha1",source:passwd});
        $('input#sec_passwd').val(passwd);
    }
    
    function getOptions() {
        var options = new Array();
        $select.find('option').each(function(){
            options.push($(this).val());
        });
        return options;
    }
    
    function setFirstLastOption(isFirst) {
        var v = $select[0];
        var i = isFirst ? v.firstChild : v.lastChild;
        var val = $(i).val();
        if (val != $select.val()) {
            $select.val(val);
            submitSelVal();
        }
    }
    
    function setPrevNextOption(isPrev) {
        var opts = getOptions();
        var index = 0;
        for(var i = 0; i < opts.length; i++) {
            var val = opts[i];
            if (val == $select.val()) {
                index = isPrev ? i - 1 : i + 1;
                break;
            }
        }
        if (index >= opts.length || index < 0) return;
        $select.val(opts[index]);
        submitSelVal();
    }
    
    $(document).ready(function() {
        $id = $('input#usr');
        $passwd = $('input#passwd');
        $err = $('div#hibauzenet');
        $captcha = $('input#captcha_input');
        $signout = $('img#signout');
        $refresh = $('img#refresh');
        $begin = $('img#begin');
        $end = $('img#end');
        $prev = $('img#prev');
        $next = $('img#next');
        $select = $('select#select');
        
        $('div#needjs').css('visibility', 'hidden');
        $('input.gomb').attr('disabled', false);
        $('input.gomb').click(function(){
            if(!validate()) return false;
            genSecuredPassword();
            return true;
        });

        $signout.click(function(){
            submit('signout');
        });
        $refresh.click(function(){
            submitSelVal();
        });
        $begin.click(function(){
            setFirstLastOption(true);
        });
        $end.click(function(){
            setFirstLastOption(false);
        });
        $prev.click(function(){
            setPrevNextOption(true);
        });
        $next.click(function(){
            setPrevNextOption(false);
        });
        $select.change(function(){
            submitSelVal();
        });
    });
    
})();
